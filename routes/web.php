<?php

use App\Models\ResultDetails;
use App\Models\ResultMarks;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\BankSettlementController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\PublicStudentAuthController;
use App\Http\Controllers\PublicStudentDashboardController;
use App\Http\Controllers\PublicStudentPayNowController;
use App\Http\Controllers\PublicStudentProfileController;
use App\Http\Controllers\PublicStudentPaymentHistoryController;
use App\Http\Controllers\PublicStudentFeesController;
use App\Http\Controllers\PublicStudentAdmitCardController;
use App\Http\Controllers\PublicApplyFeesController;
use App\Http\Controllers\PublicOnlineAdmissionController;
use App\Http\Controllers\PublicStudentResultController;
use App\Http\Controllers\PublicStudentSubjectsController;
use App\Http\Controllers\PublicStudentChangePasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::view('/', 'layouts.frontend_app');
Route::view('/content/{slug}', 'layouts.frontend_app');
Route::view('/contactus', 'layouts.frontend_app');
Route::view('/registration', 'layouts.frontend_app');
Route::view('/login', 'layouts.frontend_app');
Route::view('/forgot-password', 'layouts.frontend_app');
Route::view('/dashboard', 'layouts.frontend_app');
Route::view('/student/profile', 'layouts.frontend_app');
Route::view('/student/profile/edit', 'layouts.frontend_app');
Route::view('/student/invoices/{id}', 'layouts.frontend_app');
Route::view('/student/pay-now', 'layouts.frontend_app');
Route::view('/student/payment-history', 'layouts.frontend_app');
Route::view('/student/fees', 'layouts.frontend_app');
Route::view('/student/admit-card', 'layouts.frontend_app');
Route::view('/student/result', 'layouts.frontend_app');
Route::view('/student/subjects', 'layouts.frontend_app');
Route::view('/student/change-password', 'layouts.frontend_app');
Route::view('/apply-fees', 'layouts.frontend_app');
Route::view('/online-admission', 'layouts.frontend_app');
Route::view('/online-admission-payment', 'layouts.frontend_app');
Route::view('/online-admission-invoice', 'layouts.frontend_app');
Route::view('/online-admission-download-form', 'layouts.frontend_app');
Route::view('/notices', 'layouts.frontend_app');
Route::view('/notices/{id}', 'layouts.frontend_app');

Route::get('/api/public/home', [PublicSiteController::class, 'homeData']);
Route::get('/api/public/content/{slug}', [PublicSiteController::class, 'content']);
Route::get('/api/public/notices', [PublicSiteController::class, 'notices']);
Route::get('/api/public/notices/{id}', [PublicSiteController::class, 'noticeShow']);

Route::get('/api/public/registration/systems', [PublicRegistrationController::class, 'systems']);
Route::post('/api/public/registration', [PublicRegistrationController::class, 'register']);

Route::post('/api/public/auth/login', [PublicStudentAuthController::class, 'login']);
Route::get('/api/public/auth/me', [PublicStudentAuthController::class, 'me']);
Route::post('/api/public/auth/logout', [PublicStudentAuthController::class, 'logout']);

Route::get('/api/public/student/dashboard', [PublicStudentDashboardController::class, 'dashboard']);
Route::get('/api/public/student/invoices/{id}/view', [PublicStudentDashboardController::class, 'viewInvoice']);
Route::get('/api/public/student/invoices/{id}/download', [PublicStudentDashboardController::class, 'downloadInvoice']);

Route::get('/api/public/student/payment-history', [PublicStudentPaymentHistoryController::class, 'index']);
Route::get('/api/public/student/fees', [PublicStudentFeesController::class, 'index']);
Route::get('/api/public/student/admit-cards', [PublicStudentAdmitCardController::class, 'index']);
Route::get('/api/public/student/admit-cards/{id}/download', [PublicStudentAdmitCardController::class, 'download']);

Route::post('/api/public/student/result/search', [PublicStudentResultController::class, 'search']);
Route::get('/api/public/student/result/marksheet/{id}/download', [PublicStudentResultController::class, 'downloadMarksheet']);

Route::get('/api/public/student/subjects', [PublicStudentSubjectsController::class, 'index']);
Route::post('/api/public/student/subjects/assign', [PublicStudentSubjectsController::class, 'store']);

Route::post('/api/public/student/change-password/check-old-password', [PublicStudentChangePasswordController::class, 'checkOldPassword']);
Route::post('/api/public/student/change-password', [PublicStudentChangePasswordController::class, 'changePassword']);

Route::get('/api/public/student/pay-now/systems', [PublicStudentPayNowController::class, 'systems']);
Route::post('/api/public/student/pay-now/check-invoice', [PublicStudentPayNowController::class, 'checkInvoice']);
Route::post('/api/public/student/pay-now/init', [PublicStudentPayNowController::class, 'init']);
Route::post('/api/public/student/pay-now/pay-existing', [PublicStudentPayNowController::class, 'payExisting']);
Route::match(['get', 'post'], '/api/public/student/pay-now/success', [PublicStudentPayNowController::class, 'success']);
Route::match(['get', 'post'], '/api/public/student/pay-now/fail', [PublicStudentPayNowController::class, 'fail']);
Route::match(['get', 'post'], '/api/public/student/pay-now/cancel', [PublicStudentPayNowController::class, 'cancel']);
Route::match(['get', 'post'], '/api/public/student/pay-now/ipn', [PublicStudentPayNowController::class, 'ipn']);

