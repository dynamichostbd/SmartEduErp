<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Student;
use App\Traits\ImageUpload;
use App\Traits\ResizeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{

    use ImageUpload;

    protected $resizeArr = [
        ["width" => 300, "height" => 300],
    ];
    protected $resize_type = "force";

    /**
     * Get Logged Student Info
     *
     * @return \Illuminate\Http\Response
     */
    public function info()
    {
        $data = Student::with(
            'academic_class',
            'academic_session',
            'qualification',
            'department',
            'hostel'
        )->find(auth()->user()->id);
        return $this->sendResponse($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Student $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $student = Student::find(auth()->user()->id);
        
        $validator = clone $this->validateCheck($request->all(), $student->id);
        if ($validator->fails()) {
             return response()->json([
                 'success' => false,
                 'message' => 'Validation Error',
                 'errors'  => $validator->errors()
             ], 422);
        }

        $file = $request->file('profile');

        $arr = [];

        // Fields that need uppercase
        $upperFields = ['name', 'fathers_name', 'mothers_name', 'address', 'permanent_address'];
        foreach ($upperFields as $field) {
            if ($request->has($field)) {
                $arr[$field] = strtoupper($request->$field);
            }
        }

        // Other fields
        $normalFields = [
            'gender', 'readmission_college_roll', 'admission_id', 'college_roll',
            'email', 'reg_no', 'ssc_gpa', 'hostel_id', 'hostel_room_no',
            'blood_group', 'nid', 'dob', 'religion', 'guardian_type',
            'guardian_name', 'guardian_mobile', 'guardian_relations', 'marital_status'
        ];
        
        foreach ($normalFields as $field) {
            if ($request->has($field)) {
                $arr[$field] = $request->$field;
            }
        }

        $collegeName = env('COLLEGE_NAME', 'default_college');
        $path = $collegeName . "/student/student-profile";

        if (!empty($file)) {
            $this->getOriginalPath($student->profile);
            $imagePath      = $this->upload($file, $path);
            $arr['profile'] = $imagePath;
        }

        if (count($arr) > 0) {
            $student->update($arr);
        }

        return $this->sendResponse([], 200, 'Information Update Successfully!');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function notices()
    {
        $query = Notice::latest('id')->limit(15);
        $query->whereSub('assignables', 'department_id', auth()->user()->department_id);
        $query->whereSub('assignables', 'academic_qualification_id', auth()->user()->academic_qualification_id);
        $query->whereSub('assignables', 'academic_class_id', auth()->user()->academic_class_id);
        $query->orWhere('all_dept', 1)->where('type', 'student');

        $data = $query->get()->toArray();

        return $this->sendResponse($data);
    }

    /**
     * Change Password
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function passwordChange(Request $request)
    {
        $authID   = auth()->user()->id;
        $old_pass = $request->old_password;
        if (!Hash::check($old_pass, auth()->user()->password)) {
            return $this->sendError("Sorry!! Old password doesn't match our records", 422);
        }

        $request->validate([
            'new_password'     => 'required|min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:6',
        ]);
        $password = Hash::make($request->new_password);
        Student::where('id', $authID)->update(['password' => $password]);
        return $this->sendResponse([], 200, "Password change successfully!!");
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateCheck($data, $id = null)
    {
        return Validator::make($data, [
            "name"         => "sometimes|required",
            "admission_id" => "sometimes|required",
            "email"        => "nullable",
            "address"      => "sometimes|required",
            "profile"      => "nullable|image|mimes:jpg,png,jpeg|mimetypes:image/jpeg,image/png",
        ]);
    }
}


