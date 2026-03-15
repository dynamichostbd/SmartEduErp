<?php

namespace App\Http\Controllers\Backend\MasterSetup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubjectAssignController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('subject_assigns') ? 'subject_assigns' : null;
    }

    private function detailsTable(): ?string
    {
        return Schema::hasTable('subject_assign_details') ? 'subject_assign_details' : null;
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

        $table = $this->table();
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        if (!$table) {
            return $this->emptyPaginator($perPage);
        }

        $q = DB::table("{$table} as sa")
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'sa.academic_qualification_id')
            ->leftJoin('departments as d', 'd.id', '=', 'sa.department_id')
            ->leftJoin('academic_classes as ac', 'ac.id', '=', 'sa.academic_class_id')
            ->orderByDesc('sa.id');

        $select = [
            'sa.id',
            'sa.academic_qualification_id',
            'sa.department_id',
            'sa.academic_class_id',
            'sa.main_subject',
            'sa.note',
            'aq.name as qualification_name',
            'd.name as department_name',
            'ac.name as class_name',
        ];
        if (Schema::hasColumn($table, 'status')) {
            $select[] = 'sa.status';
        }
        $q->select($select);

        $field = (string) ($request->input('field_name') ?? '');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['qualification_name', 'department_name', 'class_name', 'note'], true)) {
            if ($field === 'qualification_name') {
                $q->where('aq.name', 'like', "%{$value}%");
            } elseif ($field === 'department_name') {
                $q->where('d.name', 'like', "%{$value}%");
            } elseif ($field === 'class_name') {
                $q->where('ac.name', 'like', "%{$value}%");
            } elseif ($field === 'note') {
                $q->where('sa.note', 'like', "%{$value}%");
            }
        }

        foreach (['academic_class_id', 'department_id', 'academic_qualification_id'] as $f) {
            $v = $request->input($f);
            if ($v !== null && $v !== '') {
                $q->where("sa.{$f}", $v);
            }
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Subject Assign module not ready'], 422);
        }

        $request->validate([
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'details' => ['required', 'array'],
            'details.*.subject_id' => ['required'],
        ]);

        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        $data = [
            'academic_qualification_id' => (int) $request->input('academic_qualification_id'),
            'department_id' => (int) $request->input('department_id'),
            'academic_class_id' => (int) $request->input('academic_class_id'),
            'main_subject' => $request->input('main_subject'),
            'note' => $request->input('note'),
        ];

        if (Schema::hasColumn($table, 'status')) {
            $data['status'] = $request->input('status', 'active');
        }

        if (Schema::hasColumn($table, 'created_by')) {
            $data['created_by'] = $admin->name ?? null;
        }
        if (Schema::hasColumn($table, 'created_ip')) {
            $data['created_ip'] = $ip;
        }
        if (Schema::hasColumn($table, 'updated_by')) {
            $data['updated_by'] = $admin->name ?? null;
        }
        if (Schema::hasColumn($table, 'updated_ip')) {
            $data['updated_ip'] = $ip;
        }
        if (Schema::hasColumn($table, 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        $exists = DB::table($table)
            ->where([
                'department_id' => $data['department_id'],
                'academic_class_id' => $data['academic_class_id'],
                'academic_qualification_id' => $data['academic_qualification_id'],
            ])->first();

        if ($exists) {
            return response()->json(['error' => 'Sorry!! Already subject assign'], 200);
        }

        $details = $request->input('details', []);

        DB::beginTransaction();
        try {
            $id = DB::table($table)->insertGetId($data);

            $rows = [];
            $sorting = 1;
            foreach ($details as $d) {
                $rows[] = $this->detailRowForInsert($detailsTable, $id, $d, $sorting++);
            }

            if (!empty($rows)) {
                DB::table($detailsTable)->insert($rows);
            }

            DB::commit();
            $this->flushCaches();
            return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
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

        $table = $this->table();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json([], 404);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        $details = DB::table("{$detailsTable} as d")
            ->leftJoin('subjects as s', 's.id', '=', 'd.subject_id')
            ->leftJoin('subjects as es', 'es.id', '=', 'd.except_subject_id')
            ->select([
                'd.*',
                's.name_en as subject_name_en',
                's.name_bn as subject_name_bn',
                's.parent_id as subject_parent_id',
                'es.name_en as except_subject_name_en',
                'es.name_bn as except_subject_name_bn',
            ])
            ->where('d.subject_assign_id', (int) $id)
            ->orderBy('d.sorting')
            ->orderBy('d.id')
            ->get();

        return response()->json([
            'subject_assign' => $row,
            'details' => $details,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Subject Assign module not ready'], 422);
        }

        $request->validate([
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'details' => ['required', 'array'],
            'details.*.subject_id' => ['required'],
        ]);

        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        $data = [
            'academic_qualification_id' => (int) $request->input('academic_qualification_id'),
            'department_id' => (int) $request->input('department_id'),
            'academic_class_id' => (int) $request->input('academic_class_id'),
            'main_subject' => $request->input('main_subject'),
            'note' => $request->input('note'),
        ];

        if (Schema::hasColumn($table, 'status')) {
            $data['status'] = $request->input('status', 'active');
        }

        if (Schema::hasColumn($table, 'updated_by')) {
            $data['updated_by'] = $admin->name ?? null;
        }
        if (Schema::hasColumn($table, 'updated_ip')) {
            $data['updated_ip'] = $ip;
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        $count = DB::table($table)
            ->where([
                'department_id' => $data['department_id'],
                'academic_class_id' => $data['academic_class_id'],
                'academic_qualification_id' => $data['academic_qualification_id'],
            ])
            ->where('id', '!=', (int) $id)
            ->count();

        if ($count > 0) {
            return response()->json(['error' => 'Sorry!! Already subject assign'], 200);
        }

        $details = $request->input('details', []);

        DB::beginTransaction();
        try {
            DB::table($table)->where('id', (int) $id)->update($data);

            $existing = DB::table($detailsTable)
                ->where('subject_assign_id', (int) $id)
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->toArray();

            $seen = [];
            $sorting = 1;
            foreach ($details as $d) {
                $detailId = isset($d['id']) ? (int) $d['id'] : 0;
                $rowData = $this->detailRowForUpdate($detailsTable, $d, $sorting++);

                if ($detailId > 0 && in_array($detailId, $existing, true)) {
                    DB::table($detailsTable)->where('id', $detailId)->update($rowData);
                    $seen[] = $detailId;
                } else {
                    $rowData['subject_assign_id'] = (int) $id;
                    if (Schema::hasColumn($detailsTable, 'created_at')) {
                        $rowData['created_at'] = now();
                    }
                    if (Schema::hasColumn($detailsTable, 'updated_at')) {
                        $rowData['updated_at'] = now();
                    }
                    DB::table($detailsTable)->insert($rowData);
                }
            }

            $toDelete = array_values(array_diff($existing, $seen));
            if (!empty($toDelete)) {
                DB::table($detailsTable)->whereIn('id', $toDelete)->delete();
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
        $table = $this->table();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Subject Assign module not ready'], 422);
        }

        DB::beginTransaction();
        try {
            DB::table($detailsTable)->where('subject_assign_id', (int) $id)->delete();
            $ok = DB::table($table)->where('id', (int) $id)->delete();
            DB::commit();

            if ($ok) {
                $this->flushCaches();
            }

            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Delete failed', 'exception' => $e->getMessage()], 422);
        }
    }

    private function detailRowForInsert(string $detailsTable, int $subjectAssignId, array $d, int $sorting)
    {
        $row = $this->detailRowForUpdate($detailsTable, $d, $sorting);
        $row['subject_assign_id'] = $subjectAssignId;

        if (Schema::hasColumn($detailsTable, 'created_at')) {
            $row['created_at'] = now();
        }
        if (Schema::hasColumn($detailsTable, 'updated_at')) {
            $row['updated_at'] = now();
        }

        return $row;
    }

    private function detailRowForUpdate(string $detailsTable, array $d, int $sorting)
    {
        $cols = Schema::getColumnListing($detailsTable);

        $intFields = [
            'subject_id',
            'except_subject_id',
            'fourth_subject',
            'main_subject',
            'ct_mark',
            'ct_pass_mark',
            'cq_mark',
            'cq_pass_mark',
            'mcq_mark',
            'mcq_pass_mark',
            'practical_mark',
            'practical_pass_mark',
            'total_mark',
            'sorting',
            'amount',
        ];

        $row = [];
        foreach ($intFields as $f) {
            if (in_array($f, $cols, true)) {
                if ($f === 'sorting') {
                    $row[$f] = (int) ($d[$f] ?? $sorting);
                } else {
                    $v = $d[$f] ?? null;
                    $row[$f] = $v === null || $v === '' ? null : (is_numeric($v) ? (int) $v : $v);
                }
            }
        }

        if (in_array('updated_at', $cols, true)) {
            $row['updated_at'] = now();
        }

        return $row;
    }
}