Route::get('/api/public/student/profile/systems', [PublicStudentProfileController::class, 'systems']);
Route::post('/api/public/student/profile', [PublicStudentProfileController::class, 'update']);

Route::get('/api/public/apply-fees/systems', [PublicApplyFeesController::class, 'systems']);
Route::post('/api/public/apply-fees/init', [PublicApplyFeesController::class, 'init']);
Route::post('/api/public/apply-fees/check-invoice', [PublicApplyFeesController::class, 'checkInvoice']);
Route::post('/api/public/apply-fees/pay-existing', [PublicApplyFeesController::class, 'payExisting']);
Route::match(['get', 'post'], '/api/public/apply-fees/success', [PublicApplyFeesController::class, 'success']);
Route::match(['get', 'post'], '/api/public/apply-fees/fail', [PublicApplyFeesController::class, 'fail']);
Route::match(['get', 'post'], '/api/public/apply-fees/cancel', [PublicApplyFeesController::class, 'cancel']);
Route::match(['get', 'post'], '/api/public/apply-fees/ipn', [PublicApplyFeesController::class, 'ipn']);

Route::get('/api/public/certificate/systems', [PublicApplyFeesController::class, 'certificateSystems']);
Route::post('/api/public/certificate/lookup', [PublicApplyFeesController::class, 'certificateLookup']);
Route::post('/api/public/certificate/init', [PublicApplyFeesController::class, 'certificateInit']);
Route::match(['get', 'post'], '/api/public/certificate/success', [PublicApplyFeesController::class, 'certificateSuccess']);
Route::match(['get', 'post'], '/api/public/certificate/fail', [PublicApplyFeesController::class, 'certificateFail']);
Route::match(['get', 'post'], '/api/public/certificate/cancel', [PublicApplyFeesController::class, 'certificateCancel']);
Route::match(['get', 'post'], '/api/public/certificate/ipn', [PublicApplyFeesController::class, 'certificateIpn']);

Route::get('/api/public/online-admission/systems', [PublicOnlineAdmissionController::class, 'systems']);
Route::post('/api/public/online-admission/check-application-fees', [PublicOnlineAdmissionController::class, 'checkApplicationFees']);
Route::post('/api/public/online-admission/check-admission-roll', [PublicOnlineAdmissionController::class, 'checkAdmissionRoll']);
Route::post('/api/public/online-admission/document-upload', [PublicOnlineAdmissionController::class, 'documentUpload']);
Route::post('/api/public/online-admission/submit', [PublicOnlineAdmissionController::class, 'submit']);
Route::post('/api/public/online-admission/payment-heads', [PublicOnlineAdmissionController::class, 'getPaymentHeads']);
Route::post('/api/public/online-admission/check-depend-head', [PublicOnlineAdmissionController::class, 'checkDependHead']);
Route::post('/api/public/online-admission/payments', [PublicOnlineAdmissionController::class, 'payments']);
Route::post('/api/public/online-admission/invoice', [PublicOnlineAdmissionController::class, 'invoice']);
Route::get('/api/public/online-admission/download-form', [PublicOnlineAdmissionController::class, 'downloadForm']);
Route::get('/api/public/online-admission/download-invoice/{id}', [PublicOnlineAdmissionController::class, 'downloadInvoice']);
Route::match(['get', 'post'], '/api/public/online-admission/success', [PublicOnlineAdmissionController::class, 'success']);
Route::match(['get', 'post'], '/api/public/online-admission/fail', [PublicOnlineAdmissionController::class, 'fail']);
Route::match(['get', 'post'], '/api/public/online-admission/cancel', [PublicOnlineAdmissionController::class, 'cancel']);
Route::match(['get', 'post'], '/api/public/online-admission/ipn', [PublicOnlineAdmissionController::class, 'ipn']);

Route::post('/sslcommerz/settlement', [BankSettlementController::class, 'settlement']);

Route::middleware('web')->prefix('admin')->group(base_path('routes/backend.php'));



Route::get('/ct-null', function (Request $request) {

    // Step 1: Get detail IDs from result_details where result_id = 50
    $detailIds = ResultDetails::where('result_id', 50)
        ->pluck('id')
        ->toArray();

    if (empty($detailIds)) {
        return response()->json([
            'ok' => true,
            'result_id' => 50,
            'subject_id' => 18,
            'detail_ids_count' => 0,
            'updated_rows' => 0,
        ]);
    }

    // Step 2: Update result_marks
    $updated = ResultMarks::whereIn('result_details_id', $detailIds)
        ->where('subject_id', 18)
        ->update([
            'ct_mark' => null,
            'updated_at' => now(), // optional but safe
        ]);

    return response()->json([
        'ok' => true,
        'result_id' => 50,
        'subject_id' => 18,
        'detail_ids_count' => count($detailIds),
        'updated_rows' => $updated,
    ]);
});