<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HostelPaymentController extends Controller
{
    private function paymentsTable(): ?string
    {
        return Schema::hasTable('hostel_payments') ? 'hostel_payments' : null;
    }

    private function detailsTable(): ?string
    {
        return Schema::hasTable('hostel_payment_details') ? 'hostel_payment_details' : null;
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

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->paymentsTable();
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        if (!$table) {
            return $this->emptyPaginator($perPage);
        }

        $departmentId = $request->input('department_id');
        if ($departmentId === null || $departmentId === '') {
            $departmentId = Auth::guard('admin')->user()->department_id ?? null;
        }

        $q = DB::table("{$table} as hp")
            ->orderByDesc('hp.id');

        if (Schema::hasTable('students')) {
            $q->leftJoin('students as s', 's.id', '=', 'hp.student_id');
        }
        if (Schema::hasTable('departments')) {
            $q->leftJoin('departments as dep', 'dep.id', '=', 'hp.department_id');
        }
        if (Schema::hasTable('academic_qualifications')) {
            $q->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'hp.academic_qualification_id');
        }
        if (Schema::hasTable('academic_classes')) {
            $q->leftJoin('academic_classes as ac', 'ac.id', '=', 'hp.academic_class_id');
        }

        // filters
        $field = (string) ($request->input('field_name') ?? 'mobile');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && Schema::hasTable('students')) {
            if ($field === 'invoice_number') {
                $q->where('hp.invoice_number', 'like', "%{$value}%");
            } else {
                $allowed = ['mobile', 'name', 'student_id', 'reg_no', 'admission_id', 'college_roll'];
                if (in_array($field, $allowed, true) && Schema::hasColumn('students', $field)) {
                    $q->where("s.{$field}", 'like', "%{$value}%");
                }
            }
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if ($from) {
            $q->whereDate('hp.payment_date', '>=', $from);
        }
        if ($to) {
            $q->whereDate('hp.payment_date', '<=', $to);
        }

        foreach ([
            'academic_class_id' => 'hp.academic_class_id',
            'academic_qualification_id' => 'hp.academic_qualification_id',
            'academic_session_id' => 'hp.academic_session_id',
            'hostel_id' => 'hp.hostel_id',
        ] as $key => $col) {
            $v = $request->input($key);
            if ($v !== null && $v !== '') {
                $q->where($col, (int) $v);
            }
        }

        // Account head filter is based on details table
        $accountHeadId = $request->input('account_head_id');
        if ($accountHeadId !== null && $accountHeadId !== '' && $this->detailsTable()) {
            $q->whereExists(function ($sub) use ($accountHeadId) {
                $sub->select(DB::raw(1))
                    ->from('hostel_payment_details as d')
                    ->whereColumn('d.hostel_payment_id', 'hp.id')
                    ->where('d.account_head_id', (int) $accountHeadId);
            });
        }

        if ($departmentId !== null && $departmentId !== '') {
            $q->where('hp.department_id', (int) $departmentId);
        }

        $studentType = $request->input('student_type');
        if ($studentType !== null && $studentType !== '' && Schema::hasTable('students') && Schema::hasColumn('students', 'student_type')) {
            $q->where('s.student_type', $studentType);
        }

        $status = $request->input('status');
        if ($status !== null && $status !== '') {
            if ($status === 'pending') {
                $q->where('hp.status', '!=', 'success');
            } else {
                $q->where('hp.status', $status);
            }
        }

        $select = [
            'hp.id',
            'hp.student_id',
            'hp.hostel_id',
            'hp.financial_year_id',
            'hp.academic_session_id',
            'hp.department_id',
            'hp.academic_qualification_id',
            'hp.academic_class_id',
            'hp.admission_id',
            'hp.college_roll',
            'hp.reg_no',
            'hp.invoice_date',
            'hp.invoice_number',
            'hp.amount',
            'hp.discount_amount',
            'hp.payment_date',
            'hp.status',
        ];

        if (Schema::hasTable('students')) {
            $select[] = DB::raw('s.student_id as s_student_id');
            $select[] = DB::raw('s.name as s_name');
            $select[] = DB::raw('s.mobile as s_mobile');
            $select[] = DB::raw('s.admission_id as s_admission_id');
        }
        if (Schema::hasTable('departments')) $select[] = DB::raw('dep.name as department_name');
        if (Schema::hasTable('academic_qualifications')) $select[] = DB::raw('aq.name as qualification_name');
        if (Schema::hasTable('academic_classes')) $select[] = DB::raw('ac.name as academic_class_name');

        if ($this->detailsTable()) {
            $select[] = DB::raw('(select count(*) from hostel_payment_details d where d.hostel_payment_id = hp.id) as details_count');
        } else {
            $select[] = DB::raw('0 as details_count');
        }

        $rows = $q->select($select)->paginate($perPage);

        // map to old-ERP-like nested objects
        $rows->getCollection()->transform(function ($r) {
            $student = null;
            if (property_exists($r, 's_name') || isset($r->s_name)) {
                $student = [
                    'student_id' => $r->s_student_id ?? null,
                    'name' => $r->s_name ?? null,
                    'mobile' => $r->s_mobile ?? null,
                    'admission_id' => $r->s_admission_id ?? null,
                ];
            }

            $r->student = $student;
            $r->department = ['name' => $r->department_name ?? null];
            $r->qualification = ['name' => $r->qualification_name ?? null];
            $r->academic_class = ['name' => $r->academic_class_name ?? null];
            unset($r->s_student_id, $r->s_name, $r->s_mobile, $r->s_admission_id, $r->department_name, $r->qualification_name, $r->academic_class_name);
            return $r;
        });

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Not implemented'], 422);
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->paymentsTable();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Hostel Payment module not ready'], 422);
        }

        $row = DB::table("{$table} as hp")
            ->where('hp.id', (int) $id)
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $details = DB::table("{$detailsTable} as d")
            ->where('d.hostel_payment_id', (int) $id)
            ->orderBy('d.id')
            ->get();

        // attach head name for modal table
        if (Schema::hasTable('account_heads')) {
            $headMap = DB::table('account_heads')->pluck('name', 'id')->toArray();
            $details = $details->map(function ($d) use ($headMap) {
                $d->head = ['name' => $headMap[$d->account_head_id] ?? null];
                return $d;
            });
        }

        $row->details = $details;

        // attach student basic
        if (Schema::hasTable('students')) {
            $stu = DB::table('students')->select('id', 'student_id', 'name', 'mobile', 'admission_id')->where('id', $row->student_id)->first();
            $row->student = $stu;
        }

        // attach related academic objects (for invoice template)
        if (Schema::hasTable('academic_sessions') && isset($row->academic_session_id)) {
            $ses = DB::table('academic_sessions')->select('id', 'name')->where('id', (int) $row->academic_session_id)->first();
            if ($ses) $row->academic_session = ['id' => $ses->id, 'name' => $ses->name];
        }
        if (Schema::hasTable('departments') && isset($row->department_id)) {
            $dep = DB::table('departments')->select('id', 'name')->where('id', (int) $row->department_id)->first();
            if ($dep) $row->department = ['id' => $dep->id, 'name' => $dep->name];
        }
        if (Schema::hasTable('academic_qualifications') && isset($row->academic_qualification_id)) {
            $q = DB::table('academic_qualifications')->select('id', 'name')->where('id', (int) $row->academic_qualification_id)->first();
            if ($q) $row->qualification = ['id' => $q->id, 'name' => $q->name];
        }
        if (Schema::hasTable('academic_classes') && isset($row->academic_class_id)) {
            $ac = DB::table('academic_classes')->select('id', 'name')->where('id', (int) $row->academic_class_id)->first();
            if ($ac) $row->academic_class = ['id' => $ac->id, 'name' => $ac->name];
        }

        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $table = $this->paymentsTable();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Hostel Payment module not ready'], 422);
        }

        $tranId = (string) ($request->input('tran_id') ?? '');
        $cardType = (string) ($request->input('card_type') ?? '');
        $bankTranId = (string) ($request->input('bank_tran_id') ?? '');

        $payment = DB::table($table)->where('id', (int) $id)->first();
        if (!$payment) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (isset($payment->status) && $payment->status === 'success') {
            return response()->json(['message' => 'Already Paid'], 200);
        }

        $updates = [
            'status' => 'success',
            'bank_trans_id' => $bankTranId,
            'card_type' => $cardType,
            'payment_date' => date('Y-m-d'),
            'paid_amount' => $payment->amount ?? null,
            'updated_at' => now(),
        ];

        DB::beginTransaction();
        try {
            DB::table($table)->where('id', (int) $id)->update($updates);
            DB::table($detailsTable)->where('hostel_payment_id', (int) $id)->update(['status' => 'success', 'updated_at' => now()]);
            DB::commit();
            return response()->json(['message' => 'Paid Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to paid', 'exception' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        $table = $this->paymentsTable();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Hostel Payment module not ready'], 422);
        }

        DB::beginTransaction();
        try {
            DB::table($detailsTable)->where('hostel_payment_id', (int) $id)->delete();
            DB::table($table)->where('id', (int) $id)->delete();
            DB::commit();
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Delete failed', 'exception' => $e->getMessage()], 422);
        }
    }

    public function feesDelete(Request $request, $id)
    {
        $detailsTable = $this->detailsTable();
        $table = $this->paymentsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Hostel Payment module not ready'], 422);
        }

        $detail = DB::table($detailsTable)->where('id', (int) $id)->first();
        if (!$detail) {
            return response()->json(['message' => 'Not found'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table($detailsTable)->where('id', (int) $id)->delete();

            // Recalculate amounts (same idea as old ERP)
            $pay = DB::table($table)->where('id', (int) $detail->hostel_payment_id)->first();
            if ($pay) {
                $payableAmount = ((float) ($pay->amount ?? 0) + (float) ($pay->discount_amount ?? 0)) - (float) ($detail->amount ?? 0);
                $discountPercent = (float) ($pay->discount_percent ?? 0);
                $discountAmount = $discountPercent * $payableAmount / 100;
                $amount = $payableAmount - $discountAmount;

                $config = app()->make('siteSettingObj');
                $cfg = is_array($config) ? $config : [];
                $percent = (float) ($cfg['hostel_service_charge_percent'] ?? 0);
                $charge = ((float) $amount * $percent) / 100;

                DB::table($table)->where('id', (int) $pay->id)->update([
                    'amount' => $amount,
                    'discount_amount' => $discountAmount,
                    'service_charge' => $charge,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Delete failed', 'exception' => $e->getMessage()], 422);
        }
    }

    public function monthly(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->paymentsTable();
        $detailsTable = $this->detailsTable();
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        if (!$table || !$detailsTable) {
            return $this->emptyPaginator($perPage);
        }

        $departmentId = $request->input('department_id');
        if ($departmentId === null || $departmentId === '') {
            $departmentId = Auth::guard('admin')->user()->department_id ?? null;
        }

        $q = DB::table("{$detailsTable} as d")
            ->join("{$table} as hp", 'd.hostel_payment_id', '=', 'hp.id')
            ->join('students as std', 'std.id', '=', 'hp.student_id')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'std.academic_session_id')
            ->leftJoin('academic_sessions as fen', 'fen.id', '=', 'hp.financial_year_id')
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'std.academic_qualification_id')
            ->leftJoin('academic_classes as ac', 'ac.id', '=', 'std.academic_class_id')
            ->leftJoin('departments as dep', 'dep.id', '=', 'std.department_id')
            ->leftJoin('hostels as ht', 'ht.id', '=', 'hp.hostel_id');

        if (Schema::hasTable('account_heads')) {
            $q->leftJoin('account_heads as head', 'head.id', '=', 'd.account_head_id');
        }

        // filters
        foreach ([
            'financial_year_id' => 'hp.financial_year_id',
            'academic_class_id' => 'hp.academic_class_id',
            'academic_qualification_id' => 'hp.academic_qualification_id',
            'academic_session_id' => 'hp.academic_session_id',
            'hostel_id' => 'hp.hostel_id',
            'account_head_id' => 'd.account_head_id',
        ] as $key => $col) {
            $v = $request->input($key);
            if ($v !== null && $v !== '') {
                $q->where($col, (int) $v);
            }
        }

        if ($departmentId !== null && $departmentId !== '') {
            $q->where('hp.department_id', (int) $departmentId);
        }

        $status = $request->input('status');
        if ($status !== null && $status !== '') {
            $q->where('hp.status', $status);
        }

        $field = (string) ($request->input('field_name') ?? 'mobile');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '') {
            $allowed = ['mobile', 'name', 'student_id', 'admission_id'];
            if (in_array($field, $allowed, true) && Schema::hasColumn('students', $field)) {
                $q->where("std.{$field}", 'like', "%{$value}%");
            }
        }

        $payableMonth = $request->input('payable_month');
        if ($payableMonth) {
            $q->whereMonth('d.invoice_date', (int) $payableMonth);
        }

        $select = [
            'd.id',
            'd.invoice_date',
            'd.amount',
            'd.status',
            'hp.payment_date',
            'hp.invoice_number',
            DB::raw('std.name as student_name'),
            DB::raw('std.mobile'),
            DB::raw('std.student_id'),
            DB::raw('hp.admission_id'),
            DB::raw('fen.name as financial_year'),
            DB::raw('ses.name as session_name'),
            DB::raw('aq.name as academic_level'),
            DB::raw('ac.name as academic_class'),
            DB::raw('dep.name as department_name'),
            DB::raw('ht.name as hostel_name'),
        ];
        if (Schema::hasTable('account_heads')) {
            $select[] = DB::raw('head.name as head_name');
        } else {
            $select[] = DB::raw("'' as head_name");
        }

        return response()->json($q->select($select)->orderByDesc('d.id')->paginate($perPage));
    }

    public function students(Request $request)
    {
        if (!Schema::hasTable('students')) {
            return response()->json([]);
        }

        $q = DB::table('students as s')
            ->leftJoin('academic_classes as ac', 'ac.id', '=', 's.academic_class_id')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 's.academic_session_id')
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 's.academic_qualification_id')
            ->leftJoin('departments as dep', 'dep.id', '=', 's.department_id')
            ->select([
                's.id',
                's.academic_session_id',
                's.department_id',
                's.academic_qualification_id',
                's.academic_class_id',
                's.hostel_id',
                's.student_id',
                's.name',
                's.mobile',
                's.admission_id',
                's.hostel_discount_percent',
                DB::raw('ses.name as session_name'),
                DB::raw('dep.name as department_name'),
                DB::raw('aq.name as qualification_name'),
                DB::raw('ac.name as academic_class_name'),
            ])
            ->orderByDesc('s.id');

        $hostelId = $request->input('hostel_id');
        if ($hostelId !== null && $hostelId !== '') {
            $q->where('s.hostel_id', (int) $hostelId);
        }

        if ($request->input('discount_student')) {
            $q->whereNotNull('s.hostel_discount_percent');
        }

        $field = (string) ($request->input('field_name') ?? 'mobile');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '') {
            $allowed = ['student_id', 'name', 'mobile', 'reg_no', 'admission_id'];
            if (in_array($field, $allowed, true) && Schema::hasColumn('students', $field)) {
                $q->where("s.{$field}", 'like', "%{$value}%");
            }
        }

        $rows = $q->get();

        $rows = $rows->map(function ($r) {
            $r->academic_session = ['name' => $r->session_name ?? null];
            $r->department = ['name' => $r->department_name ?? null];
            $r->qualification = ['name' => $r->qualification_name ?? null];
            $r->academic_class = ['name' => $r->academic_class_name ?? null];

            unset($r->session_name, $r->department_name, $r->qualification_name, $r->academic_class_name);
            return $r;
        });

        return response()->json($rows);
    }

    public function discount(Request $request)
    {
        if (!Schema::hasTable('students')) {
            return response()->json(['message' => 'Student module not ready'], 422);
        }

        $id = $request->input('id');
        if (!$id) {
            return response()->json(['message' => 'Student id required'], 422);
        }

        try {
            DB::table('students')->where('id', (int) $id)->update([
                'hostel_discount_percent' => $request->input('discount'),
                'updated_at' => Schema::hasColumn('students', 'updated_at') ? now() : DB::raw('updated_at'),
            ]);

            return response()->json(['message' => 'Discount Generate Successfully!'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed', 'exception' => $e->getMessage()], 422);
        }
    }

    public function dues(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('students') || !Schema::hasTable('hostel_fee_generate_details') || !Schema::hasTable('hostel_fee_generates')) {
            return response()->json(['current_page' => 1, 'data' => [], 'from' => null, 'last_page' => 1, 'per_page' => 10, 'to' => null, 'total' => 0]);
        }

        // This endpoint is used by HostelPaymentDues.vue.
        // Implementation mirrors old ERP approach but uses query builder.

        $departmentId = $request->input('department_id');
        if ($departmentId === null || $departmentId === '') {
            $departmentId = Auth::guard('admin')->user()->department_id ?? null;
        }

        $studentsQ = DB::table('students as s')
            ->leftJoin('academic_classes as ac', 'ac.id', '=', 's.academic_class_id')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 's.academic_session_id')
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 's.academic_qualification_id')
            ->leftJoin('departments as dep', 'dep.id', '=', 's.department_id')
            ->leftJoin('hostels as h', 'h.id', '=', 's.hostel_id')
            ->select([
                's.id',
                's.academic_class_id',
                's.academic_qualification_id',
                's.academic_session_id',
                's.department_id',
                's.hostel_admission_date',
                's.hostel_release_date',
                DB::raw('s.name as student_name'),
                's.mobile',
                's.student_id',
                's.admission_id',
                's.hostel_id',
                DB::raw('ac.name as academic_class_name'),
                DB::raw('aq.name as qualification_name'),
                DB::raw('dep.name as department_name'),
                DB::raw('ses.name as session_name'),
                DB::raw('h.name as hostel_name'),
            ])
            ->where('s.status', 'active')
            ->orderByDesc('s.id');

        foreach ([
            'academic_class_id' => 's.academic_class_id',
            'academic_qualification_id' => 's.academic_qualification_id',
            'academic_session_id' => 's.academic_session_id',
            'hostel_id' => 's.hostel_id',
        ] as $key => $col) {
            $v = $request->input($key);
            if ($v !== null && $v !== '') {
                $studentsQ->where($col, (int) $v);
            }
        }

        if ($departmentId !== null && $departmentId !== '') {
            $studentsQ->where('s.department_id', (int) $departmentId);
        }

        $field = (string) ($request->input('field_name') ?? 'mobile');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '') {
            $allowed = ['mobile', 'student_id', 'name', 'admission_id'];
            if (in_array($field, $allowed, true) && Schema::hasColumn('students', $field)) {
                $studentsQ->where("s.{$field}", 'like', "%{$value}%");
            }
        }

        $students = $studentsQ->get();

        $finYearId = (int) ($request->input('financial_year_id') ?? 0);
        if ($finYearId <= 0) {
            return response()->json(['current_page' => 1, 'data' => [], 'from' => null, 'last_page' => 1, 'per_page' => 10, 'to' => null, 'total' => 0]);
        }

        $headMap = Schema::hasTable('account_heads') ? DB::table('account_heads')->pluck('name', 'id')->toArray() : [];

        $out = [];
        foreach ($students as $s) {
            $gen = DB::table('hostel_fee_generates')
                ->where('hostel_id', (int) $s->hostel_id)
                ->where('financial_year_id', $finYearId)
                ->first();
            if (!$gen) {
                continue;
            }

            // already paid fees ids
            $paidIds = [];
            if ($this->detailsTable() && $this->paymentsTable()) {
                $paidIds = DB::table('hostel_payment_details as d')
                    ->join('hostel_payments as hp', 'hp.id', '=', 'd.hostel_payment_id')
                    ->where('hp.student_id', (int) $s->id)
                    ->where('hp.hostel_id', (int) $s->hostel_id)
                    ->where('d.financial_year_id', $finYearId)
                    ->pluck('d.hostel_fee_generate_details_id')
                    ->toArray();
            }

            $duesQ = DB::table('hostel_fee_generate_details as d')
                ->where('d.hostel_fee_generate_id', (int) $gen->id)
                ->whereNotIn('d.id', $paidIds);

            if ($request->input('dues_month')) {
                $duesQ->whereMonth('d.date', (int) $request->input('dues_month'));
            }
            if ($request->input('account_head_id')) {
                $duesQ->where('d.account_head_id', (int) $request->input('account_head_id'));
            }

            $dues = $duesQ->orderBy('d.sorting')->get();

            $sum = 0;
            $duesFees = [];
            foreach ($dues as $d) {
                $sum += (float) ($d->amount ?? 0);
                $d->head = ['name' => $headMap[$d->account_head_id] ?? null];
                $duesFees[] = $d;
            }

            if ($sum <= 0) continue;

            $s->total_dues = $sum;
            $s->dues_fees = $duesFees;

            // map nested objects for vue
            $s->academic_session = ['name' => $s->session_name ?? null];
            $s->department = ['name' => $s->department_name ?? null];
            $s->qualification = ['name' => $s->qualification_name ?? null];
            $s->academic_class = ['name' => $s->academic_class_name ?? null];
            $s->hostel = ['name' => $s->hostel_name ?? null];

            unset($s->session_name, $s->department_name, $s->qualification_name, $s->academic_class_name, $s->hostel_name);

            $out[] = $s;
        }

        return response()->json([
            'current_page' => 1,
            'data' => array_values($out),
            'from' => 1,
            'last_page' => 1,
            'per_page' => count($out),
            'to' => count($out),
            'total' => count($out),
        ]);
    }
}
