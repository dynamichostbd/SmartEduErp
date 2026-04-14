<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\Backend\SubjectAssignController;
use App\Http\Controllers\Backend\IDCardController;
use App\Http\Controllers\Backend\StudentController;
use App\Http\Controllers\Backend\StudentPromotionController;
use App\Http\Controllers\Backend\StudentMigrationRollVerifyController;
use App\Http\Controllers\Backend\StudentMigrationController;
use App\Http\Controllers\Backend\RegistrationNoVerifyController;
use App\Http\Controllers\Backend\CertificateApplicationController;
use App\Http\Controllers\Backend\CertificateTemplateController;
use App\Http\Controllers\Backend\AccountHeadController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\FeeSetupController;
use App\Http\Controllers\Backend\InvoiceController;
use App\Http\Controllers\Backend\AdmissionController;
use App\Http\Controllers\Backend\AdmissionFeeSetupController;
use App\Http\Controllers\Backend\AdminManagementController;
use App\Http\Controllers\Backend\PaymentGatewayController;
use App\Http\Controllers\Backend\OnlineAdmissionController;
use App\Http\Controllers\Backend\OnlineAdmissionRollVerifyController;
use App\Http\Controllers\Backend\AttendanceController;
use App\Http\Controllers\Backend\AttendanceSummaryController;
use App\Http\Controllers\Backend\TeacherController;
use App\Http\Controllers\Backend\TeacherAttendanceController;
use App\Http\Controllers\Backend\TeacherIdCardController;
use App\Http\Controllers\Backend\LeaveApplicationController;
use App\Http\Controllers\Backend\LibraryBooksInfoController;
use App\Http\Controllers\Backend\AdmitCardController;
use App\Http\Controllers\Backend\ExamController;
use App\Http\Controllers\Backend\Result\ClassTestResultController;
use App\Http\Controllers\Backend\Result\ResultController;
use App\Http\Controllers\Backend\System\LibController;
use App\Http\Controllers\Backend\System\RoleController;
use App\Http\Controllers\Backend\System\SiteSettingController;
use App\Http\Controllers\Backend\System\MenuController;
use App\Http\Controllers\Backend\System\ActivityLogController;
use App\Http\Controllers\Backend\MasterSetup\AcademicSessionController;
use App\Http\Controllers\Backend\MasterSetup\AcademicQualificationController;
use App\Http\Controllers\Backend\MasterSetup\AcademicClassController;
use App\Http\Controllers\Backend\MasterSetup\DepartmentController;
use App\Http\Controllers\Backend\MasterSetup\DesignationController;
use App\Http\Controllers\Backend\MasterSetup\LeaveTypeController;
use App\Http\Controllers\Backend\MasterSetup\HolidayController;
use App\Http\Controllers\Backend\MasterSetup\SubjectController;
use App\Http\Controllers\Backend\MasterSetup\SubjectClusterController;
use App\Http\Controllers\Backend\MasterSetup\ExamController as MasterSetupExamController;
use App\Http\Controllers\Backend\MasterSetup\SubjectAssignController as MasterSetupSubjectAssignController;
use App\Http\Controllers\Backend\SMS\SmsTemplateController;
use App\Http\Controllers\Backend\SMS\SmsHistoryController;
use App\Http\Controllers\Backend\SMS\SmsTransactionController;
use App\Http\Controllers\Backend\HostelController;
use App\Http\Controllers\Backend\HostelFeeGenerateController;
use App\Http\Controllers\Backend\HostelFeeSetupController;
use App\Http\Controllers\Backend\HostelPaymentController;
use App\Http\Controllers\Backend\Website\BusController;
use App\Http\Controllers\Backend\Website\CalenderController;
use App\Http\Controllers\Backend\Website\ClassRoutineController;
use App\Http\Controllers\Backend\Website\ContentController;
use App\Http\Controllers\Backend\Website\ExamRoutineController;
use App\Http\Controllers\Backend\Website\NoticeController;
use App\Http\Controllers\Backend\Website\PopupController;
use App\Http\Controllers\Backend\Website\SliderController;
use App\Http\Controllers\Backend\Website\VideoSliderController;
use Illuminate\Support\Facades\Route;

