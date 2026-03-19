<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class PublicStudentPayNowController extends Controller
{
    private function nextInvoiceNumber(): string
    {
        $prefix = 'INV-';
        if (!Schema::hasTable('invoices') || !Schema::hasColumn('invoices', 'invoice_number')) {
            return $prefix . date('YmdHis');
        }

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

    private function resolveSiteConfig(): array
    {
        try {
            $cfg = app()->make('siteSettingObj');
            return is_array($cfg) ? $cfg : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function serviceCharge(float $amount): float
    {
        $config = $this->resolveSiteConfig();
        $pct = (float) ($config['service_charge_percent'] ?? 0);
        if ($pct <= 0) {
            return 0;
        }

        return ((float) $amount * $pct) / 100;
    }

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

    private function updateInvoiceStatus(string $tranId, string $status, Request $request): void
    {
        if (!Schema::hasTable('invoices') || !Schema::hasColumn('invoices', 'invoice_number')) {
            return;
        }

        $update = [];

        if (Schema::hasColumn('invoices', 'status')) {
            $update['status'] = $status;
        }

        if ($status === 'success') {
            if (Schema::hasColumn('invoices', 'payment_date')) {
                $update['payment_date'] = date('Y-m-d');
            }
            if (Schema::hasColumn('invoices', 'paid_amount')) {
                $update['paid_amount'] = $request->input('amount') ?? null;
            }
        }

        foreach (['bank_trans_id' => 'bank_tran_id', 'card_type' => 'card_type'] as $col => $in) {
            if (Schema::hasColumn('invoices', $col) && $request->filled($in)) {
                $update[$col] = $request->input($in);
            }
        }

        if (Schema::hasColumn('invoices', 'updated_at')) {
            $update['updated_at'] = now();
        }

        if (!empty($update)) {
            DB::table('invoices')->where('invoice_number', $tranId)->update($update);
        }
    }

    private function gatewayCredentialsForInvoice(object $invoice, object $student): ?array
    {
        if (!Schema::hasTable('payment_gateways')) {
            return null;
        }

        $gateway = null;

        if (Schema::hasColumn('invoices', 'payment_gateway_id') && !empty($invoice->payment_gateway_id)) {
            $gateway = DB::table('payment_gateways')->where('id', (int) $invoice->payment_gateway_id)->first();
        }

        if (!$gateway && Schema::hasTable('fee_setups') && Schema::hasTable('fee_setup_details')) {
            $setup = DB::table('fee_setups')->where([
                'academic_qualification_id' => (int) ($student->academic_qualification_id ?? 0),
                'department_id' => (int) ($student->department_id ?? 0),
                'academic_class_id' => (int) ($student->academic_class_id ?? 0),
            ])->first();

            if ($setup && !empty($invoice->account_head_id) && Schema::hasColumn('fee_setup_details', 'payment_gateway_id')) {
                $detail = DB::table('fee_setup_details')
                    ->where('fee_setup_id', (int) $setup->id)
                    ->where('account_head_id', (int) $invoice->account_head_id)
                    ->first();

                if ($detail && !empty($detail->payment_gateway_id)) {
                    $gateway = DB::table('payment_gateways')->where('id', (int) $detail->payment_gateway_id)->first();
                }
            }
        }

        if (!$gateway) {
            $q = DB::table('payment_gateways')->orderByDesc('id');
            if (Schema::hasColumn('payment_gateways', 'status')) {
                $q->where('status', 'active');
            }
            $gateway = $q->first();
        }

        if (!$gateway) {
            return null;
        }

        $storeId = $gateway->store_id ?? null;
        $storePass = $gateway->store_password ?? null;

        if (!$storeId || !$storePass) {
            return null;
        }

        return [
            'store_id' => (string) $storeId,
            'store_pass' => (string) $storePass,
        ];
    }

    private function feeHeads(object $student): array
    {
        if (!Schema::hasTable('fee_setups') || !Schema::hasTable('fee_setup_details')) {
            return [];
        }

        $feeSetup = DB::table('fee_setups')->where([
            'academic_qualification_id' => (int) ($student->academic_qualification_id ?? 0),
            'department_id' => (int) ($student->department_id ?? 0),
            'academic_class_id' => (int) ($student->academic_class_id ?? 0),
        ])->first();

        if (!$feeSetup) {
            return [];
        }

        $paidHeadIds = [];
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'status')) {
            $paidHeadIds = DB::table('invoices')
                ->where('student_id', (int) ($student->id ?? 0))
                ->where('status', 'success')
                ->pluck('account_head_id')
                ->filter()
                ->values()
                ->all();
        }

        $gender = strtolower((string) ($student->gender ?? ''));
        $isFemale = in_array($gender, ['female', 'f'], true);
        $isMale = in_array($gender, ['male', 'm'], true);

        $detailQ = DB::table('fee_setup_details as d')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'd.account_head_id')
            ->where('d.fee_setup_id', (int) $feeSetup->id);

        if (Schema::hasColumn('fee_setup_details', 'status')) {
            $detailQ->where('d.status', 1);
        }
        if (Schema::hasColumn('fee_setup_details', 'start_date')) {
            $detailQ->where('d.start_date', '<=', date('Y-m-d H:i:s'));
        }
        if (Schema::hasColumn('fee_setup_details', 'additional_date')) {
            $detailQ->where('d.additional_date', '>=', date('Y-m-d H:i:s'));
        }

        $details = $detailQ
            ->select([
                'd.id',
                'd.account_head_id',
                'd.amount',
                Schema::hasColumn('fee_setup_details', 'service_charge') ? 'd.service_charge' : DB::raw('0 as service_charge'),
                Schema::hasColumn('fee_setup_details', 'depend_head_id') ? 'd.depend_head_id' : DB::raw('NULL as depend_head_id'),
                Schema::hasColumn('fee_setup_details', 'payment_gateway_id') ? 'd.payment_gateway_id' : DB::raw('NULL as payment_gateway_id'),
                Schema::hasColumn('fee_setup_details', 'exam_id') ? 'd.exam_id' : DB::raw('NULL as exam_id'),
                Schema::hasColumn('fee_setup_details', 'examination_year') ? 'd.examination_year' : DB::raw('NULL as examination_year'),
                'ah.name as account_head_name',
            ])
            ->orderBy('d.id', 'asc')
            ->get();

        return $details
            ->filter(function ($d) use ($paidHeadIds) {
                return !in_array((int) ($d->account_head_id ?? 0), array_map('intval', $paidHeadIds), true);
            })
            ->filter(function ($d) use ($paidHeadIds, $isFemale, $isMale) {
                $depend = $d->depend_head_id ?? null;
                if ($depend !== null && $depend !== '' && $depend !== 0) {
                    if (!in_array((int) $depend, array_map('intval', $paidHeadIds), true)) {
                        return false;
                    }
                }

                $name = (string) ($d->account_head_name ?? '');
                if ($isFemale && $name !== '' && strpos($name, 'ছেলে') !== false) {
                    return false;
                }
                if ($isMale && $name !== '' && strpos($name, 'ছাত্রী') !== false) {
                    return false;
                }

                return true;
            })
            ->map(function ($d) {
                return [
                    'id' => (int) ($d->id ?? 0),
                    'account_head_id' => (int) ($d->account_head_id ?? 0),
                    'amount' => (float) ($d->amount ?? 0),
                    'service_charge' => (int) ($d->service_charge ?? 0),
                    'depend_head_id' => $d->depend_head_id ?? null,
                    'payment_gateway_id' => $d->payment_gateway_id ?? null,
                    'exam_id' => $d->exam_id ?? null,
                    'examination_year' => $d->examination_year ?? null,
                    'account_head' => [
                        'name' => (string) ($d->account_head_name ?? ''),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    public function systems(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'heads' => $this->feeHeads($student),
            'next_invoice_number' => $this->nextInvoiceNumber(),
        ], 200);
    }

    public function checkInvoice(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'account_head_id' => ['required'],
        ]);

        if (!Schema::hasTable('invoices')) {
            return response()->json(['message' => 'Invoice module not ready'], 422);
        }

        $inv = DB::table('invoices')
            ->where('student_id', (int) ($student->id ?? 0))
            ->where('account_head_id', (int) $request->input('account_head_id'))
            ->where('status', '!=', 'success')
            ->orderByDesc('id')
            ->first();

        if (!$inv) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }

        return response()->json($inv, 200);
    }

    public function init(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'account_head_id' => ['required'],
        ]);

        if (!Schema::hasTable('invoices')) {
            return response()->json(['message' => 'Invoice module not ready'], 422);
        }

        if (!Schema::hasTable('fee_setups') || !Schema::hasTable('fee_setup_details')) {
            return response()->json(['message' => 'Sorry!! You cannot select any payment type'], 422);
        }

        $feeSetup = DB::table('fee_setups')->where([
            'academic_qualification_id' => (int) ($student->academic_qualification_id ?? 0),
            'department_id' => (int) ($student->department_id ?? 0),
            'academic_class_id' => (int) ($student->academic_class_id ?? 0),
        ])->first();

        if (!$feeSetup) {
            return response()->json(['message' => 'Sorry!! You cannot select any payment type'], 422);
        }

        $fees = DB::table('fee_setup_details')
            ->where('fee_setup_id', (int) $feeSetup->id)
            ->where('account_head_id', (int) $request->input('account_head_id'))
            ->first();

        if (!$fees) {
            return response()->json(['message' => 'Sorry!! You cannot select any payment type'], 422);
        }

        $amount = (float) ($fees->amount ?? 0);
        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], 422);
        }

        $fine = (float) (property_exists($fees, 'fine_amount') ? ($fees->fine_amount ?? 0) : 0);
        $hasServiceCharge = !empty($fees->service_charge);
        $charge = $hasServiceCharge ? $this->serviceCharge($amount) : 0;
        $gatewayCharge = 0;
        $totalAmount = $amount + $fine;

        $existing = DB::table('invoices')
            ->where('student_id', (int) ($student->id ?? 0))
            ->where('account_head_id', (int) $request->input('account_head_id'))
            ->where('status', '!=', 'success')
            ->orderByDesc('id')
            ->first();

        $invoice = null;
        $invoiceId = null;
        $invoiceNumber = null;

        if ($existing) {
            $invoice = $existing;
            $invoiceId = $existing->id;
            $invoiceNumber = $existing->invoice_number;
        } else {
            $invoiceNumber = $this->nextInvoiceNumber();

            $payload = [
                'student_id' => (int) ($student->id ?? 0),
                'online_admission_id' => null,
                'student_migration_id' => null,
                'academic_session_id' => (int) ($student->academic_session_id ?? 0) ?: null,
                'department_id' => (int) ($student->department_id ?? 0) ?: null,
                'academic_qualification_id' => (int) ($student->academic_qualification_id ?? 0) ?: null,
                'academic_class_id' => (int) ($student->academic_class_id ?? 0) ?: null,
                'account_head_id' => (int) $request->input('account_head_id'),
                'payment_gateway_id' => property_exists($fees, 'payment_gateway_id') ? ($fees->payment_gateway_id ?? null) : null,
                'exam_id' => property_exists($fees, 'exam_id') ? ($fees->exam_id ?? null) : null,
                'examination_year' => property_exists($fees, 'examination_year') ? ($fees->examination_year ?? null) : null,
                'admission_id' => $student->admission_id ?? null,
                'college_roll' => $student->college_roll ?? null,
                'reg_no' => $student->reg_no ?? null,
                'invoice_date' => date('Y-m-d'),
                'invoice_number' => $invoiceNumber,
                'fine_amount' => $fine ?: null,
                'fees_amount' => $amount,
                'service_charge' => $charge ?: null,
                'gateway_charge' => $gatewayCharge ?: null,
                'amount' => $totalAmount,
                'paid_amount' => null,
                'payment_date' => null,
                'payment_method' => 'SSL',
                'status' => 'pending',
                'created_from' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $invoiceId = DB::table('invoices')->insertGetId($payload);
            $invoice = DB::table('invoices')->where('id', (int) $invoiceId)->first();
        }

        if (!$invoice || !$invoiceNumber) {
            return response()->json(['message' => 'Failed to create invoice'], 422);
        }

        $creds = $this->gatewayCredentialsForInvoice($invoice, $student);
        if (!$creds) {
            return response()->json(['message' => 'Payment gateway not configured'], 422);
        }

        $tranId = (string) $invoiceNumber;

        $headName = '';
        if (Schema::hasTable('account_heads')) {
            $headName = (string) (DB::table('account_heads')->where('id', (int) $request->input('account_head_id'))->value('name') ?? '');
        }

        $successUrl = url('/api/public/student/pay-now/success');
        $failUrl = url('/api/public/student/pay-now/fail');
        $cancelUrl = url('/api/public/student/pay-now/cancel');
        $ipnUrl = url('/api/public/student/pay-now/ipn');

        $productName = $headName !== '' ? $headName : 'Student Fee';

        $init = $this->initiateSslCommerz(
            $creds['store_id'],
            $creds['store_pass'],
            (float) $totalAmount,
            (string) $tranId,
            $productName,
            (string) ($student->name ?? 'Student'),
            (string) ($student->mobile ?? ''),
            $successUrl,
            $failUrl,
            $cancelUrl,
            $ipnUrl
        );

        if (!($init['ok'] ?? false)) {
            return response()->json(['message' => (string) ($init['message'] ?? 'Failed to initiate payment gateway')], 422);
        }

        return response()->json([
            'gateway_url' => $init['gateway_url'] ?? null,
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber,
        ], 200);
    }

    public function payExisting(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'invoice_number' => ['required'],
        ]);

        if (!Schema::hasTable('invoices')) {
            return response()->json(['message' => 'Invoice module not ready'], 422);
        }

        $invoice = DB::table('invoices')
            ->where('student_id', (int) ($student->id ?? 0))
            ->where('invoice_number', (string) $request->input('invoice_number'))
            ->where('status', '!=', 'success')
            ->orderByDesc('id')
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $creds = $this->gatewayCredentialsForInvoice($invoice, $student);
        if (!$creds) {
            return response()->json(['message' => 'Payment gateway not configured'], 422);
        }

        $headName = '';
        if (Schema::hasTable('account_heads') && !empty($invoice->account_head_id)) {
            $headName = (string) (DB::table('account_heads')->where('id', (int) $invoice->account_head_id)->value('name') ?? '');
        }

        $successUrl = url('/api/public/student/pay-now/success');
        $failUrl = url('/api/public/student/pay-now/fail');
        $cancelUrl = url('/api/public/student/pay-now/cancel');
        $ipnUrl = url('/api/public/student/pay-now/ipn');

        $init = $this->initiateSslCommerz(
            $creds['store_id'],
            $creds['store_pass'],
            (float) ($invoice->amount ?? 0),
            (string) ($invoice->invoice_number ?? ''),
            $headName !== '' ? $headName : 'Student Fee',
            (string) ($student->name ?? 'Student'),
            (string) ($student->mobile ?? ''),
            $successUrl,
            $failUrl,
            $cancelUrl,
            $ipnUrl
        );

        if (!($init['ok'] ?? false)) {
            return response()->json(['message' => (string) ($init['message'] ?? 'Failed to initiate payment gateway')], 422);
        }

        return response()->json([
            'gateway_url' => $init['gateway_url'] ?? null,
        ], 200);
    }

    public function success(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateInvoiceStatus($tranId, 'success', $request);
        }

        return redirect('/student/pay-now?status=success&tran_id=' . urlencode($tranId));
    }

    public function fail(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateInvoiceStatus($tranId, 'failed', $request);
        }

        return redirect('/student/pay-now?status=failed&tran_id=' . urlencode($tranId));
    }

    public function cancel(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateInvoiceStatus($tranId, 'cancelled', $request);
        }

        return redirect('/student/pay-now?status=cancelled&tran_id=' . urlencode($tranId));
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
            $this->updateInvoiceStatus($tranId, $map[$status] ?? $status, $request);
        }

        return response()->json(['ok' => true], 200);
    }
}
