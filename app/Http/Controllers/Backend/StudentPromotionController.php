<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentPromotionController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'student_type' => ['required'],
        ]);

        $students = DB::table('students')
            ->select(
                'id',
                'name',
                'student_id',
                'admission_id',
                'college_roll',
                'reg_no',
                'student_type'
            )
            ->where('academic_session_id', $request->input('academic_session_id'))
            ->where('academic_qualification_id', $request->input('academic_qualification_id'))
            ->where('department_id', $request->input('department_id'))
            ->where('academic_class_id', $request->input('academic_class_id'))
            ->where('student_type', $request->input('student_type'))
            ->where('status', 'active')
            ->orderByRaw('CAST(college_roll as UNSIGNED) asc')
            ->orderBy('college_roll', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'students' => ['required', 'array'],
        ]);

        $newSessionId = $request->input('academic_session_id');
        $newQualificationId = $request->input('academic_qualification_id');
        $newDepartmentId = $request->input('department_id');
        $newClassId = $request->input('academic_class_id');

        $students = $request->input('students') ?? [];
        $irregularIds = $request->input('irregularId') ?? [];

        DB::beginTransaction();
        try {
            if (is_array($irregularIds) && count($irregularIds) > 0) {
                DB::table('students')
                    ->whereIn('id', array_values(array_filter($irregularIds)))
                    ->update(['student_type' => 'Irregular']);
            }

            foreach ($students as $std) {
                $id = (int) ($std['id'] ?? 0);
                if ($id <= 0) {
                    continue;
                }

                $student = DB::table('students')->where('id', $id)->first();
                if (!$student) {
                    continue;
                }

                $oldStudent = (array) $student;

                $update = [
                    'reg_no' => $std['new_reg_no'] ?? null,
                    'admission_id' => $std['new_admission_id'] ?? null,
                    'student_type' => $std['student_type'] ?? ($oldStudent['student_type'] ?? null),
                    'department_id' => $newDepartmentId,
                    'academic_class_id' => $newClassId,
                    'academic_session_id' => $newSessionId,
                    'academic_qualification_id' => $newQualificationId,
                    'updated_at' => now(),
                ];

                DB::table('students')->where('id', $id)->update($update);

                $this->copySubjectAssign($oldStudent, (int) $newClassId);

                $newStudent = DB::table('students')->where('id', $id)->first();

                if (Schema::hasTable('student_promotions')) {
                    DB::table('student_promotions')->insert([
                        'student_id' => $id,
                        'old_json' => json_encode($oldStudent),
                        'new_json' => json_encode($newStudent),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Promoted Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to promote students',
                'exception' => $e->getMessage(),
            ], 422);
        }
    }

    protected function copySubjectAssign(array $oldStudent, int $newClassId): void
    {
        $studentId = (int) ($oldStudent['id'] ?? 0);
        $departmentId = $oldStudent['department_id'] ?? null;
        $oldClassId = $oldStudent['academic_class_id'] ?? null;

        if ($studentId <= 0 || empty($departmentId) || empty($oldClassId)) {
            return;
        }

        $assignments = DB::table('student_subject_assigns')
            ->select('department_id', 'academic_class_id', 'student_id', 'subject_id', 'main_subject')
            ->where([
                'student_id' => $studentId,
                'department_id' => $departmentId,
                'academic_class_id' => $oldClassId,
            ])
            ->get();

        foreach ($assignments as $a) {
            $row = [
                'department_id' => $a->department_id,
                'academic_class_id' => $newClassId,
                'student_id' => $a->student_id,
                'subject_id' => $a->subject_id,
                'main_subject' => $a->main_subject,
            ];

            $exists = DB::table('student_subject_assigns')->where($row)->exists();
            if ($exists) {
                continue;
            }

            DB::table('student_subject_assigns')->insert($row + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
