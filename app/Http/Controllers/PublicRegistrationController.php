<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Traits\Lib\CustomDataTrait;
use App\Traits\Lib\DynamicDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PublicRegistrationController extends Controller
{
    use DynamicDataTrait, CustomDataTrait;

    public function systems(Request $request)
    {
        $data = [];

        $dynamic = $this->dynamicData();
        $custom = $this->customData();

        $data['academic_classes'] = $dynamic['academic_classes'] ?? [];
        $data['academic_sessions'] = $dynamic['academic_sessions'] ?? [];
        $data['academic_qualifications'] = $dynamic['academic_qualifications'] ?? [];
        $data['departments'] = $dynamic['departments'] ?? [];
        $data['department_qualidactions'] = $dynamic['department_qualidactions'] ?? [];

        $data['student_types'] = $custom['student_types'] ?? [];
        $data['religions'] = $custom['religions'] ?? [];
        $data['blood_groups'] = $custom['blood_groups'] ?? [];

        $data['divisions'] = [];
        if (Schema::hasTable('divisions')) {
            $data['divisions'] = DB::table('divisions')
                ->select('id', 'name')
                ->orderBy('id')
                ->get()
                ->toArray();
        }

        $data['districts'] = [];
        if (Schema::hasTable('districts')) {
            $select = ['id', 'name'];
            if (Schema::hasColumn('districts', 'division_id')) {
                $select[] = 'division_id';
            }
            $data['districts'] = DB::table('districts')
                ->select($select)
                ->orderBy('id')
                ->get()
                ->toArray();
        }

        $data['upazilas'] = [];
        if (Schema::hasTable('upazilas')) {
            $select = ['id', 'name'];
            if (Schema::hasColumn('upazilas', 'district_id')) {
                $select[] = 'district_id';
            }
            $data['upazilas'] = DB::table('upazilas')
                ->select($select)
                ->orderBy('id')
                ->get()
                ->toArray();
        }

        $data['unions'] = [];
        if (Schema::hasTable('unions')) {
            $select = ['id', 'name'];
            if (Schema::hasColumn('unions', 'upazilla_id')) {
                $select[] = 'upazilla_id';
            } elseif (Schema::hasColumn('unions', 'upazila_id')) {
                $select[] = 'upazila_id';
            }
            $data['unions'] = DB::table('unions')
                ->select($select)
                ->orderBy('id')
                ->get()
                ->toArray();
        }

        return response()->json(['global' => $data]);
    }

    protected function generateStudentId(): string
    {
        $last = Student::query()
            ->whereNotNull('student_id')
            ->where('student_id', 'like', 'STD-%')
            ->orderBy('id', 'desc')
            ->value('student_id');

        $lastNum = 0;
        if (is_string($last) && preg_match('/^STD-(\d+)$/', $last, $m)) {
            $lastNum = (int) ($m[1] ?? 0);
        }

        $next = $lastNum + 1;
        return 'STD-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    protected function departmentRequired(?string $qualificationId): bool
    {
        if (!$qualificationId) {
            return true;
        }

        if (!Schema::hasTable('academic_qualifications')) {
            return true;
        }

        $q = DB::table('academic_qualifications')->select('name')->where('id', $qualificationId)->first();
        $name = strtolower(trim((string) ($q->name ?? '')));
        if ($name === 'hsc' || $name === 'degree') {
            return false;
        }

        return true;
    }

    public function register(Request $request)
    {
        $deptRequired = $this->departmentRequired($request->input('academic_qualification_id'));

        $rules = [
            'academic_class_id' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => $deptRequired ? ['required'] : ['nullable'],
            'student_type' => ['required', Rule::in($this->customData()['student_types'] ?? [])],
            'name' => ['required'],
            'password' => ['required'],
            'mobile' => ['required', 'digits:11', 'regex:/^01[0-9]+$/', Rule::unique('students', 'mobile')],
            'email' => ['nullable', 'email', Rule::unique('students', 'email')],
            'reg_no' => ['nullable'],
            'college_roll' => ['nullable'],
            'admission_id' => ['nullable'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Others'])],
            'blood_group' => ['nullable'],
            'living_type' => ['nullable', Rule::in(['Hostel', 'Others'])],
            'address' => ['required'],
            'permanent_address' => ['nullable'],
            'profile' => ['required', 'image'],
        ];

        if (Schema::hasTable('students')) {
            $locationFields = [
                'division_id',
                'district_id',
                'upazila_id',
                'union_id',
                'permanent_division_id',
                'permanent_district_id',
                'permanent_upazila_id',
                'permanent_union_id',
            ];

            foreach ($locationFields as $field) {
                if (Schema::hasColumn('students', $field)) {
                    $rules[$field] = ['nullable'];
                }
            }
        }

        $request->validate($rules);

        $qualificationId = $request->input('academic_qualification_id');
        $regNo = (string) ($request->input('reg_no') ?? '');
        if ($regNo !== '') {
            $exists = Student::query()
                ->where('academic_qualification_id', $qualificationId)
                ->where('reg_no', $regNo)
                ->exists();
            if ($exists) {
                return response()->json(['message' => 'Already Registered This Reg. No'], 422);
            }

            if (Schema::hasTable('registration_no_verifies') && Schema::hasColumn('registration_no_verifies', 'registration_no_lists')) {
                $where = [
                    'academic_session_id' => $request->input('academic_session_id'),
                    'academic_qualification_id' => $request->input('academic_qualification_id'),
                    'academic_class_id' => $request->input('academic_class_id'),
                ];
                if ($deptRequired) {
                    $where['department_id'] = $request->input('department_id');
                }

                $row = DB::table('registration_no_verifies')->where($where)->orderByDesc('id')->first();
                if ($row && property_exists($row, 'registration_no_lists')) {
                    $raw = $row->registration_no_lists;
                    $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
                    $list = is_array($decoded) ? array_values($decoded) : [];

                    $set = [];
                    foreach ($list as $v) {
                        $set[(string) $v] = true;
                    }

                    if (!isset($set[$regNo])) {
                        return response()->json(['message' => 'Invalid Registration No'], 422);
                    }
                }
            }
        }

        $collegeRoll = (string) ($request->input('college_roll') ?? '');
        if ($collegeRoll !== '') {
            $exists = Student::query()
                ->where('academic_qualification_id', $qualificationId)
                ->where('college_roll', $collegeRoll)
                ->exists();
            if ($exists) {
                return response()->json(['message' => 'Already Registered This College Roll'], 422);
            }
        }

        $fillable = (new Student())->getFillable();
        $fillable = array_values(array_diff($fillable, ['otp']));

        if (Schema::hasTable('students')) {
            $fillable = array_values(array_filter($fillable, fn ($c) => Schema::hasColumn('students', $c)));
        }
        $data = $request->only($fillable);

        $data['student_id'] = $this->generateStudentId();
        $data['status'] = $data['status'] ?? 'active';
        $data['gender'] = $data['gender'] ?? 'Male';
        $data['living_type'] = $data['living_type'] ?? 'Others';

        $data['name'] = strtoupper($request->input('name', ''));
        $data['fathers_name'] = strtoupper($request->input('fathers_name', ''));
        $data['mothers_name'] = strtoupper($request->input('mothers_name', ''));
        $data['address'] = strtoupper($request->input('address', ''));
        $data['permanent_address'] = strtoupper($request->input('permanent_address', ''));

        $file = $request->file('profile');
        if ($file && $file->isValid()) {
            $path = $file->store('upload/student/student-profile', 'public');
            $data['profile'] = preg_replace('/^upload\//', '', $path);
        }

        $student = Student::create($data);

        return response()->json([
            'created' => true,
            'id' => $student->id,
            'student' => $student,
        ], 200);
    }
}
