<?php

use App\Http\Controllers\api\Auth\StudentRegisterController;
use App\Http\Controllers\api\SRLMS\SyncStudentController;
use App\Http\Controllers\api\SubjectAssignController;
use App\Http\Controllers\api\BusController;
use App\Http\Controllers\api\CalenderController;
use App\Http\Controllers\api\ClassRoutineController;
use App\Http\Controllers\api\ExamRoutineController;
use App\Http\Controllers\api\SliderController;
use App\Http\Controllers\api\VideoSliderController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

//  Login Student
Route::namespace('api')->group(function () {
    Route::post('auth/register', 'Auth\StudentRegisterController@registration');
    Route::post('auth/login', 'Auth\StudentLoginController@login');
    Route::post('auth/verify-token', 'Auth\StudentLoginController@verifyToken'); // Token validity check
    Route::post('store-device-id', 'Auth\StudentLoginController@storeDeviceID');

    Route::get('global-data', 'LibController@index');
    Route::get('subjects', 'LibController@subjects');
    Route::get('subject-assigns', 'LibController@subjectsAssigns');
    Route::get('mobile-app-version', 'LibController@mobileAppVersion');
    Route::get('teachers-and-staff', 'LibController@teachersAndStaff');


    // Application Fees
    Route::get('application/heads', 'AdmissionController@getPurposes');
    Route::post('application/paynow', 'AdmissionController@store');
    Route::post('application/payInvoice', 'AdmissionController@payInvoice');
    Route::post('application/invoice', 'AdmissionController@invoice');

    // Online Admission
    Route::post('onlineAdmission/check-application-fees', 'OnlineAdmissionController@checkApplicationFees');
    Route::post('onlineAdmission/check-online-admission-roll', 'OnlineAdmissionController@checkAdmissionRoll');
    Route::post('onlineAdmission/document-upload', 'OnlineAdmissionController@documentUpload');
    Route::post('onlineAdmission/heads', 'OnlineAdmissionController@getPaymentHeads');
    Route::post('onlineAdmission/check-depend-head', 'OnlineAdmissionController@checkDependHead');
    Route::post('onlineAdmission/submit', 'OnlineAdmissionController@store');
    Route::post('onlineAdmission/payInvoice', 'OnlineAdmissionController@payInvoice');
    Route::post('onlineAdmission/invoice', 'OnlineAdmissionController@invoice');
    Route::get('onlineAdmission/download-invoice/{id}', 'OnlineAdmissionController@downloadInvoice');
    Route::get('onlineAdmission/download-form', 'OnlineAdmissionController@downloadForm');

    // Logged Student
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', 'Auth\StudentLoginController@logout');
        Route::get('auth/student', 'StudentController@info');
        Route::match(['post', 'patch'], 'auth/student/update', 'StudentController@update');
        Route::post('change-password', 'StudentController@passwordChange');

        Route::get('department/notices', 'StudentController@notices');

        // Fees
        Route::get('fees', 'FeesController@index');
        Route::get('fees/heads', 'FeesController@duesPaymentHeads');

        // Pay Now (College Fees)
        Route::get('fees/history', 'PaymentController@index');
        Route::post('fees/depended-head', 'PaymentController@checkDependHead');
        Route::post('fees/registration-no', 'PaymentController@checkRegistrationNo');
        Route::get('fees/invoiceno', 'PaymentController@generateInvoiceNo');
        Route::post('fees/paynow', 'PaymentController@store');
        Route::post('fees/payInvoice', 'PaymentController@payInvoice');
        Route::post('fees/invoice', 'PaymentController@invoice');
        Route::post('get-subjects-by-payment', 'PaymentController@getSubjectsByPaymentType');


        // Hostel Fees
        Route::get('hostel/heads', 'HostelFeesController@index');
        Route::post('hostel/paynow', 'HostelFeesController@store');
        Route::post('hostel/payInvoice', 'HostelFeesController@payInvoice');
        Route::post('hostel/invoice', 'HostelFeesController@invoice');

        // Wallet Transaction
        Route::get('wallet/balance', 'WalletController@walletBalance');
        Route::get('wallet/transactions', 'WalletController@index');
        Route::get('wallet/transno', 'WalletController@generateTransNo');
        Route::post('wallet/recharge', 'WalletController@store');
        Route::post('wallet/payInvoice', 'WalletController@payInvoice');

        // Result
        Route::post('search-result', 'ResultController@result');
        Route::get('download-marksheet/{id}', 'ResultController@downloadMarksheet');

        // Admit Card
        Route::get('get-admit-card', 'AdmitCardController@index');
        Route::get('download-admit-card/{id}', 'AdmitCardController@downloadAdmitCard');

        // Attendance
        Route::get('get-attendance', 'AttendanceController@index');
    });
});


// CORN JOBS
Route::get('attendance-sync', 'api\CornJob\AttendanceController@syncAttendance');
Route::get('attendance-queue', 'api\CornJob\AttendanceController@attendanceQueueRun');

