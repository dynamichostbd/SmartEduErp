<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Traits\Lib\CustomDataTrait;
use App\Traits\Lib\DynamicDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PublicStudentProfileController extends Controller
{
    use DynamicDataTrait, CustomDataTrait;

    public function systems(Request $request)
    {
        return response()->json([
            'global' => array_merge($this->dynamicData(), $this->customData()),
        ]);
    }

    public function update(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reg_no' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('students', 'email')->ignore((int) $student->id)],
            'mobile' => ['nullable', 'digits:11', 'regex:/^01[0-9]+$/', Rule::unique('students', 'mobile')->ignore((int) $student->id)],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Others'])],
            'ssc_gpa' => ['nullable', 'numeric'],
            'dob' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'nid' => ['nullable', 'string', 'max:100'],
            'fathers_name' => ['nullable', 'string', 'max:255'],
            'mothers_name' => ['nullable', 'string', 'max:255'],
            'passing_year' => ['nullable', 'integer'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'extra_curricular_activity' => ['nullable', 'string', 'max:100'],
            'quota' => ['nullable', Rule::in(['Yes', 'No'])],
            'marital_status' => ['nullable', Rule::in(['Married', 'Unmarried'])],
            'address' => ['nullable', 'string', 'max:1000'],
            'permanent_address' => ['nullable', 'string', 'max:2000'],
            'living_type' => ['nullable', Rule::in(['Hostel', 'Others'])],
            'hostel_id' => ['nullable'],
            'hostel_room_no' => ['nullable', 'string', 'max:100'],
            'guardian_type' => ['nullable', Rule::in(['Father', 'Mother', 'Other'])],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_mobile' => ['nullable', 'string', 'max:30'],
            'guardian_relations' => ['nullable', 'string', 'max:50'],
            'profile' => ['nullable', 'image', 'max:5120'],
        ]);

        $fillable = (new Student())->getFillable();
        $allowed = [
            'name',
            'reg_no',
            'email',
            'mobile',
            'gender',
            'ssc_gpa',
            'dob',
            'religion',
            'blood_group',
            'nid',
            'fathers_name',
            'mothers_name',
            'passing_year',
            'nationality',
            'extra_curricular_activity',
            'quota',
            'marital_status',
            'address',
            'permanent_address',
            'living_type',
            'hostel_id',
            'hostel_room_no',
            'guardian_type',
            'guardian_name',
            'guardian_mobile',
            'guardian_relations',
        ];

        $allowed = array_values(array_intersect($allowed, $fillable));
        $payload = $request->only($allowed);

        foreach (['name', 'fathers_name', 'mothers_name', 'address', 'nationality'] as $k) {
            if (array_key_exists($k, $payload)) {
                $payload[$k] = strtoupper((string) ($payload[$k] ?? ''));
            }
        }

        if (array_key_exists('hostel_id', $payload)) {
            $hostelId = $payload['hostel_id'];
            if ($hostelId === '' || $hostelId === null) {
                $payload['hostel_id'] = null;
            } else {
                $payload['hostel_id'] = (int) $hostelId;
            }
        }

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            if ($file && $file->isValid()) {
                $path = $file->store('upload/student/student-profile', 'public');
                $payload['profile'] = preg_replace('/^upload\//', '', $path);
            }
        }

        $model = Student::query()->find((int) $student->id);
        if (!$model) {
            return response()->json(['message' => 'Student not found.'], 404);
        }

        $model->fill($payload);
        $model->save();

        return response()->json([
            'updated' => true,
            'student' => $model->fresh(),
        ], 200);
    }
}
