<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\AcademicQualification;
use App\Models\Admin;
use App\Models\AccountHead;
use App\Models\Department;
use App\Models\District;
use App\Models\Division;
use App\Models\Exam;
use App\Models\Hostel;
use App\Models\MobileAppVersion;
use App\Models\Subject;
use App\Models\SubjectAssign;
use App\Models\System\SiteSetting;
use App\Models\Union;
use App\Models\Upazila;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LibController extends Controller
{

    public function index()
    {
        $exams           = Exam::select('name', 'id')->where('exam_type', '!=', 'others')->get();
        $site_settings           = SiteSetting::all();
        $qualifications  = AcademicQualification::active()->latest('id')->select('name', 'id')->get();
        $account_heads   = AccountHead::active()->where('type', 'college')->latest('id')->select('name', 'id')->get();
        $admission_heads = AccountHead::active()->whereIn('type', ['admission', 'certificate'])->latest('id')->select('name', 'id')->get();
        $hostel_heads    = AccountHead::active()->where('type', 'hostel')->latest('id')->select('name', 'id')->get();
        $departments     = Department::active()->select('name', 'id')->oldest('name')->get();
        $hostels         = Hostel::active()->select('name', 'id')->get();
        $academic_levels = AcademicQualification::active()
            ->select('id', 'name', 'application_fees', 'admission_roll_verify', 'application_fee', 'subject_choose', 'admission_files', 'registration', 'commitment','online_admission')
            ->with('department_qualifications')
            ->oldest('name')
            ->get();

        $divisions = Division::select('id', 'name', 'bn_name')->orderBy('name')->get();
        $districts = District::select('id', 'division_id', 'name', 'bn_name')->orderBy('name')->get();
        $upazilas  = Upazila::select('id', 'district_id', 'name', 'bn_name')->orderBy('name')->get();
        $unions    = Union::select('id', 'upazilla_id', 'name', 'bn_name')->orderBy('name')->get();

        $data = [];
        $data += App::make("globalData");
        $data += [
            'academic_levels' => !empty($academic_levels) ? $academic_levels->toArray() : [],
            'hostels'         => !empty($hostels) ? $hostels->toArray() : [],
            'departments'     => !empty($departments) ? $departments->toArray() : [],
            'exams'           => !empty($exams) ? $exams->toArray() : [],
            'site_settings'   => !empty($site_settings) ? $site_settings->toArray() : [],
        ];

        $data['qualifications']  = $qualifications;
        $data['account_heads']   = $account_heads;
        $data['admission_heads'] = $admission_heads;
        $data['hostel_heads']    = $hostel_heads;
        $data['student_types']   = $this->student_types;
        $data['religions']       = $this->religions;
        $data['blood_groups']    = $this->blood_groups;
        $data['divisions']       = $divisions;
        $data['districts']       = $districts;
        $data['upazilas']        = $upazilas;
        $data['unions']          = $unions;

        return $this->sendResponse($data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function subjects(Request $request)
    {
        $subjectAssign = SubjectAssign::where([
            'academic_qualification_id' => $request->academic_qualification_id,
            'department_id'             => $request->department_id,
            'academic_class_id'         => $request->academic_class_id,
        ])->first();

        $subjectIDs = !empty($subjectAssign) ? $subjectAssign->details()->pluck('subject_id') : [];

        $subjects = Subject::select('id', 'name_en', 'name_bn')->whereIn('id', $subjectIDs)->get();

        return response()->json($subjects);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function subjectsAssigns(Request $request)
    {
        $subjectAssign = SubjectAssign::with([
            'details:subject_assign_id,subject_id,except_subject_id,fourth_subject,main_subject',
            'details.subject:id,name_en,name_bn'
        ])->where([
            'academic_qualification_id' => $request->academic_qualification_id,
            'department_id'             => $request->department_id,
            'academic_class_id'         => $request->academic_class_id,
        ])->first();

        return response()->json($subjectAssign);
    }

    /**
     * Mobile app version
     *
     * @return \Illuminate\Http\Response
     */
    public function mobileAppVersion(Request $request)
    {
        $data = MobileAppVersion::select('android', 'ios')->first();
        return response()->json($data);
    }

    // student
    protected $student_types = [
        ['name' => 'Regular'],
        ['name' => 'Irregular'],
    ];
    protected $religions = [
        ['name' => 'Islam'],
        ['name' => 'Hinduism'],
        ['name' => 'Chirstian'],
        ['name' => 'Buddhist'],
        ['name' => 'Others'],
    ];
    protected $blood_groups = [
        ['name' => 'A-'],
        ['name' => 'A+'],
        ['name' => 'B-'],
        ['name' => 'B+'],
        ['name' => 'O-'],
        ['name' => 'O+'],
        ['name' => 'AB-'],
        ['name' => 'AB+'],
    ];

    public function teachersAndStaff()
    {
        try {
            $data = Admin::with('department', 'teacher.designation')
                ->whereIn('type', ['Teacher', 'Staff'])
                ->where('status', 'active')
                ->get();
            return $this->sendResponse($data);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}


