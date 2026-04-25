<?php

namespace App\Http\Controllers;

use App\Models\NumberToWord;
use App\Traits\Lib\CustomDataTrait;
use App\Traits\Lib\DynamicDataTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PublicOnlineAdmissionController extends Controller
{
    use DynamicDataTrait, CustomDataTrait;

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

    private function normalizeStoragePath(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = ltrim($value, '/');
        $value = preg_replace('#^storage/#i', '', $value);
        $value = preg_replace('#^upload/#i', '', $value);

        return ltrim((string) $value, '/');
    }

    private function storageUploadPath(string $normalized): string
    {
        $normalized = ltrim($normalized, '/');
        return 'upload/' . $normalized;
    }

    private function assetUrl($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', (string) $value)) {
            return (string) $value;
        }

        $value = ltrim((string) $value, '/');

        $filePath1 = public_path($value);
        if (file_exists($filePath1)) {
            return url($value);
        }

        $filePath2 = public_path('storage/upload/' . $value);
        if (file_exists($filePath2)) {
            return url('storage/upload/' . $value);
        }

        $bucketUrl = env('DO_ASSET_URL', 'https://smartedubd.blr1.cdn.digitaloceanspaces.com/blr1_storage');
        $bucketUrl = rtrim((string) $bucketUrl, '/');
        $value = ltrim((string) $value, '/');

        return "$bucketUrl/$value";
    }

    private function decodeJson($val): array
    {
        if ($val === null || $val === '') {
            return [];
        }

        if (is_array($val)) {
            return $val;
        }

        $decoded = json_decode((string) $val, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private function decorateSubjectChoose($items): array
    {
        $items = is_array($items) ? $items : [];

        $ids = collect($items)
            ->pluck('subject_id')
            ->filter()
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values();

        $subjects = collect();
        if ($ids->count() > 0 && Schema::hasTable('subjects')) {
            $subjects = DB::table('subjects')->select('id', 'name_en')->whereIn('id', $ids)->get()->keyBy('id');
        }

        return collect($items)
            ->map(function ($item) use ($subjects) {
                $sid = (int) ($item['subject_id'] ?? 0);
                if ($sid <= 0) {
                    return null;
                }

                return [
                    'subject_id' => $sid,
                    'main_subject' => !empty($item['main_subject']) ? 1 : 0,
                    'subject' => [
                        'name_en' => $subjects[$sid]->name_en ?? '',
                    ],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function serviceCharge(float $amount, string $field): float
    {
        $config = $this->resolveSiteConfig();
        $pct = (float) ($config[$field] ?? 0);
        if ($pct <= 0) {
            return 0;
        }

        return ((float) $amount * $pct) / 100;
    }

    private function findOnlineAdmission(Request $request)
    {
        if (!Schema::hasTable('online_admissions')) {
            return null;
        }

        $mobile = (string) ($request->input('mobile') ?? '');
        $roll = (string) ($request->input('admission_roll') ?? $request->input('registration_no') ?? '');

        if ($mobile === '' || $roll === '') {
            return null;
        }

        $q = DB::table('online_admissions')->orderByDesc('id')->where('mobile', $mobile);

        if (Schema::hasColumn('online_admissions', 'admission_roll')) {
            $q->where('admission_roll', $roll);
        }

        return $q->first();
    }

    public function systems(Request $request)
    {
        $dynamic = $this->dynamicData();
        $custom = $this->customData();

        return response()->json([
            'global' => [
                'academic_classes' => $dynamic['academic_classes'] ?? [],
                'academic_sessions' => $dynamic['academic_sessions'] ?? [],
                'academic_qualifications' => $dynamic['academic_qualifications'] ?? [],
                'departments' => $dynamic['departments'] ?? [],
                'department_qualidactions' => $dynamic['department_qualidactions'] ?? [],
                'religions' => $custom['religions'] ?? [],
                'blood_groups' => $custom['blood_groups'] ?? [],
                'files' => $custom['files'] ?? [],
                'site' => $this->resolveSiteConfig(),
                // Geographic data for cascading address dropdowns
                'divisions' => $dynamic['divisions'] ?? [],
                'districts' => $dynamic['districts'] ?? [],
                'upazilas' => $dynamic['upazilas'] ?? [],
                'unions' => $dynamic['unions'] ?? [],
            ],
        ]);
    }

    public function checkApplicationFees(Request $request)
    {
        $request->validate([
            'application_invoice_no' => ['required'],
            'admission_roll' => ['required'],
        ]);

        if (!Schema::hasTable('admissions')) {
            return response()->json(['message' => 'Sorry!! Applicatioin Fees Not Found'], 201);
        }

        $q = DB::table('admissions')->where([
            'invoice_number' => $request->input('application_invoice_no'),
            'admission_roll' => $request->input('admission_roll'),
        ]);

        if (Schema::hasColumn('admissions', 'status')) {
            $q->where('status', 'success');
        }

        $admission = $q->first();

        if (!$admission) {
            return response()->json(['message' => 'Sorry!! Applicatioin Fees Not Found'], 201);
        }

        $exists = DB::table('online_admissions')->where([
            'admission_roll' => $request->input('admission_roll'),
            'academic_session_id' => $admission->academic_session_id ?? null,
            'department_id' => $admission->department_id ?? null,
        ])->exists();

        if ($exists) {
            return response()->json(['message' => 'Already Registered This Admission Roll'], 201);
        }

        return response()->json(['message' => 'Success', 'admission' => $admission], 200);
    }

    public function checkAdmissionRoll(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'admission_roll' => ['required'],
        ]);

        if (!Schema::hasTable('online_admission_roll_verifies')) {
            return response()->json(['message' => 'Sorry!! Roll Not Found'], 201);
        }

        $rollVerify = DB::table('online_admission_roll_verifies')->where([
            'academic_session_id' => $request->input('academic_session_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'department_id' => $request->input('department_id'),
            'academic_class_id' => $request->input('academic_class_id'),
        ])->orderByDesc('id')->first();

        if (!$rollVerify) {
            return response()->json(['message' => 'Sorry!! Roll Not Found'], 201);
        }

        $exists = DB::table('online_admissions')->where([
            'admission_roll' => $request->input('admission_roll'),
            'academic_session_id' => $request->input('academic_session_id'),
            'department_id' => $request->input('department_id'),
        ])->exists();

        if ($exists) {
            return response()->json(['message' => 'Already Registered This Admission Roll'], 201);
        }

        $rolls = $this->decodeJson($rollVerify->roll_lists ?? '[]');
        $names = $this->decodeJson($rollVerify->name_lists ?? '[]');

        foreach (array_values($rolls) as $idx => $roll) {
            if ((string) $roll === (string) $request->input('admission_roll')) {
                return response()->json([
                    'message' => 'Success',
                    'data' => [
                        'roll_number' => (string) $roll,
                        'name' => (string) ($names[$idx] ?? ''),
                    ],
                ], 200);
            }
        }

        return response()->json(['message' => 'Sorry!! Roll Not Found'], 201);
    }

    public function documentUpload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:jpg,jpeg,png', 'file'],
        ]);

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['message' => 'Invalid file'], 422);
        }

        $path = $file->store('upload/online_admission_dummy_files', 'public');
        $normalized = $this->normalizeStoragePath($path);

        return response()->json($normalized, 200);
    }

    public function submit(Request $request)
    {
        $data = $request->input('data');
        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                $request->merge($decoded);
            }
        }

        $request->validate([
            'profile' => ['required', 'mimes:jpg,png,jpeg'],
            'academic_class_id' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'name' => ['required'],
            'fathers_name' => ['required'],
            'mothers_name' => ['required'],
            'mobile' => ['required'],
            'guardian_type' => ['required'],
            'religion' => ['required'],
            'gender' => ['required'],
            'address' => ['required'],
            'admission_roll' => ['required'],
            'documents' => ['required', 'array'],
            'documents.*.file' => ['required'],
        ]);

        if (!Schema::hasTable('online_admissions')) {
            return response()->json(['message' => 'Online Admission module not ready'], 422);
        }

        $existsOA = DB::table('online_admissions')->where([
            'admission_roll' => $request->input('admission_roll'),
            'academic_session_id' => $request->input('academic_session_id'),
            'department_id' => $request->input('department_id'),
        ])->exists();

        if ($existsOA) {
            return response()->json(['message' => 'Already Registered This Admission Roll'], 201);
        }

        if (Schema::hasTable('students') && Schema::hasColumn('students', 'admission_id')) {
            $existsStd = DB::table('students')->where([
                'admission_id' => $request->input('admission_roll'),
                'academic_session_id' => $request->input('academic_session_id'),
                'department_id' => $request->input('department_id'),
            ])->exists();

            if ($existsStd) {
                return response()->json(['message' => 'Already Registered This Admission Roll'], 201);
            }
        }

        $existsMobile = DB::table('online_admissions')->where([
            'mobile' => $request->input('mobile'),
            'academic_session_id' => $request->input('academic_session_id'),
        ])->exists();

        if ($existsMobile) {
            return response()->json(['message' => 'Already Registered this Mobile Number'], 201);
        }

        if ((bool) $request->input('check_application_fees')) {
            if (!Schema::hasTable('admissions')) {
                return response()->json(['message' => 'Sorry!! Applicatioin Fees Not Found'], 201);
            }

            $admissionExists = DB::table('admissions')->where([
                'invoice_number' => $request->input('application_invoice_no'),
                'admission_roll' => $request->input('admission_roll'),
            ])->exists();

            if (!$admissionExists) {
                return response()->json(['message' => 'Sorry!! Applicatioin Fees Not Found'], 201);
            }
        }

        $documents = (array) $request->input('documents');
        foreach ($documents as $k => $doc) {
            $fileVal = is_array($doc) ? ($doc['file'] ?? null) : null;
            $normalized = $this->normalizeStoragePath(is_string($fileVal) ? $fileVal : null);
            if (!$normalized) {
                continue;
            }

            $source = $this->storageUploadPath($normalized);
            $desPath = 'admission_files/' . date('Y/F');
            $destination = 'upload/' . $desPath . '/' . basename($normalized);

            if (Storage::disk('public')->exists($source)) {
                Storage::disk('public')->makeDirectory('upload/' . $desPath);
                Storage::disk('public')->move($source, $destination);
                $documents[$k]['file'] = $this->normalizeStoragePath($destination);
            } else {
                $documents[$k]['file'] = $normalized;
            }
        }

        $profilePath = null;
        $profile = $request->file('profile');
        if ($profile && $profile->isValid()) {
            $profilePath = $profile->store('upload/onlineAdmission/student-profile', 'public');
            $profilePath = $this->normalizeStoragePath($profilePath);
        }

        $dob = $request->input('dob');
        if (!$dob) {
            $dob = $request->input('date_of_brith');
        }
        $dob = !empty($dob) ? date('Y-m-d', strtotime((string) $dob)) : null;

        $payload = $request->only([
            'academic_session_id',
            'department_id',
            'academic_qualification_id',
            'academic_class_id',
            'admission_roll',
            'ssc_gpa',
            'registration_no',
            'student_type',
            'name',
            'gender',
            'religion',
            'nid',
            'blood_group',
            'fathers_name',
            'mothers_name',
            'mobile',
            'email',
            'address',
            'permanent_address',
            // Geographic address IDs
            'division_id',
            'district_id',
            'upazila_id',
            'union_id',
            'permanent_division_id',
            'permanent_district_id',
            'permanent_upazila_id',
            'permanent_union_id',
            'guardian_type',
            'guardian_name',
            'guardian_mobile',
            'guardian_relations',
            'passing_year',
            'nationality',
            'extra_curricular_activity',
            'quota',
            'marital_status',
        ]);

        $payload['dob'] = $dob;
        $payload['profile'] = $profilePath;
        $payload['documents'] = json_encode(array_values($documents));
        $payload['name'] = strtoupper((string) ($payload['name'] ?? ''));
        $payload['fathers_name'] = strtoupper((string) ($payload['fathers_name'] ?? ''));
        $payload['mothers_name'] = strtoupper((string) ($payload['mothers_name'] ?? ''));
        $payload['address'] = strtoupper((string) ($payload['address'] ?? ''));
        $payload['status'] = 'pending';
        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        $id = DB::table('online_admissions')->insertGetId($payload);
        $std = DB::table('online_admissions')->where('id', $id)->first();

        return response()->json(['message' => 'Admission Form Submit Sccessfully, Please Payment!', 'data' => $std], 200);
    }

    public function getPaymentHeads(Request $request)
    {
        $request->validate([
            'mobile' => ['required'],
            'admission_roll' => ['required'],
        ]);

        $onlineAdmission = $this->findOnlineAdmission($request);
        if (!$onlineAdmission) {
            return response()->json(['error' => 'Sorry!! application not found'], 201);
        }

        if (!Schema::hasTable('invoices')) {
            return response()->json(['error' => 'Fees are not available'], 201);
        }

        $paidInvoices = DB::table('invoices')
            ->where('online_admission_id', $onlineAdmission->id)
            ->where('status', 'success')
            ->pluck('account_head_id')
            ->filter()
            ->values()
            ->all();

        if (!Schema::hasTable('fee_setups') || !Schema::hasTable('fee_setup_details')) {
            return response()->json(['error' => 'Fees are not available'], 201);
        }

        $feeSetup = DB::table('fee_setups')->where([
            'academic_qualification_id' => $onlineAdmission->academic_qualification_id,
            'department_id' => $onlineAdmission->department_id,
            'academic_class_id' => $onlineAdmission->academic_class_id,
        ])->first();

        if (!$feeSetup) {
            return response()->json(['error' => 'Your are too late, payment date is expired!'], 201);
        }

        $detailQ = DB::table('fee_setup_details as d')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'd.account_head_id')
            ->where('d.fee_setup_id', $feeSetup->id);

        if (Schema::hasColumn('fee_setup_details', 'online_addmission_fees')) {
            $detailQ->where('d.online_addmission_fees', 1);
        }
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
                'ah.name as account_head_name',
            ])
            ->get();

        if ($details->count() === 0) {
            return response()->json(['error' => 'Your are too late, payment date is expired!'], 201);
        }

        $fees = $details->filter(function ($d) use ($paidInvoices) {
            return !in_array((int) ($d->account_head_id ?? 0), array_map('intval', $paidInvoices), true);
        })->values();

        return response()->json($fees->map(function ($d) {
            return [
                'id' => (int) ($d->id ?? 0),
                'account_head_id' => (int) ($d->account_head_id ?? 0),
                'amount' => (float) ($d->amount ?? 0),
                'service_charge' => (int) ($d->service_charge ?? 0),
                'depend_head_id' => $d->depend_head_id ?? null,
                'account_head' => [
                    'name' => (string) ($d->account_head_name ?? ''),
                ],
            ];
        })->values()->all(), 200);
    }

    public function checkDependHead(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            'mobile' => ['required'],
        ]);

        if (!Schema::hasTable('fee_setup_details')) {
            return response()->json(['status' => false, 'message' => ''], 200);
        }

        $onlineAd = DB::table('online_admissions')->where('mobile', $request->input('mobile'))->orderByDesc('id')->first();
        $fees = DB::table('fee_setup_details')->where('id', (int) $request->input('id'))->first();

        $dependHeadName = '';
        $status = false;

        if ($fees && $onlineAd) {
            $dependHeadId = null;
            if (property_exists($fees, 'depend_head_id')) {
                $raw = $fees->depend_head_id;
                if (is_numeric($raw)) {
                    $dependHeadId = (int) $raw;
                } elseif (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded) && isset($decoded[0]) && is_numeric($decoded[0])) {
                        $dependHeadId = (int) $decoded[0];
                    }
                }
            }

            if ($dependHeadId) {
                if (Schema::hasTable('account_heads')) {
                    $dependHeadName = (string) (DB::table('account_heads')->where('id', $dependHeadId)->value('name') ?? '');
                }

                $exists = DB::table('invoices')
                    ->where('online_admission_id', $onlineAd->id)
                    ->where('account_head_id', $dependHeadId)
                    ->where('status', 'success')
                    ->first();

                $status = empty($exists);
            }
        }

        return response()->json([
            'status' => $status,
            'message' => $dependHeadName !== '' ? "You have to pay {$dependHeadName} first" : 'You have to pay first',
        ], 200);
    }

    public function payments(Request $request)
    {
        $request->validate([
            'mobile' => ['required'],
            'admission_roll' => ['required'],
            'account_head_id' => ['required'],
        ]);

        $onlineAdmission = $this->findOnlineAdmission($request);
        if (!$onlineAdmission) {
            return response()->json(['error' => 'Sorry!! application not found'], 201);
        }

        if (!Schema::hasTable('fee_setups') || !Schema::hasTable('fee_setup_details')) {
            return response()->json(['error' => 'Sorry!! You cannot select any payment type'], 201);
        }

        $feeSetup = DB::table('fee_setups')->where([
            'academic_qualification_id' => $onlineAdmission->academic_qualification_id,
            'department_id' => $onlineAdmission->department_id,
            'academic_class_id' => $onlineAdmission->academic_class_id,
        ])->first();

        if (!$feeSetup) {
            return response()->json(['error' => 'Sorry!! You cannot select any payment type'], 201);
        }

        $fees = DB::table('fee_setup_details')->where('fee_setup_id', $feeSetup->id)->where('account_head_id', (int) $request->input('account_head_id'))->first();
        if (!$fees) {
            return response()->json(['error' => 'Sorry!! You cannot select any payment type'], 201);
        }

        $amount = (float) ($fees->amount ?? 0);
        if ($amount <= 0 && $request->filled('amount')) {
            $amount = (float) $request->input('amount');
        }
        if ($amount <= 0) {
            return response()->json(['error' => 'Invalid amount'], 201);
        }

        $fine = (float) (property_exists($fees, 'fine_amount') ? ($fees->fine_amount ?? 0) : 0);
        $hasServiceCharge = !empty($fees->service_charge);

        $charge = $hasServiceCharge ? $this->serviceCharge($amount, 'admission_fees_charge_percent') : 0;
        $gatewayCharge = $hasServiceCharge ? $this->serviceCharge($amount, 'admission_fees_gateway_percent') : 0;

        $totalAmount = $amount + $fine;

        $existing = DB::table('invoices')
            ->where('online_admission_id', $onlineAdmission->id)
            ->where('account_head_id', (int) $request->input('account_head_id'))
            ->where('status', '!=', 'success')
            ->orderByDesc('id')
            ->first();

        $invoiceId = null;
        $invoiceNumber = null;

        if ($existing) {
            $invoiceId = $existing->id;
            $invoiceNumber = $existing->invoice_number;
        } else {
            $invoiceNumber = $this->nextInvoiceNumber();

            $payload = [
                'student_id' => null,
                'online_admission_id' => $onlineAdmission->id,
                'student_migration_id' => null,
                'academic_session_id' => $onlineAdmission->academic_session_id,
                'department_id' => $onlineAdmission->department_id,
                'academic_qualification_id' => $onlineAdmission->academic_qualification_id,
                'academic_class_id' => $onlineAdmission->academic_class_id,
                'account_head_id' => (int) $request->input('account_head_id'),
                'payment_gateway_id' => property_exists($fees, 'payment_gateway_id') ? ($fees->payment_gateway_id ?? null) : null,
                'exam_id' => property_exists($fees, 'exam_id') ? ($fees->exam_id ?? null) : null,
                'examination_year' => property_exists($fees, 'examination_year') ? ($fees->examination_year ?? null) : null,
                'admission_id' => $onlineAdmission->admission_roll ?? null,
                'college_roll' => null,
                'reg_no' => $onlineAdmission->registration_no ?? null,
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
        }

        $gateway = null;
        if (!Schema::hasTable('payment_gateways')) {
            return response()->json(['error' => 'Payment gateway not configured'], 201);
        }

        if (Schema::hasColumn('fee_setup_details', 'payment_gateway_id') && !empty($fees->payment_gateway_id)) {
            $gateway = DB::table('payment_gateways')->where('id', (int) $fees->payment_gateway_id)->first();
        }

        if (!$gateway && Schema::hasColumn('payment_gateways', 'status')) {
            $gateway = DB::table('payment_gateways')->where('status', 'active')->orderByDesc('id')->first();
        }

        $storeId = $gateway->store_id ?? null;
        $storePass = $gateway->store_password ?? null;

        if (!$storeId || !$storePass) {
            return response()->json(['error' => 'Payment gateway not configured'], 201);
        }

        $headName = '';
        if (Schema::hasTable('account_heads')) {
            $headName = (string) (DB::table('account_heads')->where('id', (int) $request->input('account_head_id'))->value('name') ?? '');
        }

        $successUrl = url('/api/public/online-admission/success');
        $failUrl = url('/api/public/online-admission/fail');
        $cancelUrl = url('/api/public/online-admission/cancel');
        $ipnUrl = url('/api/public/online-admission/ipn');

        $init = $this->initiateSslCommerz(
            (string) $storeId,
            (string) $storePass,
            (float) $totalAmount,
            (string) $invoiceNumber,
            $headName !== '' ? $headName : 'Online Admission',
            (string) ($onlineAdmission->name ?? ''),
            (string) ($onlineAdmission->mobile ?? ''),
            $successUrl,
            $failUrl,
            $cancelUrl,
            $ipnUrl
        );

        if (!($init['ok'] ?? false)) {
            return response()->json(['error' => (string) ($init['message'] ?? 'Sorry!! Payment cannot proceed at this time, please try again')], 201);
        }

        return response()->json([
            'pay' => [
                'url' => $init['gateway_url'] ?? null,
            ],
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber,
            'message' => 'Invoice create successfully',
        ], 200);
    }

    public function invoice(Request $request)
    {
        $request->validate([
            'mobile' => ['required'],
            'admission_roll' => ['required'],
        ]);

        $onlineAdmission = $this->findOnlineAdmission($request);
        if (!$onlineAdmission) {
            return response()->json(['error' => 'Sorry!! application not found'], 201);
        }

        if (!Schema::hasTable('invoices')) {
            return response()->json([]);
        }

        $q = DB::table('invoices as inv')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'inv.account_head_id')
            ->leftJoin('academic_sessions as as', 'as.id', '=', 'inv.academic_session_id')
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'inv.academic_qualification_id')
            ->leftJoin('departments as d', 'd.id', '=', 'inv.department_id')
            ->leftJoin('academic_classes as ac', 'ac.id', '=', 'inv.academic_class_id')
            ->where('inv.online_admission_id', $onlineAdmission->id)
            ->orderByDesc('inv.id')
            ->select([
                'inv.id',
                'inv.invoice_number',
                'inv.invoice_date',
                'inv.amount',
                'inv.payment_date',
                'inv.status',
                'inv.account_head_id',
                'ah.name as head_name',
                'as.name as session_name',
                'aq.name as qualification_name',
                'd.name as department_name',
                'ac.name as class_name',
            ]);

        return response()->json($q->get()->map(function ($r) use ($onlineAdmission) {
            return [
                'id' => (int) ($r->id ?? 0),
                'invoice_number' => $r->invoice_number ?? '',
                'invoice_date' => $r->invoice_date ?? null,
                'amount' => (float) ($r->amount ?? 0),
                'payment_date' => $r->payment_date ?? null,
                'status' => $r->status ?? 'pending',
                'head_name' => $r->head_name ?? '',
                'session_name' => $r->session_name ?? '',
                'qualification_name' => $r->qualification_name ?? '',
                'department_name' => $r->department_name ?? '',
                'class_name' => $r->class_name ?? '',
                // Flatten student info
                'name' => $onlineAdmission->name ?? '',
                'mobile' => $onlineAdmission->mobile ?? '',
                'admission_roll' => $onlineAdmission->admission_roll ?? '',
                // Keep the original object just in case
                'online_admission' => $onlineAdmission,
            ];
        })->values()->all());
    }

    public function downloadInvoice(Request $request, int $id)
    {
        if (!Schema::hasTable('invoices')) {
            abort(404);
        }

        $invoice = DB::table('invoices')->where('id', $id)->first();
        if (!$invoice) {
            abort(404);
        }

        $oa = null;
        if (!empty($invoice->online_admission_id) && Schema::hasTable('online_admissions')) {
            $oa = DB::table('online_admissions')->where('id', (int) $invoice->online_admission_id)->first();
        }

        $head = null;
        if (!empty($invoice->account_head_id) && Schema::hasTable('account_heads')) {
            $head = DB::table('account_heads')->where('id', (int) $invoice->account_head_id)->first();
        }

        $department = null;
        if (!empty($invoice->department_id) && Schema::hasTable('departments')) {
            $department = DB::table('departments')->where('id', (int) $invoice->department_id)->first();
        }

        $qualification = null;
        if (!empty($invoice->academic_qualification_id) && Schema::hasTable('academic_qualifications')) {
            $qualification = DB::table('academic_qualifications')->where('id', (int) $invoice->academic_qualification_id)->first();
        }

        $academicClass = null;
        if (!empty($invoice->academic_class_id) && Schema::hasTable('academic_classes')) {
            $academicClass = DB::table('academic_classes')->where('id', (int) $invoice->academic_class_id)->first();
        }

        $academicSession = null;
        if (!empty($invoice->academic_session_id) && Schema::hasTable('academic_sessions')) {
            $academicSession = DB::table('academic_sessions')->where('id', (int) $invoice->academic_session_id)->first();
        }

        $config = $this->resolveSiteConfig();

        $dto = (object) array_merge((array) $invoice, [
            'online_admission' => $oa,
            'head' => $head,
            'department' => $department,
            'qualification' => $qualification,
            'academic_class' => $academicClass,
            'academic_session' => $academicSession,
        ]);

        $pdf = Pdf::loadView('pdf.online_admission_invoice', [
            'invoice' => $dto,
            'config' => $config,
        ])->setPaper('a4', 'portrait');

        $fileName = 'invoice(' . ($invoice->invoice_date ?? date('Y-m-d')) . '__' . ($invoice->invoice_number ?? $id) . ').pdf';

        return $pdf->download($fileName);
    }

    private function base64Image($url): string
    {
        if (empty($url)) return '';
        try {
            $ctx = stream_context_create([
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
            ]);
            $data = @file_get_contents($url, false, $ctx);
            if ($data === false) return (string) $url;
            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            return 'data:image/' . ($ext ?: 'png') . ';base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            return (string) $url;
        }
    }

    public function downloadForm(Request $request)
    {
        $request->validate([
            'admission_roll' => ['required'],
            'mobile' => ['required'],
        ]);

        if (!Schema::hasTable('online_admissions')) {
            abort(404);
        }

        $data = DB::table('online_admissions')->where([
            'admission_roll' => $request->input('admission_roll'),
            'mobile' => $request->input('mobile'),
        ])->orderByDesc('id')->first();

        if (!$data) {
            abort(404);
        }

        $row = (object) (array) $data;
        $row->profile = $this->assetUrl($row->profile);
        $row->subject_choose = $this->decorateSubjectChoose($this->decodeJson($row->subject_choose));

        $row->academic_session = Schema::hasTable('academic_sessions')
            ? DB::table('academic_sessions')->select('id', 'name')->where('id', $row->academic_session_id)->first()
            : null;

        $row->department = $row->department_id && Schema::hasTable('departments')
            ? DB::table('departments')->select('id', 'name')->where('id', $row->department_id)->first()
            : null;

        $row->qualification = Schema::hasTable('academic_qualifications')
            ? DB::table('academic_qualifications')->select('id', 'name', 'commitment')->where('id', $row->academic_qualification_id)->first()
            : null;

        $row->academic_class = Schema::hasTable('academic_classes')
            ? DB::table('academic_classes')->select('id', 'name')->where('id', $row->academic_class_id)->first()
            : null;

        $row->student = null;
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'online_admission_id')) {
            $row->student = DB::table('students')->select('id', 'college_roll')->where('online_admission_id', $row->id)->first();
        }

        $subjects = [];
        if (Schema::hasTable('subject_assigns') && Schema::hasTable('subject_assign_details') && Schema::hasTable('subjects')) {
            $assign = DB::table('subject_assigns')->where([
                'academic_qualification_id' => $row->academic_qualification_id,
                'department_id' => $row->department_id,
                'academic_class_id' => $row->academic_class_id,
            ])->first();

            if ($assign) {
                $details = DB::table('subject_assign_details as d')
                    ->join('subjects as s', 's.id', '=', 'd.subject_id')
                    ->where('d.subject_assign_id', $assign->id)
                    ->where('d.fourth_subject', 0)
                    ->where('d.main_subject', 0)
                    ->where('s.is_child', 0)
                    ->select('d.subject_id', 's.name_en')
                    ->orderBy('d.sorting')
                    ->get();

                $subjects = $details->map(fn($d) => ['subject' => ['name_en' => $d->name_en]])->values()->all();
            }
        }

        if ($request->boolean('json_data')) {
            return response()->json($row);
        }

        $config = $this->resolveSiteConfig();
        
        // Base64 encode images for PDF to ensure rendering in DomPDF
        $config['online_admission_form_image'] = $this->base64Image($config['online_admission_form_image'] ?? '');
        $row->profile = $this->base64Image($row->profile ?? '');

        $pdf = Pdf::loadView('pdf.online_admission_form', [
            'data' => $row,
            'config' => $config,
            'subjects' => $subjects,
        ])->setPaper('a4', 'portrait')->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => false,
            'defaultFont' => 'sans-serif',
            'dpi' => 96
        ]);

        $stdName = preg_replace('/[^a-z0-9\-]/i', '-', (string) ($row->name ?? ''));
        $mobile = $row->mobile ?? '';

        $fileName = "online-admission-form({$stdName}-{$mobile}).pdf";

        return $pdf->download($fileName);
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

    public function success(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateInvoiceStatus($tranId, 'success', $request);
        }

        return redirect('/online-admission-payment?status=success&tran_id=' . urlencode($tranId));
    }

    public function fail(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateInvoiceStatus($tranId, 'failed', $request);
        }

        return redirect('/online-admission-payment?status=failed&tran_id=' . urlencode($tranId));
    }

    public function cancel(Request $request)
    {
        $tranId = (string) ($request->input('tran_id') ?? $request->input('value_a') ?? '');
        if ($tranId !== '') {
            $this->updateInvoiceStatus($tranId, 'cancelled', $request);
        }

        return redirect('/online-admission-payment?status=cancelled&tran_id=' . urlencode($tranId));
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
