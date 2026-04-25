<?php

namespace App\Http\Controllers;

use App\Traits\Lib\DynamicDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class PublicApplyFeesController extends Controller
{
    use DynamicDataTrait;

    private function initiateSslCommerz(
        string $storeId,
        string $storePass,
        float $amount,
        string $tranId,
        string $productName,
        string $customerName,
        string $customerPhone,
        string $successUrl,
        string $failUrl,
        string $cancelUrl,
        string $ipnUrl
    ): array {
        $response = Http::asForm()->timeout(60)->post('https://securepay.sslcommerz.com/gwprocess/v4/api.php', [
            'store_id' => $storeId,
            'store_passwd' => $storePass,
            'total_amount' => $amount,
            'currency' => 'BDT',
            'tran_id' => $tranId,
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
            'cancel_url' => $cancelUrl,
            'ipn_url' => $ipnUrl,
            'shipping_method' => 'NO',
            'product_name' => $productName,
            'product_category' => $productName,
            'product_profile' => 'general',
            'cus_name' => $customerName,
            'cus_email' => 'student@example.com',
            'cus_add1' => 'N/A',
            'cus_city' => 'N/A',
            'cus_state' => 'N/A',
            'cus_postcode' => '0000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $customerPhone,
        ]);

        if (!$response->successful()) {
            return ['ok' => false, 'message' => 'Failed to initiate payment gateway'];
        }

        $data = $response->json();
        $url = $data['GatewayPageURL'] ?? null;
        if (!$url) {
            $msg = is_array($data) ? ($data['failedreason'] ?? $data['message'] ?? null) : null;
            return ['ok' => false, 'message' => $msg ?: 'Failed to initiate payment gateway'];
        }

        return ['ok' => true, 'gateway_url' => $url];
    }

    private function nextAdmissionInvoiceNumber(): string
    {
        $prefix = 'ADM-';
        if (!Schema::hasTable('admissions') || !Schema::hasColumn('admissions', 'invoice_number')) {
            return $prefix . date('YmdHis');
        }

        $last = DB::table('admissions')
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $lastNum = 0;
        if (is_string($last) && preg_match('/^ADM\-(\d+)$/', $last, $m)) {
            $lastNum = (int) ($m[1] ?? 0);
        }
        $next = $lastNum + 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function purposes(): array
    {
        if (!Schema::hasTable('admission_fee_setups') || !Schema::hasTable('account_heads')) {
            return [];
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
                'afs.account_no',
                'ah.name as head_name',
            ])
            ->orderBy('afs.id')
            ->get();

        return $rows->map(function ($r) {
            return [
                'id' => (int) ($r->id ?? 0),
                'account_head_id' => (int) ($r->account_head_id ?? 0),
                'amount' => (float) ($r->amount ?? 0),
                'account_no' => $r->account_no ?? null,
                'head' => [
                    'id' => (int) ($r->account_head_id ?? 0),
                    'name' => $r->head_name ?? '',
                ],
            ];
        })->values()->all();
    }

    public function systems(Request $request)
    {
        return response()->json([
            'global' => array_merge($this->dynamicData(), [
                'purposes' => $this->purposes(),
            ]),
        ]);
    }

    public function init(Request $request)
    {
        $request->validate([
            'purpose_id' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'name' => ['required'],
            'mobile' => ['required', 'digits:11', 'regex:/^01[0-9]+$/'],
            'admission_roll' => ['required'],
        ]);

        if (!Schema::hasTable('admission_fee_setups')) {
            return response()->json(['message' => 'Admission fee setup module not ready'], 422);
        }

        $setup = DB::table('admission_fee_setups')->where('id', (int) $request->input('purpose_id'))->first();
        if (!$setup) {
            return response()->json(['message' => 'Purpose not found'], 404);
        }

        $storeId = $setup->store_id ?? null;
        $storePass = $setup->store_password ?? null;
        if (!$storeId || !$storePass) {
            return response()->json(['message' => 'Payment gateway not configured for this purpose'], 422);
        }

        $amount = (float) ($setup->amount ?? 0);
        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], 422);
        }

        if (!Schema::hasTable('admissions')) {
            return response()->json(['message' => 'Admission module not ready'], 422);
        }

        $invoiceNumber = $this->nextAdmissionInvoiceNumber();

        $payload = [
            'invoice_number' => $invoiceNumber,
            'invoice_date' => date('Y-m-d'),
            'amount' => $amount,
            'status' => 'pending',
            'account_head_id' => $setup->account_head_id ?? null,
            'admission_fee_setup_id' => $setup->id,
            'academic_session_id' => $request->input('academic_session_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'department_id' => $request->input('department_id'),
            'academic_class_id' => $request->input('academic_class_id'),
            'name' => strtoupper((string) $request->input('name')),
            'mobile' => $request->input('mobile'),
            'admission_roll' => $request->input('admission_roll'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        foreach (['store_id' => $storeId, 'store_password' => $storePass, 'account_no' => ($setup->account_no ?? null)] as $k => $v) {
            if ($v !== null && Schema::hasColumn('admissions', $k)) {
                $payload[$k] = $v;
            }
        }

        if (Schema::hasColumn('admissions', 'paid_amount')) {
            $payload['paid_amount'] = null;
        }
        if (Schema::hasColumn('admissions', 'payment_date')) {
            $payload['payment_date'] = null;
        }

        $id = DB::table('admissions')->insertGetId($payload);

        $headName = '';
        if (Schema::hasTable('account_heads')) {
            $headName = (string) (DB::table('account_heads')->where('id', (int) ($setup->account_head_id ?? 0))->value('name') ?? '');
        }

        $successUrl = url('/api/public/apply-fees/success');
        $failUrl = url('/api/public/apply-fees/fail');
        $cancelUrl = url('/api/public/apply-fees/cancel');
        $ipnUrl = url('/api/public/apply-fees/ipn');

        $productName = $headName !== '' ? $headName : 'Application Fee';
        $init = $this->initiateSslCommerz(
            (string) $storeId,
            (string) $storePass,
            (float) $amount,
            (string) $invoiceNumber,
            $productName,
            (string) $request->input('name'),
            (string) $request->input('mobile'),
            $successUrl,
            $failUrl,
            $cancelUrl,
            $ipnUrl
        );

        if (!($init['ok'] ?? false)) {
            return response()->json(['message' => (string) ($init['message'] ?? 'Failed to initiate payment gateway')], 422);
        }

        return response()->json([
            'created' => true,
            'id' => $id,
            'invoice_number' => $invoiceNumber,
            'gateway_url' => $init['gateway_url'] ?? null,
        ], 200);
    }

    public function checkInvoice(Request $request)
    {
        $request->validate([
            'admission_roll' => ['required'],
        ]);

        if (!Schema::hasTable('admissions')) {
            return response()->json(['message' => 'Admission module not ready'], 422);
        }

        $q = DB::table('admissions')->orderByDesc('id');

        if (Schema::hasColumn('admissions', 'admission_roll')) {
            $q->where('admission_roll', (string) $request->input('admission_roll'));
        }
        if ($request->filled('mobile') && Schema::hasColumn('admissions', 'mobile')) {
            $q->where('mobile', (string) $request->input('mobile'));
        }
        if ($request->filled('purpose_id') && Schema::hasColumn('admissions', 'admission_fee_setup_id')) {
            $q->where('admission_fee_setup_id', (int) $request->input('purpose_id'));
        }
        foreach (['academic_session_id', 'academic_qualification_id', 'department_id', 'academic_class_id'] as $f) {
            if ($request->filled($f) && Schema::hasColumn('admissions', $f)) {
                $q->where($f, $request->input($f));
            }
        }

        $row = $q->first();
        if (!$row) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        return response()->json([
            'invoice_number' => $row->invoice_number ?? null,
            'amount' => $row->amount ?? null,
            'status' => $row->status ?? null,
            'payment_date' => $row->payment_date ?? null,
        ], 200);
    }

    public function payExisting(Request $request)
    {
        $request->validate([
            'invoice_number' => ['required'],
        ]);

        if (!Schema::hasTable('admissions')) {
            return response()->json(['message' => 'Admission module not ready'], 422);
        }

        $row = DB::table('admissions')->where('invoice_number', (string) $request->input('invoice_number'))->first();
        if (!$row) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if (($row->status ?? '') === 'success') {
            return response()->json(['message' => 'Invoice already paid'], 422);
        }

        $storeId = null;
        $storePass = null;

        if (Schema::hasColumn('admissions', 'store_id')) {
            $storeId = $row->store_id ?? null;
        }
        if (Schema::hasColumn('admissions', 'store_password')) {
            $storePass = $row->store_password ?? null;
        }

        if ((!$storeId || !$storePass) && Schema::hasTable('admission_fee_setups') && Schema::hasColumn('admissions', 'admission_fee_setup_id')) {
            $setup = DB::table('admission_fee_setups')->where('id', (int) ($row->admission_fee_setup_id ?? 0))->first();
            if ($setup) {
                $storeId = $storeId ?: ($setup->store_id ?? null);
                $storePass = $storePass ?: ($setup->store_password ?? null);
            }
        }

        if (!$storeId || !$storePass) {
            return response()->json(['message' => 'Payment gateway not configured for this invoice'], 422);
        }

        $amount = (float) ($row->amount ?? 0);
        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], 422);
        }

        $headName = '';
        if (Schema::hasTable('account_heads') && Schema::hasColumn('admissions', 'account_head_id')) {
            $headName = (string) (DB::table('account_heads')->where('id', (int) ($row->account_head_id ?? 0))->value('name') ?? '');
        }

        $successUrl = url('/api/public/apply-fees/success');
        $failUrl = url('/api/public/apply-fees/fail');
        $cancelUrl = url('/api/public/apply-fees/cancel');
        $ipnUrl = url('/api/public/apply-fees/ipn');

        $init = $this->initiateSslCommerz(
            (string) $storeId,
            (string) $storePass,
            $amount,
            (string) ($row->invoice_number ?? ''),
            $headName !== '' ? $headName : 'Application Fee',
            (string) ($row->name ?? 'Student'),
            (string) ($row->mobile ?? ''),
            $successUrl,
            $failUrl,
            $cancelUrl,
            $ipnUrl
        );

        if (!($init['ok'] ?? false)) {
            return response()->json(['message' => (string) ($init['message'] ?? 'Failed to initiate payment gateway')], 422);
        }

        return response()->json([
            'invoice_number' => $row->invoice_number ?? null,
            'gateway_url' => $init['gateway_url'] ?? null,
        ], 200);
    }

    /**
     * Check an application-fee invoice by admission_roll + mobile.
     * Used by the new "Check Invoice" tab on the Apply Fees page.
     */
    public function checkInvoiceByRoll(Request $request)
    {
        $request->validate([
            'admission_roll' => ['required', 'string'],
            'mobile'         => ['required', 'string'],
        ]);

        if (!Schema::hasTable('admissions')) {
            return response()->json(['message' => 'Admission module not ready'], 422);
        }

        $q = DB::table('admissions')->orderByDesc('id');

        if (Schema::hasColumn('admissions', 'admission_roll')) {
            $q->where('admission_roll', (string) $request->input('admission_roll'));
        }
        if (Schema::hasColumn('admissions', 'mobile')) {
            $q->where('mobile', (string) $request->input('mobile'));
        }

        $row = $q->first();
        if (!$row) {
            return response()->json(['message' => 'No invoice found for the given details.'], 404);
        }

        // Resolve related names
        $headName = '';
        if (Schema::hasTable('account_heads') && !empty($row->account_head_id ?? null)) {
            $headName = (string) (DB::table('account_heads')->where('id', (int) $row->account_head_id)->value('name') ?? '');
        }
        $sessionName = '';
        if (Schema::hasTable('academic_sessions') && !empty($row->academic_session_id ?? null)) {
            $sessionName = (string) (DB::table('academic_sessions')->where('id', (int) $row->academic_session_id)->value('name') ?? '');
        }
        $qualificationName = '';
        if (Schema::hasTable('academic_qualifications') && !empty($row->academic_qualification_id ?? null)) {
            $qualificationName = (string) (DB::table('academic_qualifications')->where('id', (int) $row->academic_qualification_id)->value('name') ?? '');
        }
        $departmentName = '';
        if (Schema::hasTable('departments') && !empty($row->department_id ?? null)) {
            $departmentName = (string) (DB::table('departments')->where('id', (int) $row->department_id)->value('name') ?? '');
        }
        $className = '';
        if (Schema::hasTable('academic_classes') && !empty($row->academic_class_id ?? null)) {
            $className = (string) (DB::table('academic_classes')->where('id', (int) $row->academic_class_id)->value('name') ?? '');
        }

        return response()->json([
            'invoice_number'   => $row->invoice_number ?? null,
            'invoice_date'     => $row->invoice_date ?? null,
            'amount'           => $row->amount ?? null,
            'status'           => $row->status ?? null,
            'payment_date'     => $row->payment_date ?? null,
            'name'             => $row->name ?? null,
            'mobile'           => $row->mobile ?? null,
            'admission_roll'   => $row->admission_roll ?? null,
            'head_name'        => $headName,
            'session_name'     => $sessionName,
            'qualification_name' => $qualificationName,
            'department_name'  => $departmentName,
            'class_name'       => $className,
        ], 200);
    }

    /**
     * Check a certificate-fee invoice by admission_roll (application_id) + mobile.
     */
    public function certificateCheckInvoiceByRoll(Request $request)
    {
        $request->validate([
            'admission_roll' => ['required', 'string'],
            'mobile'         => ['required', 'string'],
        ]);

        if (!Schema::hasTable('certificate_applications')) {
            return response()->json(['message' => 'Certificate module not ready'], 422);
        }

        $cols = Schema::getColumnListing('certificate_applications');
        $q = DB::table('certificate_applications')->orderByDesc('id');

        if (in_array('application_id', $cols, true)) {
            $q->where('application_id', (string) $request->input('admission_roll'));
        }
        if (in_array('mobile', $cols, true)) {
            $q->where('mobile', (string) $request->input('mobile'));
        }

        $row = $q->first();
        if (!$row) {
            return response()->json(['message' => 'No certificate invoice found for the given details.'], 404);
        }

        $status = null;
        if (in_array('payment_status', $cols, true)) {
            $status = $row->payment_status ?? null;
        } elseif (in_array('status', $cols, true)) {
            $status = $row->status ?? null;
        }

        // Resolve related names
        $headName = '';
        if (Schema::hasTable('account_heads') && !empty($row->account_head_id ?? null)) {
            $headName = (string) (DB::table('account_heads')->where('id', (int) $row->account_head_id)->value('name') ?? '');
        }
        $sessionName = '';
        if (Schema::hasTable('academic_sessions') && !empty($row->academic_session_id ?? null)) {
            $sessionName = (string) (DB::table('academic_sessions')->where('id', (int) $row->academic_session_id)->value('name') ?? '');
        }
        $qualificationName = '';
        if (Schema::hasTable('academic_qualifications') && !empty($row->academic_qualification_id ?? null)) {
            $qualificationName = (string) (DB::table('academic_qualifications')->where('id', (int) $row->academic_qualification_id)->value('name') ?? '');
        }
        $departmentName = '';
        if (Schema::hasTable('departments') && !empty($row->department_id ?? null)) {
            $departmentName = (string) (DB::table('departments')->where('id', (int) $row->department_id)->value('name') ?? '');
        }
        $className = '';
        if (Schema::hasTable('academic_classes') && !empty($row->academic_class_id ?? null)) {
            $className = (string) (DB::table('academic_classes')->where('id', (int) $row->academic_class_id)->value('name') ?? '');
        }
        $studentName = '';
        if (in_array('student_name_en', $cols, true)) $studentName = (string) ($row->student_name_en ?? '');
        if ($studentName === '' && in_array('application_id', $cols, true)) $studentName = (string) ($row->application_id ?? '');

        return response()->json([
            'invoice_number'     => $row->invoice_number ?? null,
            'invoice_date'       => $row->invoice_date ?? null,
            'amount'             => $row->amount ?? null,
            'status'             => $status,
            'payment_date'       => $row->payment_date ?? null,
            'name'               => $studentName,
            'mobile'             => $row->mobile ?? null,
            'admission_roll'     => $row->application_id ?? null,
            'head_name'          => $headName,
            'session_name'       => $sessionName,
            'qualification_name' => $qualificationName,
            'department_name'    => $departmentName,
            'class_name'         => $className,
        ], 200);
    }

    /**
     * Re-initiate payment for an existing certificate invoice.
     */
    public function certificatePayExisting(Request $request)
    {
        $request->validate([
            'invoice_number' => ['required', 'string'],
        ]);

        if (!Schema::hasTable('certificate_applications')) {
            return response()->json(['message' => 'Certificate module not ready'], 422);
        }

        $row = DB::table('certificate_applications')
            ->where('invoice_number', (string) $request->input('invoice_number'))
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $cols = Schema::getColumnListing('certificate_applications');

        $status = in_array('payment_status', $cols, true) ? ($row->payment_status ?? '') : ($row->status ?? '');
        if ($status === 'success') {
            return response()->json(['message' => 'Invoice already paid'], 422);
        }

        // Resolve gateway credentials from the linked template / payment_gateway
        $storeId   = null;
        $storePass = null;

        if (in_array('payment_gateway_id', $cols, true) && Schema::hasTable('payment_gateways')) {
            $gw = DB::table('payment_gateways')
                ->where('id', (int) ($row->payment_gateway_id ?? 0))
                ->first();
            if ($gw) {
                $storeId   = $gw->store_id ?? null;
                $storePass = $gw->store_password ?? null;
            }
        }

        // Fallback: try via certificate_template
        if ((!$storeId || !$storePass) && in_array('certificate_template_id', $cols, true) && Schema::hasTable('certificate_templates')) {
            $tmpl = DB::table('certificate_templates')
                ->where('id', (int) ($row->certificate_template_id ?? 0))
                ->first();
            if ($tmpl && Schema::hasTable('payment_gateways')) {
                $gw = DB::table('payment_gateways')
                    ->where('id', (int) ($tmpl->payment_gateway_id ?? 0))
                    ->first();
                if ($gw) {
                    $storeId   = $storeId   ?: ($gw->store_id       ?? null);
                    $storePass = $storePass ?: ($gw->store_password  ?? null);
                }
            }
        }

        if (!$storeId || !$storePass) {
            return response()->json(['message' => 'Payment gateway not configured for this invoice'], 422);
        }

        $amount = (float) ($row->amount ?? 0);
        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], 422);
        }

        $productName = 'Certificate Fee';
        if (in_array('account_head_id', $cols, true) && Schema::hasTable('account_heads')) {
            $productName = (string) (DB::table('account_heads')
                ->where('id', (int) ($row->account_head_id ?? 0))
                ->value('name') ?? $productName);
        }

        $studentName  = '';
        if (in_array('student_name_en', $cols, true)) $studentName = (string) ($row->student_name_en ?? '');
        if ($studentName === '' && in_array('application_id', $cols, true)) $studentName = (string) ($row->application_id ?? 'Student');

        $successUrl = url('/api/public/certificate/success');
        $failUrl    = url('/api/public/certificate/fail');
        $cancelUrl  = url('/api/public/certificate/cancel');
        $ipnUrl     = url('/api/public/certificate/ipn');

        $init = $this->initiateSslCommerz(
            (string) $storeId,
            (string) $storePass,
            $amount,
            (string) ($row->invoice_number ?? ''),
            $productName,
            $studentName ?: 'Student',
            (string) ($row->mobile ?? ''),
            $successUrl,
            $failUrl,
            $cancelUrl,
            $ipnUrl
        );

        if (!($init['ok'] ?? false)) {
            return response()->json(['message' => (string) ($init['message'] ?? 'Failed to initiate payment gateway')], 422);
        }

        return response()->json([
            'invoice_number' => $row->invoice_number ?? null,
            'gateway_url'    => $init['gateway_url'] ?? null,
        ], 200);
    }

    private function nextCertificateInvoiceNumber(): string
    {
        $prefix = 'CER-';
        if (!Schema::hasTable('certificate_applications') || !Schema::hasColumn('certificate_applications', 'invoice_number')) {
            return $prefix . date('YmdHis');
        }

        $last = DB::table('certificate_applications')
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $lastNum = 0;
        if (is_string($last) && preg_match('/^CER\-(\d+)$/', $last, $m)) {
            $lastNum = (int) ($m[1] ?? 0);
        }

        $next = $lastNum + 1;
        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function certificateTemplates(): array
    {
        if (!Schema::hasTable('certificate_templates')) {
            return [];
        }

        $cols = Schema::getColumnListing('certificate_templates');
        $select = ['id'];
        foreach (['title', 'amount', 'status', 'academic_qualification_id', 'account_head_id', 'payment_gateway_id', 'certificate_fees', 'print_layout'] as $c) {
            if (in_array($c, $cols, true)) {
                $select[] = $c;
            }
        }

        $q = DB::table('certificate_templates')->select($select)->orderByDesc('id');
        if (in_array('status', $cols, true)) {
            $q->where('status', 'active');
        }

        return $q->get()->map(function ($r) {
            return [
                'id' => (int) ($r->id ?? 0),
                'title' => $r->title ?? '',
                'amount' => (float) ($r->amount ?? 0),
                'academic_qualification_id' => (int) ($r->academic_qualification_id ?? 0),
                'account_head_id' => (int) ($r->account_head_id ?? 0),
                'payment_gateway_id' => (int) ($r->payment_gateway_id ?? 0),
                'certificate_fees' => $r->certificate_fees ?? null,
                'print_layout' => $r->print_layout ?? null,
            ];
        })->values()->all();
    }

    public function certificateSystems(Request $request)
    {
        return response()->json([
            'global' => array_merge($this->dynamicData(), [
                'templates' => $this->certificateTemplates(),
            ]),
        ]);
    }

    public function certificateLookup(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'digits:11', 'regex:/^01[0-9]+$/'],
        ]);

        if (!Schema::hasTable('students')) {
            return response()->json(['message' => 'Student module not ready'], 422);
        }

        $student = DB::table('students')->where('mobile', (string) $request->input('mobile'))->orderByDesc('id')->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'student' => [
                'name' => $student->name ?? '',
                'fathers_name' => $student->fathers_name ?? '',
                'mothers_name' => $student->mothers_name ?? '',
                'reg_no' => $student->reg_no ?? '',
                'college_roll' => $student->college_roll ?? '',
                'passing_year' => $student->passing_year ?? '',
                'academic_session_id' => $student->academic_session_id ?? null,
                'academic_qualification_id' => $student->academic_qualification_id ?? null,
                'department_id' => $student->department_id ?? null,
                'academic_class_id' => $student->academic_class_id ?? null,
            ],
        ], 200);
    }

    public function certificateInit(Request $request)
    {
        $rules = [
            'template_id' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'name' => ['required'],
            'mobile' => ['required', 'digits:11', 'regex:/^01[0-9]+$/'],
            'application_id' => ['nullable'],
        ];

        if (Schema::hasTable('certificate_applications')) {
            if (Schema::hasColumn('certificate_applications', 'department_id')) {
                $rules['department_id'] = ['required'];
            }
            if (Schema::hasColumn('certificate_applications', 'academic_class_id')) {
                $rules['academic_class_id'] = ['required'];
            }
        }

        if (Schema::hasTable('certificate_applications')) {
            $cols = Schema::getColumnListing('certificate_applications');

            if (in_array('certificate_type', $cols, true)) {
                $rules['certificate_type'] = ['required'];
            }

            $requiredIf = function (string $field) {
                return ['required_if:certificate_type,en,both'];
            };
            $requiredIfBn = function (string $field) {
                return ['required_if:certificate_type,bn,both'];
            };

            $pairs = [
                'student_name_en' => $requiredIf,
                'student_name_bn' => $requiredIfBn,
                'fathers_name_en' => $requiredIf,
                'fathers_name_bn' => $requiredIfBn,
                'mothers_name_en' => $requiredIf,
                'mothers_name_bn' => $requiredIfBn,
                'academic_year_en' => $requiredIf,
                'academic_year_bn' => $requiredIfBn,
                'registration_no_en' => $requiredIf,
                'registration_no_bn' => $requiredIfBn,
                'exam_roll_en' => $requiredIf,
                'exam_roll_bn' => $requiredIfBn,
                'exam_year_en' => $requiredIf,
                'exam_year_bn' => $requiredIfBn,
                'gpa_en' => $requiredIf,
                'gpa_bn' => $requiredIfBn,
                'division_en' => $requiredIf,
                'division_bn' => $requiredIfBn,
            ];

            foreach ($pairs as $field => $builder) {
                if (in_array($field, $cols, true)) {
                    $rules[$field] = $builder($field);
                }
            }
        }

        $request->validate($rules);

        if (!Schema::hasTable('certificate_templates') || !Schema::hasTable('certificate_applications')) {
            return response()->json(['message' => 'Certificate module not ready'], 422);
        }

        $template = DB::table('certificate_templates')->where('id', (int) $request->input('template_id'))->first();
        if (!$template) {
            return response()->json(['message' => 'Fees not found'], 404);
        }

        $amount = (float) ($template->amount ?? 0);
        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], 422);
        }

        $gateway = null;
        if (Schema::hasTable('payment_gateways')) {
            $gateway = DB::table('payment_gateways')->where('id', (int) ($template->payment_gateway_id ?? 0))->first();
        }
        $storeId = $gateway->store_id ?? null;
        $storePass = $gateway->store_password ?? null;
        if (!$storeId || !$storePass) {
            return response()->json(['message' => 'Payment gateway not configured for this fees'], 422);
        }

        $invoiceNumber = $this->nextCertificateInvoiceNumber();

        $cols = Schema::getColumnListing('certificate_applications');
        $payload = [];

        $appId = (string) ($request->input('application_id') ?? '');
        if ($appId === '') {
            $appId = (string) ($request->input('college_roll_en') ?? $request->input('registration_no_en') ?? '');
        }
        if ($appId === '') {
            $appId = (string) $request->input('mobile');
        }

        $map = [
            'invoice_number' => $invoiceNumber,
            'invoice_date' => date('Y-m-d'),
            'amount' => $amount,
            'academic_session_id' => $request->input('academic_session_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'department_id' => $request->input('department_id'),
            'academic_class_id' => $request->input('academic_class_id'),
            'certificate_template_id' => (int) $request->input('template_id'),
            'account_head_id' => $template->account_head_id ?? null,
            'payment_gateway_id' => $template->payment_gateway_id ?? null,
            'mobile' => (string) $request->input('mobile'),
            'application_id' => $appId,
            'certificate_type' => (string) ($request->input('certificate_type') ?? ''),
            'student_name_en' => strtoupper((string) ($request->input('student_name_en') ?? $request->input('name'))),
            'student_name_bn' => (string) ($request->input('student_name_bn') ?? ''),
            'fathers_name_en' => strtoupper((string) ($request->input('fathers_name_en') ?? '')),
            'fathers_name_bn' => (string) ($request->input('fathers_name_bn') ?? ''),
            'mothers_name_en' => strtoupper((string) ($request->input('mothers_name_en') ?? '')),
            'mothers_name_bn' => (string) ($request->input('mothers_name_bn') ?? ''),
            'academic_year_en' => (string) ($request->input('academic_year_en') ?? ''),
            'academic_year_bn' => (string) ($request->input('academic_year_bn') ?? ''),
            'registration_no_en' => (string) ($request->input('registration_no_en') ?? ''),
            'registration_no_bn' => (string) ($request->input('registration_no_bn') ?? ''),
            'exam_roll_en' => (string) ($request->input('exam_roll_en') ?? ''),
            'exam_roll_bn' => (string) ($request->input('exam_roll_bn') ?? ''),
            'exam_year_en' => (string) ($request->input('exam_year_en') ?? ''),
            'exam_year_bn' => (string) ($request->input('exam_year_bn') ?? ''),
            'gpa_en' => (string) ($request->input('gpa_en') ?? ''),
            'gpa_bn' => (string) ($request->input('gpa_bn') ?? ''),
            'division_en' => (string) ($request->input('division_en') ?? ''),
            'division_bn' => (string) ($request->input('division_bn') ?? ''),
            'payment_status' => 'pending',
            'application_status' => 'pending',
            'paid_amount' => null,
            'payment_date' => null,
        ];

        foreach ($map as $k => $v) {
            if (in_array($k, $cols, true)) {
                $payload[$k] = $v;
            }
        }

        if (in_array('created_at', $cols, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $cols, true)) {
            $payload['updated_at'] = now();
        }

        $id = DB::table('certificate_applications')->insertGetId($payload);

        $productName = 'Certificate Fee';
        if (Schema::hasTable('account_heads') && !empty($template->account_head_id ?? null)) {
            $productName = (string) (DB::table('account_heads')->where('id', (int) $template->account_head_id)->value('name') ?? $productName);
        }

        $successUrl = url('/api/public/certificate/success');
        $failUrl = url('/api/public/certificate/fail');
        $cancelUrl = url('/api/public/certificate/cancel');
        $ipnUrl = url('/api/public/certificate/ipn');

        $init = $this->initiateSslCommerz(
            (string) $storeId,
            (string) $storePass,
            $amount,
            $invoiceNumber,
            $productName,
            (string) $request->input('name'),
            (string) $request->input('mobile'),
            $successUrl,
            $failUrl,
            $cancelUrl,
            $ipnUrl
        );

        if (!($init['ok'] ?? false)) {
            return response()->json(['message' => (string) ($init['message'] ?? 'Failed to initiate payment gateway')], 422);
        }

        return response()->json([
            'created' => true,
            'id' => $id,
            'invoice_number' => $invoiceNumber,
            'gateway_url' => $init['gateway_url'] ?? null,
        ], 200);
    }

    private function updateCertificateStatus(string $tranId, string $status, Request $request)
    {
        if (!Schema::hasTable('certificate_applications') || !Schema::hasColumn('certificate_applications', 'invoice_number')) {
            return;
        }

        $cols = Schema::getColumnListing('certificate_applications');
        $update = [];

        if (in_array('payment_status', $cols, true)) {
            $update['payment_status'] = $status;
        }

        if ($status === 'success') {
            if (in_array('payment_date', $cols, true)) {
                $update['payment_date'] = date('Y-m-d');
            }
            if (in_array('paid_amount', $cols, true)) {
                $update['paid_amount'] = $request->input('amount') ?? null;
            }
        }

        foreach (['bank_trans_id' => 'bank_tran_id', 'card_type' => 'card_type', 'val_id' => 'val_id'] as $col => $in) {
            if (in_array($col, $cols, true) && $request->filled($in)) {
                $update[$col] = $request->input($in);
            }
        }

        if (in_array('updated_at', $cols, true)) {
            $update['updated_at'] = now();
        }

        if (!empty($update)) {
            DB::table('certificate_applications')->where('invoice_number', $tranId)->update($update);
        }
    }

    public function certificateSuccess(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateCertificateStatus($tranId, 'success', $request);
        }

        return redirect('/apply-fees?tab=certificate&status=success&tran_id=' . urlencode($tranId));
    }

    public function certificateFail(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateCertificateStatus($tranId, 'failed', $request);
        }

        return redirect('/apply-fees?tab=certificate&status=failed&tran_id=' . urlencode($tranId));
    }

    public function certificateCancel(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateCertificateStatus($tranId, 'cancelled', $request);
        }

        return redirect('/apply-fees?tab=certificate&status=cancelled&tran_id=' . urlencode($tranId));
    }

    public function certificateIpn(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        $status = strtolower((string) ($request->input('status') ?? ''));
        if ($tranId !== '' && $status !== '') {
            $map = [
                'valid' => 'success',
                'validated' => 'success',
                'success' => 'success',
                'failed' => 'failed',
                'cancelled' => 'cancelled',
            ];
            $this->updateCertificateStatus($tranId, $map[$status] ?? $status, $request);
        }

        return response()->json(['ok' => true], 200);
    }

    private function updateAdmissionStatus(string $tranId, string $status, Request $request)
    {
        if (!Schema::hasTable('admissions') || !Schema::hasColumn('admissions', 'invoice_number')) {
            return;
        }

        $update = [];
        if (Schema::hasColumn('admissions', 'status')) {
            $update['status'] = $status;
        }

        if ($status === 'success') {
            if (Schema::hasColumn('admissions', 'payment_date')) {
                $update['payment_date'] = date('Y-m-d');
            }
            if (Schema::hasColumn('admissions', 'paid_amount')) {
                $update['paid_amount'] = $request->input('amount') ?? null;
            }
        }

        foreach (['bank_trans_id' => 'bank_tran_id', 'card_type' => 'card_type', 'val_id' => 'val_id'] as $col => $in) {
            if (Schema::hasColumn('admissions', $col) && $request->filled($in)) {
                $update[$col] = $request->input($in);
            }
        }

        if (Schema::hasColumn('admissions', 'updated_at')) {
            $update['updated_at'] = now();
        }

        if (!empty($update)) {
            DB::table('admissions')->where('invoice_number', $tranId)->update($update);
        }
    }

    public function success(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateAdmissionStatus($tranId, 'success', $request);
        }

        return redirect('/apply-fees?status=success&tran_id=' . urlencode($tranId));
    }

    public function fail(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateAdmissionStatus($tranId, 'failed', $request);
        }

        return redirect('/apply-fees?status=failed&tran_id=' . urlencode($tranId));
    }

    public function cancel(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateAdmissionStatus($tranId, 'cancelled', $request);
        }

        return redirect('/apply-fees?status=cancelled&tran_id=' . urlencode($tranId));
    }

    public function ipn(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        $status = strtolower((string) ($request->input('status') ?? ''));
        if ($tranId !== '' && $status !== '') {
            $map = [
                'valid' => 'success',
                'validated' => 'success',
                'success' => 'success',
                'failed' => 'failed',
                'cancelled' => 'cancelled',
            ];
            $this->updateAdmissionStatus($tranId, $map[$status] ?? $status, $request);
        }

        return response()->json(['ok' => true], 200);
    }
}
