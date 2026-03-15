<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdmissionController extends Controller
{
    private function baseQuery(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $deptID = $admin->department_id ?? null;
        $departmentId = $request->filled('department_id') ? $request->input('department_id') : $deptID;

        $query = DB::table('admissions as adm')
            ->leftJoin('departments as dept', 'dept.id', '=', 'adm.department_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'adm.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'adm.academic_class_id')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'adm.academic_session_id')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'adm.account_head_id')
            ->where('adm.amount', '!=', 0);

        if (!empty($departmentId) && Schema::hasColumn('admissions', 'department_id')) {
            $query->where('adm.department_id', $departmentId);
        }

        return $query;
    }

    private function applyFilters($query, Request $request)
    {
        $field = (string) $request->input('field_name');
        $value = $request->input('value');

        if ($field !== '' && $value !== null && $value !== '') {
            $allowed = [
                'invoice_number' => 'adm.invoice_number',
                'name' => 'adm.name',
                'mobile' => 'adm.mobile',
                'admission_roll' => 'adm.admission_roll',
                'student_id' => 'adm.student_id',
                'reg_no' => 'adm.reg_no',
                'admission_id' => 'adm.admission_id',
            ];

            if (isset($allowed[$field])) {
                $col = $allowed[$field];
                $parts = explode('.', $col);
                $table = $parts[0] ?? '';
                $column = $parts[1] ?? '';

                if ($table === 'adm' && $column !== '' && Schema::hasColumn('admissions', $column)) {
                    $query->where($col, 'like', '%' . $value . '%');
                }
            }
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if (!empty($from) && !empty($to) && Schema::hasColumn('admissions', 'payment_date')) {
            $query->whereBetween('adm.payment_date', [$from, $to]);
        } elseif (!empty($from) && Schema::hasColumn('admissions', 'payment_date')) {
            $query->whereDate('adm.payment_date', '>=', $from);
        } elseif (!empty($to) && Schema::hasColumn('admissions', 'payment_date')) {
            $query->whereDate('adm.payment_date', '<=', $to);
        }

        foreach (['academic_class_id', 'academic_qualification_id', 'academic_session_id', 'account_head_id'] as $k) {
            if ($request->filled($k) && Schema::hasColumn('admissions', $k)) {
                $query->where('adm.' . $k, $request->input($k));
            }
        }

        if ($request->filled('status') && Schema::hasColumn('admissions', 'status')) {
            $status = (string) $request->input('status');
            if ($status === 'pending') {
                $query->where('adm.status', '!=', 'success');
            } else {
                $query->where('adm.status', $status);
            }
        }

        if ($request->filled('student_type') && Schema::hasColumn('admissions', 'student_type')) {
            $query->where('adm.student_type', $request->input('student_type'));
        }

        if ($request->filled('payment_gateway_id')) {
            $setupId = $request->input('payment_gateway_id');
            if (Schema::hasTable('admission_fee_setups') && Schema::hasColumn('admission_fee_setups', 'store_id')) {
                $storeId = DB::table('admission_fee_setups')->where('id', $setupId)->value('store_id');
                if ($storeId) {
                    $setupIds = DB::table('admission_fee_setups')->where('store_id', $storeId)->pluck('id')->all();
                    if (!empty($setupIds) && Schema::hasColumn('admissions', 'admission_fee_setup_id')) {
                        $query->whereIn('adm.admission_fee_setup_id', $setupIds);
                    }
                } elseif (Schema::hasColumn('admissions', 'admission_fee_setup_id')) {
                    $query->where('adm.admission_fee_setup_id', $setupId);
                }
            } elseif (Schema::hasColumn('admissions', 'admission_fee_setup_id')) {
                $query->where('adm.admission_fee_setup_id', $setupId);
            }
        }

        return $query;
    }

    private function selectForList($query)
    {
        $select = [
            'adm.id',
            'adm.invoice_number',
            'adm.invoice_date',
            'adm.payment_date',
            'adm.amount',
            'adm.status',
            'adm.account_head_id',
            'adm.department_id',
            'adm.academic_qualification_id',
            'adm.academic_class_id',
            'adm.academic_session_id',
            'adm.admission_fee_setup_id',
            'adm.name',
            'adm.mobile',
            'adm.admission_roll',
            'dept.id as dept_id',
            'dept.name as dept_name',
            'q.id as q_id',
            'q.name as q_name',
            'cls.id as cls_id',
            'cls.name as cls_name',
            'ses.id as ses_id',
            'ses.name as ses_name',
            'ah.id as ah_id',
            'ah.name as ah_name',
        ];

        if (Schema::hasColumn('admissions', 'refund_amount')) {
            $select[] = 'adm.refund_amount';
        } else {
            $select[] = DB::raw('0 as refund_amount');
        }

        if (Schema::hasColumn('admissions', 'refund_date')) {
            $select[] = 'adm.refund_date';
        }

        if (Schema::hasColumn('admissions', 'refund_note')) {
            $select[] = 'adm.refund_note';
        }

        if (Schema::hasColumn('admissions', 'reg_no')) {
            $select[] = 'adm.reg_no';
        }

        if (Schema::hasColumn('admissions', 'student_id')) {
            $select[] = 'adm.student_id';
        }

        if (Schema::hasColumn('admissions', 'admission_id')) {
            $select[] = 'adm.admission_id';
        }

        return $query->select($select);
    }

    private function mapRow($r)
    {
        return [
            'id' => (int) ($r->id ?? 0),
            'invoice_number' => $r->invoice_number ?? null,
            'invoice_date' => $r->invoice_date ?? null,
            'payment_date' => $r->payment_date ?? null,
            'amount' => $r->amount ?? 0,
            'status' => $r->status ?? null,
            'account_head_id' => $r->account_head_id ?? null,
            'department_id' => $r->department_id ?? null,
            'academic_qualification_id' => $r->academic_qualification_id ?? null,
            'academic_class_id' => $r->academic_class_id ?? null,
            'academic_session_id' => $r->academic_session_id ?? null,
            'admission_fee_setup_id' => $r->admission_fee_setup_id ?? null,
            'name' => $r->name ?? null,
            'mobile' => $r->mobile ?? null,
            'admission_roll' => $r->admission_roll ?? null,
            'refund_amount' => $r->refund_amount ?? 0,
            'refund_date' => $r->refund_date ?? null,
            'refund_note' => $r->refund_note ?? null,
            'reg_no' => $r->reg_no ?? null,
            'student_id' => $r->student_id ?? null,
            'admission_id' => $r->admission_id ?? null,
            'department' => $r->dept_id ? ['id' => (int) $r->dept_id, 'name' => $r->dept_name] : null,
            'qualification' => $r->q_id ? ['id' => (int) $r->q_id, 'name' => $r->q_name] : null,
            'academic_class' => $r->cls_id ? ['id' => (int) $r->cls_id, 'name' => $r->cls_name] : null,
            'academic_session' => $r->ses_id ? ['id' => (int) $r->ses_id, 'name' => $r->ses_name] : null,
            'head' => $r->ah_id ? ['id' => (int) $r->ah_id, 'name' => $r->ah_name] : null,
        ];
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admissions')) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $query = $this->baseQuery($request);
        $query = $this->applyFilters($query, $request);
        $query = $this->selectForList($query);
        $query->orderByDesc('adm.id');

        $datas = $query->paginate($perPage);
        $datas->getCollection()->transform(function ($r) {
            return $this->mapRow($r);
        });

        return response()->json($datas);
    }

    public function getPurposes(Request $request)
    {
        if (!Schema::hasTable('admission_fee_setups') || !Schema::hasTable('account_heads')) {
            return response()->json([]);
        }

        $query = DB::table('admission_fee_setups as afs')
            ->join('account_heads as ah', 'ah.id', '=', 'afs.account_head_id');

        if (Schema::hasColumn('account_heads', 'status')) {
            $query->where('ah.status', 'active');
        }

        $rows = $query
            ->select([
                'afs.id',
                'afs.account_head_id',
                'afs.amount',
                'ah.name as head_name',
            ])
            ->orderBy('afs.id')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int) ($r->id ?? 0),
                'account_head_id' => (int) ($r->account_head_id ?? 0),
                'amount' => $r->amount ?? 0,
                'head' => ['id' => (int) ($r->account_head_id ?? 0), 'name' => $r->head_name],
            ];
        }

        return response()->json($out);
    }

    public function refundAmount(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admissions') || !Schema::hasColumn('admissions', 'refund_amount')) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $query = $this->baseQuery($request)->where('adm.refund_amount', '!=', 0);

        $field = (string) $request->input('field_name');
        $value = $request->input('value');
        if ($field !== '' && $value !== null && $value !== '') {
            $allowed = [
                'invoice_number' => 'adm.invoice_number',
                'student_id' => 'adm.student_id',
                'name' => 'adm.name',
                'mobile' => 'adm.mobile',
                'reg_no' => 'adm.reg_no',
                'admission_id' => 'adm.admission_id',
                'admission_roll' => 'adm.admission_roll',
            ];

            if (isset($allowed[$field])) {
                $col = $allowed[$field];
                $parts = explode('.', $col);
                $column = $parts[1] ?? '';
                if ($column !== '' && Schema::hasColumn('admissions', $column)) {
                    $query->where($col, 'like', '%' . $value . '%');
                }
            }
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if (!empty($from) && !empty($to) && Schema::hasColumn('admissions', 'payment_date')) {
            $query->whereBetween('adm.payment_date', [$from, $to]);
        } elseif (!empty($from) && Schema::hasColumn('admissions', 'payment_date')) {
            $query->whereDate('adm.payment_date', '>=', $from);
        } elseif (!empty($to) && Schema::hasColumn('admissions', 'payment_date')) {
            $query->whereDate('adm.payment_date', '<=', $to);
        }

        foreach (['academic_class_id', 'academic_qualification_id', 'academic_session_id'] as $k) {
            if ($request->filled($k) && Schema::hasColumn('admissions', $k)) {
                $query->where('adm.' . $k, $request->input($k));
            }
        }

        if ($request->filled('department_id') && Schema::hasColumn('admissions', 'department_id')) {
            $query->where('adm.department_id', $request->input('department_id'));
        }

        $query = $this->selectForList($query);
        $query->orderByDesc('adm.id');

        $datas = $query->paginate($perPage);
        $datas->getCollection()->transform(function ($r) {
            return $this->mapRow($r);
        });

        return response()->json($datas);
    }

    public function accountWise()
    {
        return view('layouts.backend_app');
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admissions')) {
            return response()->json([], 404);
        }

        $query = $this->selectForList($this->baseQuery($request))->where('adm.id', (int) $id);
        $row = $query->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json($this->mapRow($row));
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('admissions')) {
            return response()->json(['message' => 'Admission module not ready'], 422);
        }

        $admission = DB::table('admissions')->where('id', (int) $id)->first();
        if (!$admission) {
            return response()->json(['message' => 'Admission not found'], 404);
        }

        $updateData = [];

        $map = [
            'name' => 'name',
            'mobile' => 'mobile',
            'admission_roll' => 'admission_roll',
            'amount' => 'amount',
            'status' => 'status',
            'payment_date' => 'payment_date',
            'refund_amount' => 'refund_amount',
            'refund_date' => 'refund_date',
            'refund_note' => 'refund_note',
            'account_head_id' => 'account_head_id',
            'academic_session_id' => 'academic_session_id',
            'department_id' => 'department_id',
            'academic_qualification_id' => 'academic_qualification_id',
            'academic_class_id' => 'academic_class_id',
        ];

        foreach ($map as $in => $col) {
            if ($request->has($in) && Schema::hasColumn('admissions', $col)) {
                $updateData[$col] = $request->input($in);
            }
        }

        if (
            $request->input('status') === 'success' &&
            Schema::hasColumn('admissions', 'status') &&
            ($admission->status ?? '') !== 'success'
        ) {
            if (Schema::hasColumn('admissions', 'payment_date') && empty($updateData['payment_date'])) {
                $updateData['payment_date'] = date('Y-m-d');
            }
            if (Schema::hasColumn('admissions', 'paid_amount') && !isset($updateData['paid_amount'])) {
                $updateData['paid_amount'] = $request->input('amount') ?? ($admission->amount ?? null);
            }
        }

        if (Schema::hasColumn('admissions', 'updated_at')) {
            $updateData['updated_at'] = now();
        }

        DB::table('admissions')->where('id', (int) $id)->update($updateData);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!Schema::hasTable('admissions')) {
            return response()->json(['message' => 'Admission module not ready'], 422);
        }

        $admission = DB::table('admissions')->where('id', (int) $id)->first();
        if (!$admission) {
            return response()->json(['message' => 'Admission not found'], 404);
        }

        if (($admission->status ?? '') === 'success') {
            return response()->json(['message' => 'Paid admission cannot be deleted'], 422);
        }

        DB::table('admissions')->where('id', (int) $id)->delete();

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }
}