Route::get('loginme', [AdminLoginController::class, 'login'])->name('admin.loginme');
Route::post('send-otp', [OTPController::class, 'sendOTP']);
Route::post('verify-otp', [OTPController::class, 'verifyOTP']);
Route::post('system-admin', [AdminLoginController::class, 'login']);
Route::get('login-check', [AdminLoginController::class, 'loginCheck'])->name('admin.loginCheck');
Route::post('logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

Route::middleware('auth:admin')->group(function () {
    Route::get('initialize-systems', [LibController::class, 'systems']);

    Route::get('get-menus/{any?}', [MenuController::class, 'menus'])->name('menu.getMenus');
    Route::get('activity-admins', [ActivityLogController::class, 'admins'])->name('activityLog.admins');

    Route::get('get-dashboard-info', [AdminController::class, 'dashboardInfo']);
    Route::get('today-payments', [InvoiceController::class, 'todayPayments']);
    Route::view('invoice-print', 'layouts.backend_app')->name('invoice.print');
    Route::post('get-print-invoice', [InvoiceController::class, 'invoicePrint']);

    Route::get('get-exam', [ExamController::class, 'index'])->name('get-exam.index');
    Route::get('get-designation', [DesignationController::class, 'index'])->name('get-designation.index');
    Route::get('all-subjects', [SubjectController::class, 'allSubjects'])->name('subject.allSubjects');
    Route::get('all-child-subjects', [SubjectController::class, 'allChildSubjects'])->name('subject.allChildSubjects');

    Route::get('get-departments-level', [DepartmentController::class, 'departmentQualifications'])->name('department.departmentQualifications');

    Route::get('classwise-subjects', [SubjectAssignController::class, 'subjectLists'])->name('classwise-subjects.index');
    Route::get('get-student-subjects/{id}', [SubjectAssignController::class, 'index'])->name('get-student-subjects.index');
    Route::post('subject-assign', [SubjectAssignController::class, 'store'])->name('subject-assign.store');

    Route::get('student/print', [StudentController::class, 'print'])->name('student.print');
    Route::get('student/export-csv', [StudentController::class, 'exportCsv'])->name('student.exportCsv');
    Route::post('student/bulk-deactivate', [StudentController::class, 'bulkDeactivate'])->name('student.bulkDeactivate');
    Route::post('download-student-zip-image', [StudentController::class, 'downloadStudentZipImage'])->name('student.downloadZipImage');
    Route::get('check-zip-status/{filename}', [StudentController::class, 'checkZipStatus'])->name('student.zip.status');
    Route::get('download-zip/{filename}', [StudentController::class, 'downloadZip'])->name('student.zip.download');
    Route::get('admit-card-four-in-one-bulk', [StudentController::class, 'downloadBulkAdminAdmit'])->name('admit.card.bulk');
    Route::get('admit-card-two-in-one-bulk', [StudentController::class, 'downloadBulkAdminAdmitTwoInOne'])->name('admit.cardTwoInOne.bulk');
    Route::get('exam-seat-card-bulk', [StudentController::class, 'downloadBulkSeatCard'])->name('seat.card.bulk');

    Route::post('check-old-password', [AdminManagementController::class, 'checkOldPassword'])->name('admin.checkOldPassword');
    Route::post('change-password', [AdminManagementController::class, 'changePassword'])->name('admin.changePassword');

    Route::view('dashboard', 'layouts.backend_app')->name('admin.dashboard');
});

Route::middleware(['auth:admin', 'auth.access'])->group(function () {
    /*-----CONTENT PORTION-----*/
    Route::view('content/{slug}/create', 'layouts.backend_app')->name('content.create');
    Route::get('content/{slug}', [ContentController::class, 'show'])->name('content.show');
    Route::view('content-file/{slug}', 'layouts.backend_app')->name('content.file');
    Route::post('content', [ContentController::class, 'store'])->name('content.store');
    Route::post('content-file/{contentId}', [ContentController::class, 'storeFile'])->name('content.storeFile');
    Route::delete('content/{contentFileId}', [ContentController::class, 'destroy'])->name('content.destroy');

    // Bus
    Route::get('bus', [BusController::class, 'index'])->name('bus.index');
    Route::view('bus/create', 'layouts.backend_app')->name('bus.create');
    Route::post('bus', [BusController::class, 'store'])->name('bus.store');
    Route::get('bus/{id}', [BusController::class, 'show'])->name('bus.show');
    Route::view('bus/{id}/edit', 'layouts.backend_app')->name('bus.edit');
    Route::put('bus/{id}', [BusController::class, 'update'])->name('bus.update');
    Route::delete('bus/{id}', [BusController::class, 'destroy'])->name('bus.destroy');

    // Class Routine
    Route::get('class', [ClassRoutineController::class, 'index'])->name('class.index');
    Route::view('class/create', 'layouts.backend_app')->name('class.create');
    Route::post('class', [ClassRoutineController::class, 'store'])->name('class.store');
    Route::get('class/{id}', [ClassRoutineController::class, 'show'])->name('class.show');
    Route::view('class/{id}/edit', 'layouts.backend_app')->name('class.edit');
    Route::put('class/{id}', [ClassRoutineController::class, 'update'])->name('class.update');
    Route::delete('class/{id}', [ClassRoutineController::class, 'destroy'])->name('class.destroy');

    // Exam Routine
    Route::get('examR', [ExamRoutineController::class, 'index'])->name('examR.index');
    Route::view('examR/create', 'layouts.backend_app')->name('examR.create');
    Route::post('examR', [ExamRoutineController::class, 'store'])->name('examR.store');
    Route::get('examR/{id}', [ExamRoutineController::class, 'show'])->name('examR.show');
    Route::view('examR/{id}/edit', 'layouts.backend_app')->name('examR.edit');
    Route::put('examR/{id}', [ExamRoutineController::class, 'update'])->name('examR.update');
    Route::delete('examR/{id}', [ExamRoutineController::class, 'destroy'])->name('examR.destroy');

    // Calender
    Route::get('calender', [CalenderController::class, 'index'])->name('calender.index');
    Route::view('calender/create', 'layouts.backend_app')->name('calender.create');
    Route::post('calender', [CalenderController::class, 'store'])->name('calender.store');
    Route::get('calender/{id}', [CalenderController::class, 'show'])->name('calender.show');
    Route::view('calender/{id}/edit', 'layouts.backend_app')->name('calender.edit');
    Route::put('calender/{id}', [CalenderController::class, 'update'])->name('calender.update');
    Route::delete('calender/{id}', [CalenderController::class, 'destroy'])->name('calender.destroy');

    // Slider
    Route::get('slider', [SliderController::class, 'index'])->name('slider.index');
    Route::view('slider/create', 'layouts.backend_app')->name('slider.create');
    Route::post('slider', [SliderController::class, 'store'])->name('slider.store');
    Route::get('slider/{id}', [SliderController::class, 'show'])->name('slider.show');
    Route::view('slider/{id}/edit', 'layouts.backend_app')->name('slider.edit');
    Route::put('slider/{id}', [SliderController::class, 'update'])->name('slider.update');
    Route::delete('slider/{id}', [SliderController::class, 'destroy'])->name('slider.destroy');

    // Slider for Dashboard
    Route::get('videoSlider', [VideoSliderController::class, 'index'])->name('videoSlider.index');
    Route::view('videoSlider/create', 'layouts.backend_app')->name('videoSlider.create');
    Route::post('videoSlider', [VideoSliderController::class, 'store'])->name('videoSlider.store');
    Route::get('videoSlider/{id}', [VideoSliderController::class, 'show'])->name('videoSlider.show');
    Route::view('videoSlider/{id}/edit', 'layouts.backend_app')->name('videoSlider.edit');
    Route::put('videoSlider/{id}', [VideoSliderController::class, 'update'])->name('videoSlider.update');
    Route::delete('videoSlider/{id}', [VideoSliderController::class, 'destroy'])->name('videoSlider.destroy');

    // Popup
    Route::get('popup', [PopupController::class, 'index'])->name('popup.index');
    Route::view('popup/create', 'layouts.backend_app')->name('popup.create');
    Route::post('popup', [PopupController::class, 'store'])->name('popup.store');
    Route::get('popup/{id}', [PopupController::class, 'show'])->name('popup.show');
    Route::view('popup/{id}/edit', 'layouts.backend_app')->name('popup.edit');
    Route::put('popup/{id}', [PopupController::class, 'update'])->name('popup.update');
    Route::delete('popup/{id}', [PopupController::class, 'destroy'])->name('popup.destroy');

    // Notice
    Route::get('notice', [NoticeController::class, 'index'])->name('notice.index');
    Route::view('notice/create', 'layouts.backend_app')->name('notice.create');
    Route::post('notice', [NoticeController::class, 'store'])->name('notice.store');
    Route::get('notice/{id}', [NoticeController::class, 'show'])->name('notice.show');
    Route::view('notice/{id}/edit', 'layouts.backend_app')->name('notice.edit');
    Route::put('notice/{id}', [NoticeController::class, 'update'])->name('notice.update');
    Route::delete('notice/{id}', [NoticeController::class, 'destroy'])->name('notice.destroy');
    Route::get('notice-officeOrder', [NoticeController::class, 'officeOrder'])->name('notice.officeOrder');

    // Master Setup: Academic Session
    Route::get('academicSession', [AcademicSessionController::class, 'index'])->name('academicSession.index');
    Route::view('academicSession/create', 'layouts.backend_app')->name('academicSession.create');
    Route::post('academicSession', [AcademicSessionController::class, 'store'])->name('academicSession.store');
    Route::get('academicSession/{id}', [AcademicSessionController::class, 'show'])->name('academicSession.show');
    Route::view('academicSession/{id}/edit', 'layouts.backend_app')->name('academicSession.edit');
    Route::put('academicSession/{id}', [AcademicSessionController::class, 'update'])->name('academicSession.update');
    Route::delete('academicSession/{id}', [AcademicSessionController::class, 'destroy'])->name('academicSession.destroy');

    // Master Setup: Academic Level
    Route::get('academicQualification', [AcademicQualificationController::class, 'index'])->name('academicQualification.index');
    Route::view('academicQualification/create', 'layouts.backend_app')->name('academicQualification.create');
    Route::post('academicQualification', [AcademicQualificationController::class, 'store'])->name('academicQualification.store');
    Route::get('academicQualification/{id}', [AcademicQualificationController::class, 'show'])->name('academicQualification.show');
    Route::view('academicQualification/{id}/edit', 'layouts.backend_app')->name('academicQualification.edit');
    Route::put('academicQualification/{id}', [AcademicQualificationController::class, 'update'])->name('academicQualification.update');
    Route::delete('academicQualification/{id}', [AcademicQualificationController::class, 'destroy'])->name('academicQualification.destroy');

    // Master Setup: Academic Class
    Route::get('academicClass', [AcademicClassController::class, 'index'])->name('academicClass.index');
    Route::view('academicClass/create', 'layouts.backend_app')->name('academicClass.create');
    Route::post('academicClass', [AcademicClassController::class, 'store'])->name('academicClass.store');
    Route::get('academicClass/{id}', [AcademicClassController::class, 'show'])->name('academicClass.show');
    Route::view('academicClass/{id}/edit', 'layouts.backend_app')->name('academicClass.edit');
    Route::put('academicClass/{id}', [AcademicClassController::class, 'update'])->name('academicClass.update');
    Route::delete('academicClass/{id}', [AcademicClassController::class, 'destroy'])->name('academicClass.destroy');

    // Master Setup: Department
    Route::get('department', [DepartmentController::class, 'index'])->name('department.index');
    Route::view('department/create', 'layouts.backend_app')->name('department.create');
    Route::post('department', [DepartmentController::class, 'store'])->name('department.store');
    Route::get('department/{id}', [DepartmentController::class, 'show'])->name('department.show');
    Route::view('department/{id}/edit', 'layouts.backend_app')->name('department.edit');
    Route::put('department/{id}', [DepartmentController::class, 'update'])->name('department.update');
    Route::delete('department/{id}', [DepartmentController::class, 'destroy'])->name('department.destroy');

    // Master Setup: Designation
    Route::get('designation', [DesignationController::class, 'index'])->name('designation.index');
    Route::view('designation/create', 'layouts.backend_app')->name('designation.create');
    Route::post('designation', [DesignationController::class, 'store'])->name('designation.store');
    Route::get('designation/{id}', [DesignationController::class, 'show'])->name('designation.show');
    Route::view('designation/{id}/edit', 'layouts.backend_app')->name('designation.edit');
    Route::put('designation/{id}', [DesignationController::class, 'update'])->name('designation.update');
    Route::delete('designation/{id}', [DesignationController::class, 'destroy'])->name('designation.destroy');

    // Master Setup: Leave Type
    Route::get('leaveType', [LeaveTypeController::class, 'index'])->name('leaveType.index');
    Route::view('leaveType/create', 'layouts.backend_app')->name('leaveType.create');
    Route::post('leaveType', [LeaveTypeController::class, 'store'])->name('leaveType.store');
    Route::get('leaveType/{id}', [LeaveTypeController::class, 'show'])->name('leaveType.show');
    Route::view('leaveType/{id}/edit', 'layouts.backend_app')->name('leaveType.edit');
    Route::put('leaveType/{id}', [LeaveTypeController::class, 'update'])->name('leaveType.update');
    Route::delete('leaveType/{id}', [LeaveTypeController::class, 'destroy'])->name('leaveType.destroy');

    // Master Setup: Holiday
    Route::get('holiday', [HolidayController::class, 'index'])->name('holiday.index');
    Route::view('holiday/create', 'layouts.backend_app')->name('holiday.create');
    Route::post('holiday', [HolidayController::class, 'store'])->name('holiday.store');
    Route::get('holiday/{id}', [HolidayController::class, 'show'])->name('holiday.show');
    Route::view('holiday/{id}/edit', 'layouts.backend_app')->name('holiday.edit');
    Route::put('holiday/{id}', [HolidayController::class, 'update'])->name('holiday.update');
    Route::delete('holiday/{id}', [HolidayController::class, 'destroy'])->name('holiday.destroy');

    // Master Setup: Subject
    Route::get('subject', [SubjectController::class, 'index'])->name('subject.index');
    Route::view('subject/create', 'layouts.backend_app')->name('subject.create');
    Route::post('subject', [SubjectController::class, 'store'])->name('subject.store');
    Route::get('subject/{id}', [SubjectController::class, 'show'])->name('subject.show');
    Route::view('subject/{id}/edit', 'layouts.backend_app')->name('subject.edit');
    Route::put('subject/{id}', [SubjectController::class, 'update'])->name('subject.update');
    Route::delete('subject/{id}', [SubjectController::class, 'destroy'])->name('subject.destroy');

    // Master Setup: Subject Cluster
    Route::get('subjectCluster', [SubjectClusterController::class, 'index'])->name('subjectCluster.index');
    Route::view('subjectCluster/create', 'layouts.backend_app')->name('subjectCluster.create');
    Route::post('subjectCluster', [SubjectClusterController::class, 'store'])->name('subjectCluster.store');
    Route::get('subjectCluster/{id}', [SubjectClusterController::class, 'show'])->name('subjectCluster.show');
    Route::view('subjectCluster/{id}/edit', 'layouts.backend_app')->name('subjectCluster.edit');
    Route::put('subjectCluster/{id}', [SubjectClusterController::class, 'update'])->name('subjectCluster.update');
    Route::delete('subjectCluster/{id}', [SubjectClusterController::class, 'destroy'])->name('subjectCluster.destroy');

    // Master Setup: Exam
    Route::get('exam', [MasterSetupExamController::class, 'index'])->name('exam.index');
    Route::view('exam/create', 'layouts.backend_app')->name('exam.create');
    Route::post('exam', [MasterSetupExamController::class, 'store'])->name('exam.store');
    Route::get('exam/{id}', [MasterSetupExamController::class, 'show'])->name('exam.show');
    Route::view('exam/{id}/edit', 'layouts.backend_app')->name('exam.edit');
    Route::put('exam/{id}', [MasterSetupExamController::class, 'update'])->name('exam.update');
    Route::delete('exam/{id}', [MasterSetupExamController::class, 'destroy'])->name('exam.destroy');

    // Master Setup: Subject Assign
    Route::get('subjectAssign', [MasterSetupSubjectAssignController::class, 'index'])->name('subjectAssign.index');
    Route::view('subjectAssign/create', 'layouts.backend_app')->name('subjectAssign.create');
    Route::post('subjectAssign', [MasterSetupSubjectAssignController::class, 'store'])->name('subjectAssign.store');
    Route::get('subjectAssign/{id}', [MasterSetupSubjectAssignController::class, 'show'])->name('subjectAssign.show');
    Route::view('subjectAssign/{id}/edit', 'layouts.backend_app')->name('subjectAssign.edit');
    Route::put('subjectAssign/{id}', [MasterSetupSubjectAssignController::class, 'update'])->name('subjectAssign.update');
    Route::delete('subjectAssign/{id}', [MasterSetupSubjectAssignController::class, 'destroy'])->name('subjectAssign.destroy');

    // SMS
    Route::get('smsTemplate', [SmsTemplateController::class, 'index'])->name('smsTemplate.index');
    Route::view('smsTemplate/create', 'layouts.backend_app')->name('smsTemplate.create');
    Route::post('smsTemplate', [SmsTemplateController::class, 'store'])->name('smsTemplate.store');
    Route::get('smsTemplate/{id}', [SmsTemplateController::class, 'show'])->name('smsTemplate.show');
    Route::view('smsTemplate/{id}/edit', 'layouts.backend_app')->name('smsTemplate.edit');
    Route::put('smsTemplate/{id}', [SmsTemplateController::class, 'update'])->name('smsTemplate.update');
    Route::delete('smsTemplate/{id}', [SmsTemplateController::class, 'destroy'])->name('smsTemplate.destroy');

    Route::get('smsHistory', [SmsHistoryController::class, 'index'])->name('smsHistory.index');
    Route::view('smsHistory/create', 'layouts.backend_app')->name('smsHistory.create');
    Route::post('smsHistory', [SmsHistoryController::class, 'store'])->name('smsHistory.store');
    Route::get('smsHistory/{id}', [SmsHistoryController::class, 'show'])->name('smsHistory.show');
    Route::delete('smsHistory/{id}', [SmsHistoryController::class, 'destroy'])->name('smsHistory.destroy');
    Route::get('smsHistory/students', [SmsHistoryController::class, 'students'])->name('smsHistory.students');

    Route::get('smsTransaction', [SmsTransactionController::class, 'index'])->name('smsTransaction.index');
    Route::view('smsTransaction/create', 'layouts.backend_app')->name('smsTransaction.create');
    Route::post('smsTransaction', [SmsTransactionController::class, 'store'])->name('smsTransaction.store');
    Route::get('smsTransaction/{id}', [SmsTransactionController::class, 'show'])->name('smsTransaction.show');
    Route::delete('smsTransaction/{id}', [SmsTransactionController::class, 'destroy'])->name('smsTransaction.destroy');

    // Hostel
    Route::get('hostel', [HostelController::class, 'index'])->name('hostel.index');
    Route::view('hostel/create', 'layouts.backend_app')->name('hostel.create');
    Route::post('hostel', [HostelController::class, 'store'])->name('hostel.store');
    Route::get('hostel/{id}', [HostelController::class, 'show'])->name('hostel.show');
    Route::view('hostel/{id}/edit', 'layouts.backend_app')->name('hostel.edit');
    Route::put('hostel/{id}', [HostelController::class, 'update'])->name('hostel.update');
    Route::delete('hostel/{id}', [HostelController::class, 'destroy'])->name('hostel.destroy');

    // Hostel Fee Generate
    Route::get('hostelFeeGenerate', [HostelFeeGenerateController::class, 'index'])->name('hostelFeeGenerate.index');
    Route::view('hostelFeeGenerate/create', 'layouts.backend_app')->name('hostelFeeGenerate.create');
    Route::post('hostelFeeGenerate', [HostelFeeGenerateController::class, 'store'])->name('hostelFeeGenerate.store');
    Route::get('hostelFeeGenerate/{id}', [HostelFeeGenerateController::class, 'show'])->name('hostelFeeGenerate.show');
    Route::view('hostelFeeGenerate/{id}/edit', 'layouts.backend_app')->name('hostelFeeGenerate.edit');
    Route::put('hostelFeeGenerate/{id}', [HostelFeeGenerateController::class, 'update'])->name('hostelFeeGenerate.update');
    Route::delete('hostelFeeGenerate/{id}', [HostelFeeGenerateController::class, 'destroy'])->name('hostelFeeGenerate.destroy');

    // Hostel Payment
    Route::get('hostelPayment', [HostelPaymentController::class, 'index'])->name('hostelPayment.index');
    Route::view('hostelPayment/create', 'layouts.backend_app')->name('hostelPayment.create');
    Route::post('hostelPayment', [HostelPaymentController::class, 'store'])->name('hostelPayment.store');
    Route::get('hostelPayment/{id}', [HostelPaymentController::class, 'show'])->name('hostelPayment.show');
    Route::view('hostelPayment/{id}/edit', 'layouts.backend_app')->name('hostelPayment.edit');
    Route::put('hostelPayment/{id}', [HostelPaymentController::class, 'update'])->name('hostelPayment.update');
    Route::delete('hostelPayment/{id}', [HostelPaymentController::class, 'destroy'])->name('hostelPayment.destroy');
    Route::match(['get'], 'hostelPayment-monthly', [HostelPaymentController::class, 'monthly'])->name('hostelPayment.monthly');
    Route::match(['get'], 'hostelPayment-dues', [HostelPaymentController::class, 'dues'])->name('hostelPayment.dues');
    Route::match(['get', 'post'], 'hostelPayment-students', [HostelPaymentController::class, 'students'])->name('hostelPayment.students');
    Route::match(['post'], 'hostelPayment-discount', [HostelPaymentController::class, 'discount'])->name('hostelPayment.discount');
    Route::match(['get', 'delete'], 'hostel-fees-delete/{id}', [HostelPaymentController::class, 'feesDelete'])->name('hostelPayment.feesDelete');

    // Hostel Fee Setup
    Route::get('hostelFeeSetup', [HostelFeeSetupController::class, 'index'])->name('hostelFeeSetup.index');
    Route::view('hostelFeeSetup/create', 'layouts.backend_app')->name('hostelFeeSetup.create');
    Route::post('hostelFeeSetup', [HostelFeeSetupController::class, 'store'])->name('hostelFeeSetup.store');
    Route::get('hostelFeeSetup/{id}', [HostelFeeSetupController::class, 'show'])->name('hostelFeeSetup.show');
    Route::view('hostelFeeSetup/{id}/edit', 'layouts.backend_app')->name('hostelFeeSetup.edit');
    Route::put('hostelFeeSetup/{id}', [HostelFeeSetupController::class, 'update'])->name('hostelFeeSetup.update');
    Route::delete('hostelFeeSetup/{id}', [HostelFeeSetupController::class, 'destroy'])->name('hostelFeeSetup.destroy');

    // Payment Gateway
    Route::get('paymentGateway', [PaymentGatewayController::class, 'index'])->name('paymentGateway.index');
    Route::view('paymentGateway/create', 'layouts.backend_app')->name('paymentGateway.create');
    Route::post('paymentGateway', [PaymentGatewayController::class, 'store'])->name('paymentGateway.store');
    Route::get('paymentGateway/{id}', [PaymentGatewayController::class, 'show'])->name('paymentGateway.show');
    Route::view('paymentGateway/{id}/edit', 'layouts.backend_app')->name('paymentGateway.edit');
    Route::put('paymentGateway/{id}', [PaymentGatewayController::class, 'update'])->name('paymentGateway.update');
    Route::delete('paymentGateway/{id}', [PaymentGatewayController::class, 'destroy'])->name('paymentGateway.destroy');

    // Fee Setup
    Route::get('feeSetup', [FeeSetupController::class, 'index'])->name('feeSetup.index');
    Route::view('feeSetup/create', 'layouts.backend_app')->name('feeSetup.create');
    Route::post('feeSetup', [FeeSetupController::class, 'store'])->name('feeSetup.store');
    Route::get('feeSetup/{id}', [FeeSetupController::class, 'show'])->name('feeSetup.show');
    Route::view('feeSetup/{id}/edit', 'layouts.backend_app')->name('feeSetup.edit');
    Route::put('feeSetup/{id}', [FeeSetupController::class, 'update'])->name('feeSetup.update');
    Route::delete('feeSetup/{id}', [FeeSetupController::class, 'destroy'])->name('feeSetup.destroy');

    // Teacher
    Route::get('teacher', [TeacherController::class, 'index'])->name('teacher.index');
    Route::get('teacher-idcard', [TeacherIdCardController::class, 'index'])->name('teacher.idcard.index');
    Route::view('teacher/create', 'layouts.backend_app')->name('teacher.create');
    Route::post('teacher', [TeacherController::class, 'store'])->name('teacher.store');
    Route::get('teacher/{id}', [TeacherController::class, 'show'])->name('teacher.show');
    Route::view('teacher/{id}/edit', 'layouts.backend_app')->name('teacher.edit');
    Route::put('teacher/{id}', [TeacherController::class, 'update'])->name('teacher.update');
    Route::delete('teacher/{id}', [TeacherController::class, 'destroy'])->name('teacher.destroy');

    Route::match(['get', 'post'], 'teacher-import', [TeacherController::class, 'import'])->name('teacher.import');

    // Teacher Attendance
    Route::get('teacherAttendance', [TeacherAttendanceController::class, 'index'])->name('teacherAttendance.index');
    Route::view('teacherAttendance/create', 'layouts.backend_app')->name('teacherAttendance.create');
    Route::post('teacherAttendance', [TeacherAttendanceController::class, 'store'])->name('teacherAttendance.store');
    Route::get('teacherAttendance/{id}', [TeacherAttendanceController::class, 'show'])->name('teacherAttendance.show');
    Route::view('teacherAttendance/{id}/edit', 'layouts.backend_app')->name('teacherAttendance.edit');
    Route::put('teacherAttendance/{id}', [TeacherAttendanceController::class, 'update'])->name('teacherAttendance.update');
    Route::delete('teacherAttendance/{id}', [TeacherAttendanceController::class, 'destroy'])->name('teacherAttendance.destroy');

    // Leave Application
    Route::get('leaveApplication', [LeaveApplicationController::class, 'index'])->name('leaveApplication.index');
    Route::view('leaveApplication/create', 'layouts.backend_app')->name('leaveApplication.create');
    Route::post('leaveApplication', [LeaveApplicationController::class, 'store'])->name('leaveApplication.store');
    Route::get('leaveApplication/{id}', [LeaveApplicationController::class, 'show'])->name('leaveApplication.show');
    Route::view('leaveApplication/{id}/edit', 'layouts.backend_app')->name('leaveApplication.edit');
    Route::put('leaveApplication/{id}', [LeaveApplicationController::class, 'update'])->name('leaveApplication.update');
    Route::delete('leaveApplication/{id}', [LeaveApplicationController::class, 'destroy'])->name('leaveApplication.destroy');

    // Library
    Route::get('libraryBooksInfo', [LibraryBooksInfoController::class, 'index'])->name('libraryBooksInfo.index');

    Route::get('student', [StudentController::class, 'index'])->name('student.index');

    Route::get('admin', [AdminManagementController::class, 'index'])->name('admin.index');
    Route::view('admin/create', 'layouts.backend_app')->name('admin.create');
    Route::post('admin', [AdminManagementController::class, 'store'])->name('admin.store');
    Route::get('admin/{id}', [AdminManagementController::class, 'show'])->name('admin.show');
    Route::view('admin/{id}/edit', 'layouts.backend_app')->name('admin.edit');
    Route::put('admin/{id}', [AdminManagementController::class, 'update'])->name('admin.update');
    Route::delete('admin/{id}', [AdminManagementController::class, 'destroy'])->name('admin.destroy');

    Route::get('get-permissions', [RoleController::class, 'getPermissions'])->name('role.getPermissions');

    Route::get('role', [RoleController::class, 'index'])->name('role.index');
    Route::view('role/create', 'layouts.backend_app')->name('role.create');
    Route::post('role', [RoleController::class, 'store'])->name('role.store');
    Route::get('role/{id}', [RoleController::class, 'show'])->name('role.show');
    Route::view('role/{id}/edit', 'layouts.backend_app')->name('role.edit');
    Route::put('role/{id}', [RoleController::class, 'update'])->name('role.update');
    Route::delete('role/{id}', [RoleController::class, 'destroy'])->name('role.destroy');

    Route::get('siteSetting', [SiteSettingController::class, 'index'])->name('siteSetting.index');
    Route::view('siteSetting/{id}/edit', 'layouts.backend_app')->name('siteSetting.edit');
    Route::get('siteSetting/{id}', [SiteSettingController::class, 'show'])->name('siteSetting.show');
    Route::put('siteSetting/{id}', [SiteSettingController::class, 'update'])->name('siteSetting.update');

    Route::get('menu', [MenuController::class, 'index'])->name('menu.index');
    Route::view('menu/create', 'layouts.backend_app')->name('menu.create');
    Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
    Route::get('menu/{id}', [MenuController::class, 'show'])->name('menu.show');
    Route::view('menu/{id}/edit', 'layouts.backend_app')->name('menu.edit');
    Route::put('menu/{id}', [MenuController::class, 'update'])->name('menu.update');
    Route::delete('menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');

    Route::get('activityLog', [ActivityLogController::class, 'index'])->name('activityLog.index');
    Route::get('activityLog/{id}', [ActivityLogController::class, 'show'])->name('activityLog.show');
    Route::delete('activityLog/{id}', [ActivityLogController::class, 'destroy'])->name('activityLog.destroy');
    Route::get('allRead', [ActivityLogController::class, 'allRead'])->name('activityLog.allRead');

    Route::get('admission', [AdmissionController::class, 'index'])->name('admission.index');
    Route::get('admission-account-wise', [AdmissionController::class, 'accountWise'])->name('admission.accountWise');
    Route::get('admission-refund-amount', [AdmissionController::class, 'refundAmount'])->name('admission.refundAmount');
    Route::get('admission-purposes', [AdmissionController::class, 'getPurposes'])->name('admission.getPurposes');
    Route::get('admission/{id}', [AdmissionController::class, 'show'])->name('admission.show');
    Route::view('admission/{id}/edit', 'layouts.backend_app')->name('admission.edit');
    Route::put('admission/{id}', [AdmissionController::class, 'update'])->name('admission.update');
    Route::delete('admission/{id}', [AdmissionController::class, 'destroy'])->name('admission.destroy');

    Route::get('admissionFeeSetup', [AdmissionFeeSetupController::class, 'index'])->name('admissionFeeSetup.index');
    Route::post('admissionFeeSetup', [AdmissionFeeSetupController::class, 'store'])->name('admissionFeeSetup.store');
    Route::view('admissionFeeSetup/create', 'layouts.backend_app')->name('admissionFeeSetup.create');
    Route::get('admissionFeeSetup/{id}', [AdmissionFeeSetupController::class, 'show'])->name('admissionFeeSetup.show');
    Route::view('admissionFeeSetup/{id}/edit', 'layouts.backend_app')->name('admissionFeeSetup.edit');
    Route::put('admissionFeeSetup/{id}', [AdmissionFeeSetupController::class, 'update'])->name('admissionFeeSetup.update');
    Route::delete('admissionFeeSetup/{id}', [AdmissionFeeSetupController::class, 'destroy'])->name('admissionFeeSetup.destroy');

    Route::get('invoice', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::post('invoice', [InvoiceController::class, 'store'])->name('invoice.store');
    Route::view('invoice/create', 'layouts.backend_app')->name('invoice.create');
    Route::get('invoice/{id}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::view('invoice/{id}/edit', 'layouts.backend_app')->name('invoice.edit');
    Route::put('invoice/{id}', [InvoiceController::class, 'update'])->name('invoice.update');
    Route::delete('invoice/{id}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');

    Route::get('account-wise-payment', [InvoiceController::class, 'accountWise'])->name('invoice.accountWise');
    Route::get('account-head-wise-payment', [InvoiceController::class, 'accountHeadWise'])->name('invoice.accountHeadWise');
    Route::get('account-summary', [InvoiceController::class, 'accountSummary'])->name('invoice.accountSummary');
    Route::get('refund-amount', [InvoiceController::class, 'refundAmount'])->name('invoice.refundAmount');

    Route::view('bank-settlement', 'layouts.backend_app')->name('bankSettlement.index');
    Route::get('all-account', [PaymentGatewayController::class, 'allAccount']);

    Route::get('fees-lists', [FeeSetupController::class, 'feesLists'])->name('fees.lists');
    Route::get('exist-feeSetup', [FeeSetupController::class, 'existSetup']);
    Route::get('get-classes', [FeeSetupController::class, 'getClasses']);

    Route::get('admin-admit-card-four-in-one', [StudentController::class, 'adminAdmitCard'])->name('admin.admitCard.index');
    Route::get('admin-admit-card-two-in-one', [StudentController::class, 'adminAdmitCardTwoInOne'])->name('admin.admitCardTwoInOne.index');

    Route::get('exam-seat-card', [StudentController::class, 'seatCard'])->name('seat.card.index');

    Route::post('student', [StudentController::class, 'store']);

    Route::post('student-import', [StudentController::class, 'import']);

    Route::post('studentPromotion/search', [StudentPromotionController::class, 'search']);
    Route::post('studentPromotion', [StudentPromotionController::class, 'store']);

    Route::get('student-idcard', [IDCardController::class, 'index']);

    Route::get('studentMigrationRollVerify/list', [StudentMigrationRollVerifyController::class, 'index']);
    Route::post('studentMigrationRollVerify/import', [StudentMigrationRollVerifyController::class, 'store']);
    Route::get('studentMigrationRollVerify/{id}/details', [StudentMigrationRollVerifyController::class, 'show']);
    Route::delete('studentMigrationRollVerify/{id}', [StudentMigrationRollVerifyController::class, 'destroy']);

    Route::get('studentMigration/list', [StudentMigrationController::class, 'index']);
    Route::get('studentMigration/{id}/details', [StudentMigrationController::class, 'show']);
    Route::post('studentMigration-approved', [StudentMigrationController::class, 'approved']);
    Route::get('studentMigration-reject/{id}', [StudentMigrationController::class, 'reject']);

    Route::get('registrationNoVerify/list', [RegistrationNoVerifyController::class, 'index']);
    Route::post('registrationNoVerify/import', [RegistrationNoVerifyController::class, 'store']);
    Route::get('registrationNoVerify/{id}/details', [RegistrationNoVerifyController::class, 'show']);
    Route::put('registrationNoVerify/{id}', [RegistrationNoVerifyController::class, 'update']);
    Route::delete('registrationNoVerify/{id}', [RegistrationNoVerifyController::class, 'destroy']);

    Route::get('accountHead', [AccountHeadController::class, 'index'])->name('accountHead.index');
    Route::view('accountHead/create', 'layouts.backend_app')->name('accountHead.create');
    Route::post('accountHead', [AccountHeadController::class, 'store'])->name('accountHead.store');
    Route::get('accountHead/{id}', [AccountHeadController::class, 'show'])->name('accountHead.show');
    Route::view('accountHead/{id}/edit', 'layouts.backend_app')->name('accountHead.edit');
    Route::put('accountHead/{id}', [AccountHeadController::class, 'update'])->name('accountHead.update');
    Route::delete('accountHead/{id}', [AccountHeadController::class, 'destroy'])->name('accountHead.destroy');
    Route::get('get-paymentGateway', [PaymentGatewayController::class, 'index']);

    Route::get('certificateTemplate/list', [CertificateTemplateController::class, 'index']);
    Route::post('certificateTemplate', [CertificateTemplateController::class, 'store']);
    Route::get('certificateTemplate/{id}/details', [CertificateTemplateController::class, 'show']);
    Route::post('certificateTemplate/{id}', [CertificateTemplateController::class, 'update']);
    Route::delete('certificateTemplate/{id}', [CertificateTemplateController::class, 'destroy']);

    Route::get('certificateApplication/list', [CertificateApplicationController::class, 'index']);
    Route::get('certificateApplication/{id}/details', [CertificateApplicationController::class, 'show']);
    Route::put('certificateApplication/{id}', [CertificateApplicationController::class, 'update']);
    Route::delete('certificateApplication/{id}', [CertificateApplicationController::class, 'destroy']);
    Route::post('certificateApplication-approved', [CertificateApplicationController::class, 'approved']);
    Route::post('certificateApplication-print', [CertificateApplicationController::class, 'printStatus']);

    Route::get('onlineAdmission/list', [OnlineAdmissionController::class, 'index']);
    Route::get('onlineAdmission/rejected-list', [OnlineAdmissionController::class, 'rejectedList']);
    Route::get('onlineAdmission/{id}/details', [OnlineAdmissionController::class, 'show']);
    Route::post('onlineAdmission/{id}', [OnlineAdmissionController::class, 'update']);
    Route::delete('onlineAdmission/{id}', [OnlineAdmissionController::class, 'destroy']);
    Route::post('onlineAdmission-approved', [OnlineAdmissionController::class, 'approved']);
    Route::get('get-applicant-subjects/{id}', [OnlineAdmissionController::class, 'getSubjects']);
    Route::post('applicant-subject-assign', [OnlineAdmissionController::class, 'subjectAssign']);
    Route::get('approved-sms-send', [OnlineAdmissionController::class, 'approvedSmsSend']);
    Route::get('download-online-admission-form', [OnlineAdmissionController::class, 'downloadOnlineAdmissionForm']);

    Route::get('onlineAdmissionRollVerify/list', [OnlineAdmissionRollVerifyController::class, 'index']);
    Route::post('onlineAdmissionRollVerify/import', [OnlineAdmissionRollVerifyController::class, 'store']);
    Route::get('onlineAdmissionRollVerify/{id}/details', [OnlineAdmissionRollVerifyController::class, 'show']);
    Route::put('onlineAdmissionRollVerify/{id}', [OnlineAdmissionRollVerifyController::class, 'update']);
    Route::delete('onlineAdmissionRollVerify/{id}', [OnlineAdmissionRollVerifyController::class, 'destroy']);

    Route::get('attendance/list', [AttendanceController::class, 'index']);
    Route::get('attendance/students', [AttendanceController::class, 'students']);
    Route::post('attendance', [AttendanceController::class, 'store']);
    Route::get('attendance/{id}/details', [AttendanceController::class, 'show']);
    Route::put('attendance/{id}', [AttendanceController::class, 'update']);
    Route::delete('attendance/{id}', [AttendanceController::class, 'destroy']);

    Route::get('attendance-report/list', [AttendanceController::class, 'attendanceReport']);
    Route::get('attendance-sheet/list', [AttendanceController::class, 'attendanceSheet']);
    Route::get('exam-attendance-sheet/list', [AttendanceController::class, 'examAttendanceSheet']);

    Route::get('attendanceSummary/list', [AttendanceSummaryController::class, 'index']);
    Route::get('attendanceSummary/students', [AttendanceSummaryController::class, 'students']);
    Route::post('attendanceSummary', [AttendanceSummaryController::class, 'store']);
    Route::get('attendanceSummary/{id}/details', [AttendanceSummaryController::class, 'show']);
    Route::put('attendanceSummary/{id}', [AttendanceSummaryController::class, 'update']);
    Route::delete('attendanceSummary/{id}', [AttendanceSummaryController::class, 'destroy']);

    Route::get('admitCard/list', [AdmitCardController::class, 'index']);
    Route::post('admitCard', [AdmitCardController::class, 'store']);
    Route::get('admitCard/{id}/details', [AdmitCardController::class, 'show']);
    Route::put('admitCard/{id}', [AdmitCardController::class, 'update']);
    Route::delete('admitCard/{id}', [AdmitCardController::class, 'destroy']);
    Route::get('admitCard/student/{studentId}', [AdmitCardController::class, 'getStudentAdmitCard']);
    Route::get('get-student-admit-card/{studentId}', [AdmitCardController::class, 'getStudentAdmitCard']);
    Route::get('download-admit-card/{id}/{stdID}', [AdmitCardController::class, 'downloadAdmitCard']);
    Route::post('download-all-admit-cards', [AdmitCardController::class, 'downloadAllAdmitCards']);

    Route::get('classTestResult/list', [ClassTestResultController::class, 'index']);
    Route::post('classTestResult', [ClassTestResultController::class, 'store']);
    Route::get('classTestResult/{id}/details', [ClassTestResultController::class, 'show']);
    Route::post('classTestResult/{id}', [ClassTestResultController::class, 'update']);
    Route::put('classTestResult/{id}', [ClassTestResultController::class, 'update']);
    Route::delete('classTestResult/{id}', [ClassTestResultController::class, 'destroy']);

    Route::post('classTestResult-published', [ClassTestResultController::class, 'published']);
    Route::get('classTestResult-sync/{id}', [ClassTestResultController::class, 'syncResult']);
    Route::get('classTestResult-marksheet/{id}', [ClassTestResultController::class, 'marksheet']);
    Route::get('students-for-class-test-marks-entry', [ClassTestResultController::class, 'studentsForMarksEntry']);

    Route::get('result/list', [ResultController::class, 'index']);
    Route::post('result', [ResultController::class, 'store']);
    Route::get('result/{id}/details', [ResultController::class, 'show']);
    Route::post('result/{id}', [ResultController::class, 'update']);
    Route::put('result/{id}', [ResultController::class, 'update']);
    Route::delete('result/{id}', [ResultController::class, 'destroy']);

    Route::post('result-published', [ResultController::class, 'published']);
    Route::get('result-sync/{id}', [ResultController::class, 'syncResult']);
    Route::get('result-sync-subject/{id}', [ResultController::class, 'syncSubject']);
    Route::get('result-marksheet-data/{id}', [ResultController::class, 'marksheet']);
    Route::get('download-marksheet/{id}', [ResultController::class, 'downloadMarksheet'])->name('result.downloadMarksheet');
    Route::get('marksheet-all-data/{id}', [ResultController::class, 'marksheetAllData'])->name('result.marksheetAllData');
    Route::get('marksheet-all-download/{id}', [ResultController::class, 'marksheetAll'])->name('result.marksheetAllDownload');
    Route::get('download-bulk-marksheet', [ResultController::class, 'downloadBulkMarksheet'])->name('result.downloadBulkMarksheet');
    Route::get('bulk-marksheet-status/{id}', [ResultController::class, 'bulkMarksheetStatus'])->name('result.bulkMarksheetStatus');
    Route::get('download-bulk-marksheet-file/{id}', [ResultController::class, 'downloadBulkMarksheetFile'])->name('result.downloadBulkMarksheetFile');
    Route::get('students-for-marks-entry', [ResultController::class, 'studentsForMarksEntry']);
    Route::get('result-report/list', [ResultController::class, 'report']);
    Route::get('subjectwise-result-data', [ResultController::class, 'subjectwiseResultData']);
    Route::get('tabulation-sheet-data', [ResultController::class, 'tabulationSheetData']);
    Route::get('download-tabulation-sheet', [ResultController::class, 'exportTabulationSheet'])->name('result.exportTabulationSheet');

    Route::view('student/create', 'layouts.backend_app')->name('student.create');
    Route::view('student-import', 'layouts.backend_app')->name('student.import');
    Route::view('studentPromotion/create', 'layouts.backend_app')->name('studentPromotion.create');
    Route::view('idcard', 'layouts.backend_app')->name('idcard.index');
    Route::view('studentMigrationRollVerify', 'layouts.backend_app')->name('studentMigrationRollVerify.index');
    Route::view('studentMigrationRollVerify/create', 'layouts.backend_app')->name('studentMigrationRollVerify.create');
    Route::view('studentMigrationRollVerify/{id}', 'layouts.backend_app')->name('studentMigrationRollVerify.show');
    Route::view('studentMigration', 'layouts.backend_app')->name('studentMigration.index');
    Route::view('registrationNoVerify', 'layouts.backend_app')->name('registrationNoVerify.index');
    Route::view('registrationNoVerify/create', 'layouts.backend_app')->name('registrationNoVerify.create');
    Route::view('registrationNoVerify/{id}', 'layouts.backend_app')->name('registrationNoVerify.show');
    Route::view('certificateApplication', 'layouts.backend_app')->name('certificateApplication.index');
    Route::view('certificateApplication/{id}', 'layouts.backend_app')->name('certificateApplication.show');
    Route::view('certificateApplication/{id}/edit', 'layouts.backend_app')->name('certificateApplication.edit');
    Route::view('certificateTemplate', 'layouts.backend_app')->name('certificateTemplate.index');
    Route::view('certificateTemplate/create', 'layouts.backend_app')->name('certificateTemplate.create');
    Route::view('certificateTemplate/{id}', 'layouts.backend_app')->name('certificateTemplate.show');
    Route::view('certificateTemplate/{id}/edit', 'layouts.backend_app')->name('certificateTemplate.edit');

    Route::view('onlineAdmission', 'layouts.backend_app')->name('onlineAdmission.index');
    Route::view('onlineAdmission-rejected', 'layouts.backend_app')->name('onlineAdmission.rejectedList');
    Route::view('onlineAdmission/{id}/edit', 'layouts.backend_app')->name('onlineAdmission.edit');
    Route::view('onlineAdmissionRollVerify', 'layouts.backend_app')->name('onlineAdmissionRollVerify.index');
    Route::view('onlineAdmissionRollVerify/create', 'layouts.backend_app')->name('onlineAdmissionRollVerify.create');
    Route::view('onlineAdmissionRollVerify/{id}', 'layouts.backend_app')->name('onlineAdmissionRollVerify.show');

    Route::view('attendance', 'layouts.backend_app')->name('attendance.index');
    Route::view('attendance/create', 'layouts.backend_app')->name('attendance.create');
    Route::view('attendance/{id}/edit', 'layouts.backend_app')->name('attendance.edit');

    Route::view('attendance-report', 'layouts.backend_app')->name('attendance.attendanceReport');
    Route::view('attendance-sheet', 'layouts.backend_app')->name('attendance.attendanceSheet');
    Route::view('attendanceSummary', 'layouts.backend_app')->name('attendanceSummary.index');
    Route::view('attendanceSummary/create', 'layouts.backend_app')->name('attendanceSummary.create');
    Route::view('attendanceSummary/{id}', 'layouts.backend_app')->name('attendanceSummary.show');
    Route::view('attendanceSummary/{id}/edit', 'layouts.backend_app')->name('attendanceSummary.edit');

    Route::view('admitCard', 'layouts.backend_app')->name('admitCard.index');
    Route::view('admitCard/create', 'layouts.backend_app')->name('admitCard.create');
    Route::view('admitCard/{id}', 'layouts.backend_app')->name('admitCard.show');
    Route::view('admitCard/{id}/edit', 'layouts.backend_app')->name('admitCard.edit');

    Route::view('classTestResult', 'layouts.backend_app')->name('classTestResult.index');
    Route::view('classTestResult/create', 'layouts.backend_app')->name('classTestResult.create');
    Route::view('classTestResult/{id}/edit', 'layouts.backend_app')->name('classTestResult.edit');
    Route::view('classTestResult/{id}', 'layouts.backend_app')->name('classTestResult.show');
    Route::view('classTestResult-marksheet/{id}', 'layouts.backend_app')->name('classTestResult.marksheet');

    Route::view('result', 'layouts.backend_app')->name('result.index');
    Route::view('result/create', 'layouts.backend_app')->name('result.create');
    Route::view('result/{id}/edit', 'layouts.backend_app')->name('result.edit');
    Route::view('result/{id}', 'layouts.backend_app')->name('result.show');
    Route::view('result-marksheet/{id}', 'layouts.backend_app')->name('result.marksheet');
    Route::view('marksheet-all/{id}', 'layouts.backend_app')->name('result.marksheetAll');
    Route::view('result-report', 'layouts.backend_app')->name('result.result');
    Route::view('subjectwise-result', 'layouts.backend_app')->name('result.subjectwiseResult');
    Route::view('tabulation-sheet', 'layouts.backend_app')->name('result.tabulationSheet');
    Route::view('tabulation-sheet-ct', 'layouts.backend_app')->name('result.tabulationSheetCt');
    Route::view('tabulation-sheet-v2', 'layouts.backend_app')->name('result.tabulationSheetV2');
    Route::view('result-grade-summary', 'layouts.backend_app')->name('result.gradeSummary');

    Route::view('exam-attendance-sheet', 'layouts.backend_app')->name('exam.attendance.sheet');

    Route::view('student/{id}/edit', 'layouts.backend_app')->name('student.edit');
    Route::view('student/{id}', 'layouts.backend_app')->name('student.show');
});
