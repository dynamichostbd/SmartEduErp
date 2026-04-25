<?php

namespace App\Http\Controllers\api\Auth;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Base\BaseController;
use App\Models\Student;
use App\Traits\ResizeTrait;
use App\Traits\SMSTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentRegisterController extends BaseController
{

    use SMSTrait, ResizeTrait;

    protected $resizeArr = [
        ["width" => 300, "height" => 300],
    ];
    protected $resize_type = "force";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registration(Request $request)
    {
        $vd = $this->validateCheck($request->all());
        if ($vd->fails()) {
            return $this->sendError("Validation errors", 401, $vd->errors());
        }

        try {
            $data = $request->all();
            $file = $request->file('profile');

            $data['student_id'] = GlobalHelper::generate_id(Student::class, 'student_id', [
                'pad_length' => 5,
                'prefix'     => "STD-",
            ]);
            $data['status']       = 'active';
            $data['name']         = strtoupper($request->name);
            $data['fathers_name'] = strtoupper($request->fathers_name);
            $data['mothers_name'] = strtoupper($request->mothers_name);
            $data['address']      = strtoupper($request->address);

            if (!empty($file)) {
                $imagePath       = $this->resizer($file, ['student_profile']);
                $data['profile'] = $imagePath['resize0'] ?? null;
            }

            $std = Student::create($data);

            return $this->sendResponse([], 200, "Registration Successfully");
        } catch (\Throwable $th) {
            return $this->sendError("Exceptions errors", 500, $th->getMessage());
        }
    }

    /**
     * Validation check====
     */
    public function validateCheck($data)
    {
        return Validator::make($data, [
            "department_id"             => "required",
            "academic_class_id"         => "required",
            "academic_session_id"       => "required",
            "academic_qualification_id" => "required",
            "student_type"              => "required",
            "name"                      => "required",
            "mobile"                    => "required|unique:students",
            "admission_id"              => "required|unique:students",
            "email"                     => "nullable|unique:students",
            "password"                  => "required",
            "address"                   => "required",
            "profile"                   => "required|image|mimes:jpg,png,jpeg|mimetypes:image/jpeg,image/png",
        ]);
    }
}


