<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceController extends Controller
{
    private function invoiceBaseQuery(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $deptID = $admin->department_id ?? null;
        $departmentId = $request->filled('department_id') ? $request->input('department_id') : $deptID;

        $query = DB::table('invoices as inv')
            ->leftJoin('students as std', 'std.id', '=', 'inv.student_id')
            ->leftJoin('online_admissions as oa', 'oa.id', '=', 'inv.online_admission_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'inv.department_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'inv.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'inv.academic_class_id')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'inv.academic_session_id')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'inv.account_head_id')
            ->where('inv.amount', '!=', 0);

        if (!empty($departmentId) && Schema::hasColumn('invoices', 'department_id')) {
            $query->where('inv.department_id', $departmentId);
        }

        return $query;
    }

    private function applyFilters($query, Request $request)
    {
        $field = (string) $request->input('field_name');
        $value = $request->input('value');

        if ($field !== '' && $value !== null && $value !== '') {
            if ($field === 'invoice_number') {
                $query->where('inv.invoice_number', 'like', '%' . $value . '%');
            } elseif ($field === 'admission_id') {
                $query->where('std.admission_id', $value);
            } else {
                $allowed = [
                    'student_id' => 'std.student_id',
                    'name' => 'std.name',
                    'mobile' => 'std.mobile',
                    'college_roll' => 'std.college_roll',
                    'reg_no' => 'std.reg_no',
                ];
                if (isset($allowed[$field])) {
                    $col = $allowed[$field];
                    $query->where($col, 'like', '%' . $value . '%');
                }
            }
        }

        if ($request->boolean('date')) {
            $query->whereDate('inv.payment_date', date('Y-m-d'));
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if (!empty($from) && !empty($to)) {
            $query->whereBetween('inv.payment_date', [$from, $to]);
        } elseif (!empty($from)) {
            $query->whereDate('inv.payment_date', '>=', $from);
        } elseif (!empty($to)) {
            $query->whereDate('inv.payment_date', '<=', $to);
        }

        foreach (['academic_class_id', 'academic_qualification_id', 'academic_session_id', 'account_head_id', 'exam_id', 'examination_year'] as $k) {
            if ($request->filled($k)) {
                $query->where('inv.' . $k, $request->input($k));
            }
        }

        if ($request->filled('student_type')) {
            $query->where('std.student_type', $request->input('student_type'));
        }

        if ($request->filled('payment_gateway_id')) {
            $gatewayId = $request->input('payment_gateway_id');
            if (Schema::hasTable('payment_gateways') && Schema::hasColumn('payment_gateways', 'store_id')) {
                $storeId = DB::table('payment_gateways')->where('id', $gatewayId)->value('store_id');
                if ($storeId) {
                    $gateways = DB::table('payment_gateways')->where('store_id', $storeId)->pluck('id')->all();
                    if (!empty($gateways)) {
                        $query->whereIn('inv.payment_gateway_id', $gateways);
                    }
                } else {
                    $query->where('inv.payment_gateway_id', $gatewayId);
                }
            } else {
                $query->where('inv.payment_gateway_id', $gatewayId);
            }
        }

        $status = $request->input('status');
        if (!empty($status)) {
            if ($status === 'pending') {
                $query->where('inv.status', '!=', 'success');
            } else {
                $query->where('inv.status', $status);
            }
        }

        return $query;
    }

    private function selectForList($query)
    {
        $select = [
            'inv.id',
            'inv.invoice_number',
            'inv.invoice_date',
            'inv.payment_date',
            'inv.amount',
            'inv.status',
            'inv.admission_id',
            'inv.college_roll',
            'inv.reg_no',
            'inv.account_head_id',
            'inv.department_id',
            'inv.academic_qualification_id',
            'inv.academic_class_id',
            'inv.academic_session_id',
            'inv.payment_gateway_id',
            'inv.exam_id',
            'inv.examination_year',
            'std.id as student_db_id',
            'std.student_id as student_student_id',
            'std.name as student_name',
            'std.mobile as student_mobile',
            'std.admission_id as student_admission_id',
            'std.college_roll as student_college_roll',
            'std.reg_no as student_reg_no',
            'std.student_type as student_type',
            'oa.id as online_admission_db_id',
            'oa.name as online_admission_name',
            'oa.mobile as online_admission_mobile',
            'oa.admission_roll as online_admission_roll',
            'oa.registration_no as online_admission_registration_no',
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

        if (Schema::hasColumn('invoices', 'refund_amount')) {
            $select[] = 'inv.refund_amount';
        } else {
            $select[] = DB::raw('0 as refund_amount');
        }

        if (Schema::hasColumn('invoices', 'refund_date')) {
            $select[] = 'inv.refund_date';
        }

        if (Schema::hasColumn('invoices', 'refund_note')) {
            $select[] = 'inv.refund_note';
        }

        return $query->select($select);
    }

    private function mapRow($r)
    {
        return [
            'id' => (int) ($r->id ?? 0),
            'invoice_number' => $r->invoice_number,
            'invoice_date' => $r->invoice_date,
            'payment_date' => $r->payment_date,
            'amount' => $r->amount,
            'refund_amount' => $r->refund_amount ?? 0,
            'refund_date' => $r->refund_date ?? null,
            'refund_note' => $r->refund_note ?? null,
            'status' => $r->status,
            'admission_id' => $r->admission_id,
            'college_roll' => $r->college_roll,
            'reg_no' => $r->reg_no,
            'department' => $r->dept_id ? ['id' => (int) $r->dept_id, 'name' => $r->dept_name] : null,
            'qualification' => $r->q_id ? ['id' => (int) $r->q_id, 'name' => $r->q_name] : null,
            'academic_class' => $r->cls_id ? ['id' => (int) $r->cls_id, 'name' => $r->cls_name] : null,
            'academic_session' => $r->ses_id ? ['id' => (int) $r->ses_id, 'name' => $r->ses_name] : null,
            'head' => $r->ah_id ? ['id' => (int) $r->ah_id, 'name' => $r->ah_name] : null,
            'student' => $r->student_db_id ? [
                'id' => (int) $r->student_db_id,
                'student_id' => $r->student_student_id,
                'name' => $r->student_name,
                'mobile' => $r->student_mobile,
                'admission_id' => $r->student_admission_id,
                'college_roll' => $r->student_college_roll,
                'reg_no' => $r->student_reg_no,
                'student_type' => $r->student_type,
            ] : null,
            'online_admission' => $r->online_admission_db_id ? [
                'id' => (int) $r->online_admission_db_id,
                'name' => $r->online_admission_name,
                'mobile' => $r->online_admission_mobile,
                'admission_roll' => $r->online_admission_roll,
                'registration_no' => $r->online_admission_registration_no,
            ] : null,
        ];
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('invoices')) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 15);
        $perPage = $perPage > 0 ? min($perPage, 200) : 15;

        $query = $this->invoiceBaseQuery($request);
        $query = $this->applyFilters($query, $request);
        $query = $this->selectForList($query);
        $query->orderByDesc('inv.id');

        $datas = $query->paginate($perPage);
        $datas->getCollection()->transform(function ($r) {
            return $this->mapRow($r);
        });

        return response()->json($datas);
    }

    public function todayPayments(Request $request)
    {
        $request->merge([
            'status' => $request->input('status') ?? 'success',
            'date' => $request->boolean('date') ? true : true,
        ]);

        $admin = Auth::guard('admin')->user();
        $departmentId = $admin->department_id ?? null;
        $deptKey = !empty($departmentId) ? (int) $departmentId : 0;
        $date = date('Y-m-d');
        $page = (int) ($request->input('page') ?? 1);
        $perPage = (int) ($request->input('pagination') ?? 15);
        $perPage = $perPage > 0 ? min($perPage, 200) : 15;

        $cacheKey = "today_payments_{$deptKey}_{$date}_p{$page}_pp{$perPage}";

        $data = Cache::remember($cacheKey, 30, function () use ($request) {
            $resp = $this->index($request);
            return method_exists($resp, 'getData') ? $resp->getData(true) : [];
        });

        return response()->json($data);
    }

    public function refundAmount(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('invoices')) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $query = $this->invoiceBaseQuery($request);

        if (Schema::hasColumn('invoices', 'refund_amount')) {
            $query->where('inv.refund_amount', '!=', 0);
        } else {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate($perPage));
        }

        $field = (string) $request->input('field_name');
        $value = $request->input('value');
        if ($field !== '' && $value !== null && $value !== '') {
            if ($field === 'invoice_number') {
                $query->where('inv.invoice_number', 'like', '%' . $value . '%');
            } elseif ($field === 'admission_id') {
                $query->where('std.admission_id', $value);
            } else {
                $allowed = [
                    'student_id' => 'std.student_id',
                    'name' => 'std.name',
                    'mobile' => 'std.mobile',
                    'reg_no' => 'std.reg_no',
                ];
                if (isset($allowed[$field])) {
                    $query->where($allowed[$field], 'like', '%' . $value . '%');
                }
            }
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if (!empty($from) && !empty($to)) {
            $query->whereBetween('inv.payment_date', [$from, $to]);
        } elseif (!empty($from)) {
            $query->whereDate('inv.payment_date', '>=', $from);
        } elseif (!empty($to)) {
            $query->whereDate('inv.payment_date', '<=', $to);
        }

        foreach (['academic_class_id', 'academic_qualification_id', 'academic_session_id'] as $k) {
            if ($request->filled($k)) {
                $query->where('inv.' . $k, $request->input($k));
            }
        }

        if ($request->filled('department_id') && Schema::hasColumn('invoices', 'department_id')) {
            $query->where('inv.department_id', $request->input('department_id'));
        }

        $query = $this->selectForList($query);
        $query->orderByDesc('inv.id');

        $datas = $query->paginate($perPage);
        $datas->getCollection()->transform(function ($r) {
            return $this->mapRow($r);
        });

        return response()->json($datas);
    }

    public function show(Request $request, $id)
    {
        if (!Schema::hasTable('invoices')) {
            return response()->json([], 404);
        }

        $query = $this->selectForList($this->invoiceBaseQuery($request))->where('inv.id', (int) $id);
        $row = $query->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json($this->mapRow($row));
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = 'INV-';
        $last = DB::table('invoices')
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $lastNum = 0;
        if (is_string($last) && preg_match('/^INV\-(\d+)$/', $last, $m)) {
            $lastNum = (int) ($m[1] ?? 0);
        }
        $next = $lastNum + 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'account_head_id' => ['required'],
            'student_id' => ['required'],
            'payment_gateway_id' => ['required'],
            'amount' => ['required'],
        ]);

        if (!Schema::hasTable('invoices') || !Schema::hasTable('students')) {
            return response()->json(['message' => 'Invoice module not ready'], 422);
        }

        $student = DB::table('students')->where('id', (int) $request->input('student_id'))->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $invoiceNumber = $this->nextInvoiceNumber();

        $feeRow = null;
        if (Schema::hasTable('fee_setups') && Schema::hasTable('fee_setup_details')) {
            $feeSetup = DB::table('fee_setups')->where([
                'academic_qualification_id' => (int) $student->academic_qualification_id,
                'department_id' => (int) $student->department_id,
                'academic_class_id' => (int) $student->academic_class_id,
            ])->first();

            if ($feeSetup) {
                $feeRow = DB::table('fee_setup_details')
                    ->where('fee_setup_id', $feeSetup->id)
                    ->where('account_head_id', (int) $request->input('account_head_id'))
                    ->first();
            }
        }

        $payload = [
            'student_id' => (int) $request->input('student_id'),
            'online_admission_id' => null,
            'student_migration_id' => null,
            'academic_session_id' => (int) $request->input('academic_session_id'),
            'department_id' => (int) $request->input('department_id'),
            'academic_qualification_id' => (int) $request->input('academic_qualification_id'),
            'academic_class_id' => (int) $request->input('academic_class_id'),
            'account_head_id' => (int) $request->input('account_head_id'),
            'payment_gateway_id' => $request->input('payment_gateway_id'),
            'exam_id' => $feeRow->exam_id ?? null,
            'examination_year' => $feeRow->examination_year ?? null,
            'invoice_date' => date('Y-m-d'),
            'invoice_number' => $invoiceNumber,
            'amount' => (float) $request->input('amount'),
            'paid_amount' => null,
            'payment_date' => null,
            'status' => 'pending',
            'created_from' => 'web',
            'admission_id' => $student->admission_id ?? null,
            'college_roll' => $student->college_roll ?? null,
            'reg_no' => $student->reg_no ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('invoices')->insert($payload);

        return response()->json(['message' => 'Create Successfully!'], 200);
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('invoices')) {
            return response()->json(['message' => 'Invoice module not ready'], 422);
        }

        $invoice = DB::table('invoices')->where('id', (int) $id)->first();
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $updateType = (string) ($request->input('update_type') ?? 'info_only');

        $updateData = [
            'refund_amount' => $request->input('refund_amount'),
            'refund_date' => $request->input('refund_date'),
            'refund_note' => $request->input('refund_note'),
            'payment_date' => $request->input('payment_date'),
            'account_head_id' => $request->input('account_head_id'),
            'academic_session_id' => $request->input('academic_session_id'),
            'department_id' => $request->input('department_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'academic_class_id' => $request->input('academic_class_id'),
            'admission_id' => $request->input('admission_roll'),
            'college_roll' => $request->input('college_roll'),
            'reg_no' => $request->input('reg_no'),
            'amount' => $request->input('amount'),
            'exam_id' => $request->input('exam_id'),
            'examination_year' => $request->input('examination_year'),
            'updated_at' => now(),
        ];

        $updateData = array_filter($updateData, function ($v) {
            return $v !== null;
        });

        if ($updateType === 'paid_and_update') {
            if (($invoice->status ?? '') !== 'success') {
                $updateData['status'] = 'success';
                $updateData['payment_date'] = $request->input('payment_date') ?? date('Y-m-d');
                $updateData['paid_amount'] = $request->input('amount') ?? $invoice->amount;
                $updateData['bank_trans_id'] = $invoice->bank_trans_id ?? ('manually/' . date('Y-m-d'));
                $updateData['card_type'] = $invoice->card_type ?? 'BKASH-BKash';
            }
        }

        DB::table('invoices')->where('id', (int) $id)->update($updateData);

        if (Schema::hasTable('accounts') && Schema::hasColumn('accounts', 'invoice_id')) {
            $inv = DB::table('invoices')->where('id', (int) $id)->first();
            if ($inv && !empty($inv->invoice_number)) {
                $acc = DB::table('accounts')->where('invoice_number', $inv->invoice_number)->first();
                $accPayload = (array) $inv;
                $accPayload['invoice_id'] = $inv->id;
                if ($acc) {
                    if (Schema::hasColumn('accounts', 'amount')) {
                        DB::table('accounts')->where('id', $acc->id)->update(['amount' => $inv->amount]);
                    }
                } else {
                    DB::table('accounts')->insert($accPayload);
                }
            }
        }

        return response()->json(['message' => 'Updated Successfully!'], 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!Schema::hasTable('invoices')) {
            return response()->json(['message' => 'Invoice module not ready'], 422);
        }

        $invoice = DB::table('invoices')->where('id', (int) $id)->first();
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if (Schema::hasTable('accounts') && Schema::hasColumn('accounts', 'invoice_id')) {
            DB::table('accounts')->where('invoice_id', (int) $id)->delete();
        }

        DB::table('invoices')->where('id', (int) $id)->delete();

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function invoicePrint(Request $request)
    {
        if (!Schema::hasTable('invoices')) {
            return response()->json([]);
        }

        $query = $this->invoiceBaseQuery($request);
        $query = $this->applyFilters($query, $request);
        $query = $this->selectForList($query);
        $query->orderByDesc('inv.id');

        $rows = $query->get();
        $out = [];
        foreach ($rows as $r) {
            $out[] = $this->mapRow($r);
        }

        return response()->json($out);
    }

    public function accountWise()
    {
        return view('layouts.backend_app');
    }

    public function accountHeadWise()
    {
        return view('layouts.backend_app');
    }

    private function normalizeIdList($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value)));
        }

        $s = trim((string) $value);
        if ($s === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/', $s) ?: [];
        return array_values(array_filter(array_map('intval', $parts)));
    }

    private function applyDateRange($query, string $col, Request $request)
    {
        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if (!empty($from) && !empty($to)) {
            $query->whereBetween($col, [$from, $to]);
        } elseif (!empty($from)) {
            $query->whereDate($col, '>=', $from);
        } elseif (!empty($to)) {
            $query->whereDate($col, '<=', $to);
        }

        return $query;
    }

    private function invoiceSummary(Request $request): array
    {
        if (!Schema::hasTable('invoices') || !Schema::hasColumn('invoices', 'payment_date') || !Schema::hasColumn('invoices', 'status')) {
            return [];
        }

        $headIds = $this->normalizeIdList($request->input('account_head_id'));

        $query = DB::table('invoices as inv')
            ->where('inv.status', 'success');

        if (!empty($headIds) && Schema::hasColumn('invoices', 'account_head_id')) {
            $query->whereIn('inv.account_head_id', $headIds);
        }

        if ($request->filled('store_id') && Schema::hasTable('payment_gateways') && Schema::hasColumn('payment_gateways', 'store_id')) {
            $gatewayIds = DB::table('payment_gateways')->where('store_id', $request->input('store_id'))->pluck('id')->all();
            if (!empty($gatewayIds) && Schema::hasColumn('invoices', 'payment_gateway_id')) {
                $query->whereIn('inv.payment_gateway_id', $gatewayIds);
            }
        }

        $query = $this->applyDateRange($query, 'inv.payment_date', $request);

        if (!Schema::hasTable('departments') || !Schema::hasTable('account_heads')) {
            return [];
        }

        $rows = $query
            ->join('departments as dept', 'dept.id', '=', 'inv.department_id')
            ->join('account_heads as ah', 'ah.id', '=', 'inv.account_head_id')
            ->select([
                'dept.id as dept_id',
                'dept.name as dept_name',
                'ah.id as head_id',
                'ah.name as head_name',
                DB::raw('ah.name as head_name_en'),
                'inv.academic_qualification_id',
                DB::raw('MAX(inv.amount) as head_amount'),
                DB::raw('SUM(inv.amount) as total_amount'),
                DB::raw('COUNT(inv.student_id) as total_student'),
            ])
            ->groupBy('inv.department_id', 'inv.account_head_id', 'inv.academic_qualification_id', 'dept.id', 'dept.name', 'ah.id', 'ah.name')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $k = (string) ($r->academic_qualification_id ?? '');
            if ($k === '') {
                $k = '0';
            }
            if (!isset($out[$k])) {
                $out[$k] = [];
            }
            $out[$k][] = (array) $r;
        }

        return $out;
    }

    private function admissionSummary(Request $request): array
    {
        if (!Schema::hasTable('admissions') || !Schema::hasColumn('admissions', 'payment_date') || !Schema::hasColumn('admissions', 'status')) {
            return [];
        }

        $headIds = $this->normalizeIdList($request->input('account_head_id'));

        $query = DB::table('admissions as adm')
            ->where('adm.status', 'success');

        if (!empty($headIds) && Schema::hasColumn('admissions', 'account_head_id')) {
            $query->whereIn('adm.account_head_id', $headIds);
        }

        if ($request->filled('store_id') && Schema::hasTable('admission_fee_setups') && Schema::hasColumn('admission_fee_setups', 'store_id')) {
            $setupIds = DB::table('admission_fee_setups')->where('store_id', $request->input('store_id'))->pluck('id')->all();
            if (!empty($setupIds) && Schema::hasColumn('admissions', 'admission_fee_setup_id')) {
                $query->whereIn('adm.admission_fee_setup_id', $setupIds);
            }
        }

        $query = $this->applyDateRange($query, 'adm.payment_date', $request);

        if (!Schema::hasTable('departments') || !Schema::hasTable('account_heads')) {
            return [];
        }

        $rows = $query
            ->join('departments as dept', 'dept.id', '=', 'adm.department_id')
            ->join('account_heads as ah', 'ah.id', '=', 'adm.account_head_id')
            ->select([
                'dept.id as dept_id',
                'dept.name as dept_name',
                'ah.id as head_id',
                'ah.name as head_name',
                DB::raw('ah.name as head_name_en'),
                'adm.academic_qualification_id',
                DB::raw('MAX(adm.amount) as head_amount'),
                DB::raw('SUM(adm.amount) as total_amount'),
                DB::raw('COUNT(adm.mobile) as total_student'),
            ])
            ->groupBy('adm.department_id', 'adm.account_head_id', 'adm.academic_qualification_id', 'dept.id', 'dept.name', 'ah.id', 'ah.name')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $k = (string) ($r->academic_qualification_id ?? '');
            if ($k === '') {
                $k = '0';
            }
            if (!isset($out[$k])) {
                $out[$k] = [];
            }
            $out[$k][] = (array) $r;
        }

        return $out;
    }

    private function allSummary(Request $request): array
    {
        $invoices = $this->invoiceSummary($request);
        $admissions = $this->admissionSummary($request);

        $result = array_intersect_key($invoices, $admissions);
        $matchKeys = array_keys($result);

        $summaries = $invoices;
        $summaries += $admissions;

        foreach ($summaries as $key => $summary) {
            if (in_array($key, $matchKeys, true)) {
                $summaries[$key] = array_merge($admissions[$key], $invoices[$key]);
            }
        }

        return $summaries;
    }

    public function accountSummary(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $reportType = (string) $request->input('report_type');

        switch ($reportType) {
            case 'invoice':
                $summary = $this->invoiceSummary($request);
                break;
            case 'admission':
                $summary = $this->admissionSummary($request);
                break;
            case 'all':
                $summary = $this->allSummary($request);
                break;
            default:
                $summary = [];
                break;
        }

        return response()->json($summary);
    }
}
