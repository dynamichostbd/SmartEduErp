<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class BankSettlementController extends Controller
{
    public function settlement(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'string'],
            'end_date' => ['required', 'string'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1'],
            'account' => ['nullable', 'string'],
        ]);

        if (!Schema::hasTable('payment_gateways') || !Schema::hasColumn('payment_gateways', 'account_no')) {
            return response()->json(['error' => 'Payment gateways not available'], 422);
        }

        if (empty($request->input('account'))) {
            return response()->json(['error' => 'Account is required'], 422);
        }

        $gateway = DB::table('payment_gateways')->where('account_no', $request->input('account'))->first();
        if (!$gateway) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        if (!Schema::hasColumn('payment_gateways', 'store_id') || !Schema::hasColumn('payment_gateways', 'store_password')) {
            return response()->json(['error' => 'store_id/store_password missing in payment_gateways'], 422);
        }

        $storeId = $gateway->store_id ?? null;
        $storePass = $gateway->store_password ?? null;
        if (!$storeId || !$storePass) {
            return response()->json(['error' => 'SSL store credentials not configured for this account'], 422);
        }

        $response = Http::asForm()->timeout(60)->post('https://securepay.sslcommerz.com/validator/api/v4/', [
            'store_id' => $storeId,
            'store_passwd' => $storePass,
            'action' => 'settlementRecord',
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'page' => $validated['page'],
            'limit' => $validated['limit'],
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'SSLCommerz API error',
                'status' => $response->status(),
                'message' => $response->body(),
            ], $response->status());
        }

        $data = $response->json();

        $results = [];
        foreach (($data['sett'] ?? []) as $sett) {
            $transactionId = $sett['transactionID'] ?? null;
            $invoice = null;

            if ($transactionId && Schema::hasTable('invoices')) {
                $invoice = $this->loadInvoiceByNumber($transactionId);
            }

            if (!$invoice && $transactionId && Schema::hasTable('admissions')) {
                $invoice = $this->loadAdmissionByNumber($transactionId);
            }

            $results[] = [
                'settlement' => $sett,
                'invoice' => $invoice,
            ];
        }

        return response()->json([
            'status' => 'success',
            'details' => $results,
            'data' => $data,
            'pagination' => [
                'total_pages' => $data['total_pages'] ?? 1,
                'page' => $validated['page'],
                'limit' => $validated['limit'],
            ],
        ]);
    }

    private function loadInvoiceByNumber(string $invoiceNumber)
    {
        if (!Schema::hasTable('invoices')) {
            return null;
        }

        $q = DB::table('invoices as inv')
            ->leftJoin('students as std', 'std.id', '=', 'inv.student_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'inv.department_id')
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'inv.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'inv.academic_class_id')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'inv.account_head_id')
            ->where('inv.invoice_number', $invoiceNumber);

        $row = $q->select([
            'inv.*',
            'std.id as student_id',
            'std.student_id as student_student_id',
            'std.name as student_name',
            'std.mobile as student_mobile',
            'std.admission_id as student_admission_id',
            'std.reg_no as student_reg_no',
            'dept.id as department_id',
            'dept.name as department_name',
            'aq.id as qualification_id',
            'aq.name as qualification_name',
            'cls.id as academic_class_id',
            'cls.name as academic_class_name',
            'ah.id as head_id',
            DB::raw((Schema::hasColumn('account_heads', 'name_en') ? 'ah.name_en' : 'ah.name') . ' as head_name_en'),
        ])->first();

        if (!$row) return null;

        return [
            'id' => $row->id,
            'invoice_number' => $row->invoice_number,
            'amount' => $row->amount,
            'student' => $row->student_id ? [
                'id' => $row->student_id,
                'student_id' => $row->student_student_id,
                'name' => $row->student_name,
                'mobile' => $row->student_mobile,
                'admission_id' => $row->student_admission_id,
                'reg_no' => $row->student_reg_no,
            ] : null,
            'department' => $row->department_id ? ['id' => $row->department_id, 'name' => $row->department_name] : null,
            'qualification' => $row->qualification_id ? ['id' => $row->qualification_id, 'name' => $row->qualification_name] : null,
            'academic_class' => $row->academic_class_id ? ['id' => $row->academic_class_id, 'name' => $row->academic_class_name] : null,
            'head' => $row->head_id ? ['id' => $row->head_id, 'name_en' => $row->head_name_en] : null,
        ];
    }

    private function loadAdmissionByNumber(string $invoiceNumber)
    {
        if (!Schema::hasTable('admissions')) {
            return null;
        }

        $q = DB::table('admissions as adm')
            ->leftJoin('departments as dept', 'dept.id', '=', 'adm.department_id')
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'adm.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'adm.academic_class_id')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'adm.account_head_id')
            ->where('adm.invoice_number', $invoiceNumber);

        $row = $q->select([
            'adm.*',
            'dept.id as department_id',
            'dept.name as department_name',
            'aq.id as qualification_id',
            'aq.name as qualification_name',
            'cls.id as academic_class_id',
            'cls.name as academic_class_name',
            'ah.id as head_id',
            DB::raw((Schema::hasColumn('account_heads', 'name_en') ? 'ah.name_en' : 'ah.name') . ' as head_name_en'),
        ])->first();

        if (!$row) return null;

        return [
            'id' => $row->id,
            'invoice_number' => $row->invoice_number,
            'amount' => $row->amount,
            'student' => [
                'id' => null,
                'student_id' => null,
                'name' => $row->name ?? null,
                'mobile' => $row->mobile ?? null,
                'admission_id' => $row->admission_roll ?? null,
                'reg_no' => $row->registration_no ?? null,
            ],
            'department' => $row->department_id ? ['id' => $row->department_id, 'name' => $row->department_name] : null,
            'qualification' => $row->qualification_id ? ['id' => $row->qualification_id, 'name' => $row->qualification_name] : null,
            'academic_class' => $row->academic_class_id ? ['id' => $row->academic_class_id, 'name' => $row->academic_class_name] : null,
            'head' => $row->head_id ? ['id' => $row->head_id, 'name_en' => $row->head_name_en] : null,
        ];
    }
}
