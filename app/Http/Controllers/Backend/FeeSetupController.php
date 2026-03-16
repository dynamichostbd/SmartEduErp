<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeeSetupController extends Controller
{
    private function setupsTable(): ?string
    {
        return Schema::hasTable('fee_setups') ? 'fee_setups' : null;
    }

    private function detailsTable(): ?string
    {
        return Schema::hasTable('fee_setup_details') ? 'fee_setup_details' : null;
    }

    private function emptyPaginator(int $perPage)
    {
        return response()->json([
            'current_page' => 1,
            'data' => [],
            'from' => null,
            'last_page' => 1,
            'per_page' => $perPage,
            'to' => null,
            'total' => 0,
        ]);
    }

    private function flushCaches(): void
    {
        Cache::forget('dynamic_data_cache');
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->setupsTable();
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        if (!$table) {
            return $this->emptyPaginator($perPage);
        }

        $q = DB::table("{$table} as fs")
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'fs.academic_qualification_id')
            ->leftJoin('departments as d', 'd.id', '=', 'fs.department_id')
            ->leftJoin('academic_classes as ac', 'ac.id', '=', 'fs.academic_class_id')
            ->select([
                'fs.id',
                'fs.department_id',
                'fs.academic_qualification_id',
                'fs.academic_class_id',
                'fs.description',
                'fs.created_at',
                'd.name as department_name',
                'aq.name as qualification_name',
                'ac.name as class_name',
            ])
            ->orderByDesc('fs.id');

        foreach (['academic_class_id', 'department_id', 'academic_qualification_id'] as $f) {
            if ($request->filled($f)) {
                $q->where("fs.{$f}", $request->input($f));
            }
        }

        $field = (string) ($request->input('field_name') ?? '');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '') {
            if ($field === 'department') {
                $q->where('d.name', 'like', "%{$value}%");
            } elseif ($field === 'qualification') {
                $q->where('aq.name', 'like', "%{$value}%");
            } elseif ($field === 'class') {
                $q->where('ac.name', 'like', "%{$value}%");
            }
        }

        return response()->json($q->paginate($perPage));
    }

    public function existSetup(Request $request)
    {
        $table = $this->setupsTable();
        if (!$table) {
            return response()->json(false);
        }

        $request->validate([
            'department_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'academic_class_id' => ['required'],
        ]);

        $q = DB::table($table)->where([
            'department_id' => $request->input('department_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'academic_class_id' => $request->input('academic_class_id'),
        ]);

        if ($request->filled('id')) {
            $q->where('id', '!=', (int) $request->input('id'));
        }

        return response()->json($q->exists());
    }

    public function getClasses(Request $request)
    {
        $request->validate([
            'department_id' => ['required'],
            'academic_qualification_id' => ['required'],
        ]);

        if (!Schema::hasTable('subject_assigns') || !Schema::hasTable('academic_classes')) {
            return response()->json([]);
        }

        $q = DB::table('subject_assigns as sa')
            ->join('academic_classes as ac', 'ac.id', '=', 'sa.academic_class_id')
            ->where('sa.department_id', $request->input('department_id'))
            ->where('sa.academic_qualification_id', $request->input('academic_qualification_id'))
            ->select('ac.id', 'ac.name')
            ->orderBy('ac.id');

        $rows = $q->get();
        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r->id] = $r->name;
        }

        return response()->json($out);
    }

    public function store(Request $request)
    {
        $table = $this->setupsTable();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Fee Setup module not ready'], 422);
        }

        $request->validate([
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'details' => ['required', 'array'],
            'details.*.account_head_id' => ['required'],
            'details.*.amount' => ['required'],
            'details.*.payment_gateway_id' => ['required'],
        ]);

        $setupCols = Schema::getColumnListing($table);
        $detailCols = Schema::getColumnListing($detailsTable);
        $admin = Auth::guard('admin')->user();
        $adminId = (int) ($admin->id ?? 0);

        DB::beginTransaction();
        try {
            $setup = [
                'academic_qualification_id' => (int) $request->input('academic_qualification_id'),
                'department_id' => (int) $request->input('department_id'),
                'academic_class_id' => (int) $request->input('academic_class_id'),
            ];

            if ($request->has('description') && in_array('description', $setupCols, true)) {
                $setup['description'] = $request->input('description');
            }
            if (in_array('created_at', $setupCols, true)) {
                $setup['created_at'] = now();
            }
            if (in_array('updated_at', $setupCols, true)) {
                $setup['updated_at'] = now();
            }

            $setupId = DB::table($table)->insertGetId($setup);

            $rows = [];
            foreach ((array) $request->input('details') as $d) {
                $row = [];
                if (in_array('fee_setup_id', $detailCols, true)) {
                    $row['fee_setup_id'] = $setupId;
                }
                foreach ([
                    'account_head_id',
                    'payment_gateway_id',
                    'amount',
                    'start_date',
                    'expire_date',
                    'additional_date',
                    'exam_id',
                    'examination_year',
                    'payment_duration',
                    'academic_class_id_improvment',
                    'online_addmission_fees',
                    'service_charge',
                    'college_fee',
                    'migration_fee',
                    'check_registration_no',
                    'improvement',
                    'status',
                ] as $c) {
                    if (array_key_exists($c, $d) && in_array($c, $detailCols, true)) {
                        $row[$c] = $d[$c];
                    }
                }

                foreach (['is_maker', 'is_checker', 'is_approver', 'maker_id', 'checker_id', 'approver_id'] as $c) {
                    if (array_key_exists($c, $d) && in_array($c, $detailCols, true)) {
                        $row[$c] = $d[$c];
                    }
                }

                if (in_array('is_maker', $detailCols, true) && (int) ($row['is_maker'] ?? 0) === 1 && in_array('maker_id', $detailCols, true)) {
                    $row['maker_id'] = (int) ($row['maker_id'] ?? 0) ?: $adminId;
                }
                if (in_array('is_checker', $detailCols, true) && (int) ($row['is_checker'] ?? 0) === 1 && in_array('checker_id', $detailCols, true)) {
                    $row['checker_id'] = (int) ($row['checker_id'] ?? 0) ?: $adminId;
                }
                if (in_array('is_approver', $detailCols, true) && (int) ($row['is_approver'] ?? 0) === 1 && in_array('approver_id', $detailCols, true)) {
                    $row['approver_id'] = (int) ($row['approver_id'] ?? 0) ?: $adminId;
                }

                if (array_key_exists('depend_head_id', $d) && in_array('depend_head_id', $detailCols, true)) {
                    $v = $d['depend_head_id'];
                    $row['depend_head_id'] = is_array($v) ? json_encode(array_values($v)) : $v;
                }

                if (in_array('created_at', $detailCols, true)) {
                    $row['created_at'] = now();
                }
                if (in_array('updated_at', $detailCols, true)) {
                    $row['updated_at'] = now();
                }

                $rows[] = $row;
            }

            if (count($rows) > 0) {
                DB::table($detailsTable)->insert($rows);
            }

            DB::commit();
            $this->flushCaches();
            return response()->json(['message' => 'Create Successfully!', 'id' => $setupId], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create', 'exception' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->setupsTable();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json([], 404);
        }

        $setup = DB::table($table)->where('id', (int) $id)->first();
        if (!$setup) {
            return response()->json([], 404);
        }

        $department = Schema::hasTable('departments') ? DB::table('departments')->select('id', 'name')->where('id', (int) ($setup->department_id ?? 0))->first() : null;
        $qualification = Schema::hasTable('academic_qualifications') ? DB::table('academic_qualifications')->select('id', 'name')->where('id', (int) ($setup->academic_qualification_id ?? 0))->first() : null;
        $academicClass = Schema::hasTable('academic_classes') ? DB::table('academic_classes')->select('id', 'name')->where('id', (int) ($setup->academic_class_id ?? 0))->first() : null;

        $details = [];
        if (Schema::hasTable('account_heads') && Schema::hasTable('payment_gateways')) {
            $q = DB::table("{$detailsTable} as d")
                ->leftJoin('account_heads as ah', 'ah.id', '=', 'd.account_head_id')
                ->leftJoin('payment_gateways as g', 'g.id', '=', 'd.payment_gateway_id')
                ->leftJoin('exams as e', 'e.id', '=', 'd.exam_id')
                ->where('d.fee_setup_id', (int) $id);

            if (Schema::hasTable('admins')) {
                $cols = Schema::getColumnListing($detailsTable);
                if (in_array('maker_id', $cols, true)) {
                    $q->leftJoin('admins as am', 'am.id', '=', 'd.maker_id');
                }
                if (in_array('checker_id', $cols, true)) {
                    $q->leftJoin('admins as ac', 'ac.id', '=', 'd.checker_id');
                }
                if (in_array('approver_id', $cols, true)) {
                    $q->leftJoin('admins as aa', 'aa.id', '=', 'd.approver_id');
                }
            }

            $select = [
                'd.*',
                'ah.name as account_head_name',
                'g.title as gateway_title',
                'e.name as exam_name',
            ];
            if (Schema::hasTable('admins')) {
                $cols = Schema::getColumnListing($detailsTable);
                if (in_array('maker_id', $cols, true)) {
                    $select[] = 'am.name as maker_name';
                }
                if (in_array('checker_id', $cols, true)) {
                    $select[] = 'ac.name as checker_name';
                }
                if (in_array('approver_id', $cols, true)) {
                    $select[] = 'aa.name as approver_name';
                }
            }

            $details = $q
                ->select($select)
                ->orderBy('d.id')
                ->get()
                ->map(function ($row) {
                    if (isset($row->depend_head_id) && is_string($row->depend_head_id)) {
                        $decoded = json_decode($row->depend_head_id, true);
                        $row->depend_head_id = is_array($decoded) ? $decoded : [];
                    }
                    return $row;
                })
                ->toArray();
        }

        return response()->json([
            'fee_setup' => $setup,
            'department' => $department,
            'qualification' => $qualification,
            'academic_class' => $academicClass,
            'details' => $details,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->setupsTable();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Fee Setup module not ready'], 422);
        }

        $request->validate([
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'details' => ['required', 'array'],
            'details.*.account_head_id' => ['required'],
            'details.*.amount' => ['required'],
            'details.*.payment_gateway_id' => ['required'],
        ]);

        $setupCols = Schema::getColumnListing($table);
        $detailCols = Schema::getColumnListing($detailsTable);

        DB::beginTransaction();
        try {
            $setup = [
                'academic_qualification_id' => (int) $request->input('academic_qualification_id'),
                'department_id' => (int) $request->input('department_id'),
                'academic_class_id' => (int) $request->input('academic_class_id'),
            ];
            if ($request->has('description') && in_array('description', $setupCols, true)) {
                $setup['description'] = $request->input('description');
            }
            if (in_array('updated_at', $setupCols, true)) {
                $setup['updated_at'] = now();
            }

            DB::table($table)->where('id', (int) $id)->update($setup);

            DB::table($detailsTable)->where('fee_setup_id', (int) $id)->delete();

            $rows = [];
            foreach ((array) $request->input('details') as $d) {
                $row = [];
                if (in_array('fee_setup_id', $detailCols, true)) {
                    $row['fee_setup_id'] = (int) $id;
                }
                foreach ([
                    'account_head_id',
                    'payment_gateway_id',
                    'amount',
                    'start_date',
                    'expire_date',
                    'additional_date',
                    'exam_id',
                    'examination_year',
                    'payment_duration',
                    'academic_class_id_improvment',
                    'online_addmission_fees',
                    'service_charge',
                    'college_fee',
                    'migration_fee',
                    'check_registration_no',
                    'improvement',
                    'status',
                ] as $c) {
                    if (array_key_exists($c, $d) && in_array($c, $detailCols, true)) {
                        $row[$c] = $d[$c];
                    }
                }

                foreach (['is_maker', 'is_checker', 'is_approver', 'maker_id', 'checker_id', 'approver_id'] as $c) {
                    if (array_key_exists($c, $d) && in_array($c, $detailCols, true)) {
                        $row[$c] = $d[$c];
                    }
                }

                if (in_array('is_maker', $detailCols, true) && (int) ($row['is_maker'] ?? 0) === 1 && in_array('maker_id', $detailCols, true)) {
                    $row['maker_id'] = (int) ($row['maker_id'] ?? 0) ?: $adminId;
                }
                if (in_array('is_checker', $detailCols, true) && (int) ($row['is_checker'] ?? 0) === 1 && in_array('checker_id', $detailCols, true)) {
                    $row['checker_id'] = (int) ($row['checker_id'] ?? 0) ?: $adminId;
                }
                if (in_array('is_approver', $detailCols, true) && (int) ($row['is_approver'] ?? 0) === 1 && in_array('approver_id', $detailCols, true)) {
                    $row['approver_id'] = (int) ($row['approver_id'] ?? 0) ?: $adminId;
                }

                if (array_key_exists('depend_head_id', $d) && in_array('depend_head_id', $detailCols, true)) {
                    $v = $d['depend_head_id'];
                    $row['depend_head_id'] = is_array($v) ? json_encode(array_values($v)) : $v;
                }

                if (in_array('created_at', $detailCols, true)) {
                    $row['created_at'] = now();
                }
                if (in_array('updated_at', $detailCols, true)) {
                    $row['updated_at'] = now();
                }

                $rows[] = $row;
            }

            if (count($rows) > 0) {
                DB::table($detailsTable)->insert($rows);
            }

            DB::commit();
            $this->flushCaches();
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update', 'exception' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        $table = $this->setupsTable();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Fee Setup module not ready'], 422);
        }

        DB::beginTransaction();
        try {
            DB::table($detailsTable)->where('fee_setup_id', (int) $id)->delete();
            $ok = DB::table($table)->where('id', (int) $id)->delete();
            DB::commit();
            $this->flushCaches();
            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete', 'exception' => $e->getMessage()], 422);
        }
    }

    public function feesLists(Request $request)
    {
        if (!Schema::hasTable('fee_setups') || !Schema::hasTable('fee_setup_details') || !Schema::hasTable('account_heads')) {
            return response()->json([]);
        }

        $request->validate([
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
        ]);

        $feeSetup = DB::table('fee_setups')
            ->where([
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'department_id' => $request->input('department_id'),
                'academic_class_id' => $request->input('academic_class_id'),
            ])
            ->first();

        if (!$feeSetup) {
            return response()->json([]);
        }

        $details = DB::table('fee_setup_details as d')
            ->join('account_heads as ah', 'ah.id', '=', 'd.account_head_id')
            ->where('d.fee_setup_id', $feeSetup->id)
            ->select([
                'ah.name',
                'ah.id',
                'd.payment_gateway_id',
                'd.amount',
                'd.exam_id',
                'd.examination_year',
            ])
            ->orderBy('ah.id')
            ->get();

        return response()->json($details);
    }
}