// frontend
Route::group(['namespace' => 'api\Frontend'], function () {
    Route::get('get-config', 'HomeController@getConfig');
    Route::get('get-notices', 'HomeController@getNotices');
    Route::get('get-content/{slug}', 'HomeController@content');

    // Forgot Pass
    Route::post('send-otp', 'OTPController@sendOTP');
    Route::post('check-otp', 'OTPController@checkOTP');
    Route::post('forgot-password', 'StudentController@passwordChange');

    // For Nagad Callback
    Route::get('/nagad/callback', 'WalletController@callback');
});

// Bus
Route::get('bus', [BusController::class, 'index'])->name('bus.index');
Route::get('bus/create', [BusController::class, 'create'])->name('bus.create');
Route::post('bus', [BusController::class, 'store'])->name('bus.store');
Route::get('bus/{bus}', [BusController::class, 'show'])->name('bus.show');
Route::get('bus/{bus}/edit', [BusController::class, 'edit'])->name('bus.edit');
Route::put('bus/{bus}', [BusController::class, 'update'])->name('bus.update');
Route::delete('bus/{bus}', [BusController::class, 'destroy'])->name('bus.destroy');


// Class Routine
Route::get('class', [ClassRoutineController::class, 'index'])->name('class.index');
Route::get('class/create', [ClassRoutineController::class, 'create'])->name('class.create');
Route::post('class', [ClassRoutineController::class, 'store'])->name('class.store');
Route::get('class/{class}', [ClassRoutineController::class, 'show'])->name('class.show');
Route::get('class/{class}/edit', [ClassRoutineController::class, 'edit'])->name('class.edit');
Route::put('class/{class}', [ClassRoutineController::class, 'update'])->name('class.update');
Route::delete('class/{class}', [ClassRoutineController::class, 'destroy'])->name('class.destroy');

// Class Routine
Route::get('examR', [ExamRoutineController::class, 'index'])->name('examR.index');
Route::get('examR/create', [ExamRoutineController::class, 'create'])->name('examR.create');
Route::post('examR', [ExamRoutineController::class, 'store'])->name('examR.store');
Route::get('examR/{examR}', [ExamRoutineController::class, 'show'])->name('examR.show');
Route::get('examR/{examR}/edit', [ExamRoutineController::class, 'edit'])->name('examR.edit');
Route::put('examR/{examR}', [ExamRoutineController::class, 'update'])->name('examR.update');
Route::delete('examR/{examR}', [ExamRoutineController::class, 'destroy'])->name('examR.destroy');

// Calender
Route::get('calender', [CalenderController::class, 'index'])->name('calender.index');
Route::get('calender/create', [CalenderController::class, 'create'])->name('calender.create');
Route::post('calender', [CalenderController::class, 'store'])->name('calender.store');
Route::get('calender/{calender}', [CalenderController::class, 'show'])->name('calender.show');
Route::get('calender/{calender}/edit', [CalenderController::class, 'edit'])->name('calender.edit');
Route::put('calender/{calender}', [CalenderController::class, 'update'])->name('calender.update');
Route::delete('calender/{calender}', [CalenderController::class, 'destroy'])->name('calender.destroy');

// Slider
Route::get('slider', [SliderController::class, 'index'])->name('slider.index');
Route::get('slider/create', [SliderController::class, 'create'])->name('slider.create');
Route::post('slider', [SliderController::class, 'store'])->name('slider.store');
Route::get('slider/{slider}', [SliderController::class, 'show'])->name('slider.show');
Route::get('slider/{slider}/edit', [SliderController::class, 'edit'])->name('slider.edit');
Route::put('slider/{slider}', [SliderController::class, 'update'])->name('slider.update');
Route::delete('slider/{slider}', [SliderController::class, 'destroy'])->name('slider.destroy');

// Slider for Dashboard
Route::get('videoSlider', [VideoSliderController::class, 'index'])->name('videoSlider.index');
Route::get('videoSlider/create', [VideoSliderController::class, 'create'])->name('videoSlider.create');
Route::post('videoSlider', [VideoSliderController::class, 'store'])->name('videoSlider.store');
Route::get('videoSlider/{videoSlider}', [VideoSliderController::class, 'show'])->name('videoSlider.show');
Route::get('videoSlider/{videoSlider}/edit', [VideoSliderController::class, 'edit'])->name('videoSlider.edit');
Route::put('videoSlider/{videoSlider}', [VideoSliderController::class, 'update'])->name('videoSlider.update');
Route::delete('videoSlider/{videoSlider}', [VideoSliderController::class, 'destroy'])->name('videoSlider.destroy');

Route::get('student/get-subjects', [SubjectAssignController::class, 'index'])->name('studentSubjects.index');

/**
 * StudyRoom Synchronization
 */
Route::prefix('erp')->group(function () {
    Route::post('sync-students', [SyncStudentController::class, 'sync_students']);
});

Route::post('student/registration', [StudentRegisterController::class, 'registration']);