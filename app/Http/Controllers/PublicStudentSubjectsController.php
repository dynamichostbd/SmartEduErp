<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicStudentSubjectsController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!Schema::hasTable('subject_assigns') || !Schema::hasTable('subject_assign_details') || !Schema::hasTable('subjects')) {
            return response()->json([
                'subjectAssign' => null,
                'assign_subjects' => [],
            ], 200);
        }

        $conditions = [
            'academic_qualification_id' => (int) ($student->academic_qualification_id ?? 0),
            'department_id' => (int) ($student->department_id ?? 0),
            'academic_class_id' => (int) ($student->academic_class_id ?? 0),
        ];

        $subjectAssign = DB::table('subject_assigns')->where($conditions)->first();

        $details = [];
        if ($subjectAssign) {
            $details = DB::table('subject_assign_details as d')
                ->join('subjects as s', 's.id', '=', 'd.subject_id')
                ->where('d.subject_assign_id', (int) $subjectAssign->id)
                ->where(function ($q) {
                    $q->where('s.is_child', 0)->orWhereNull('s.is_child');
                })
                ->select([
                    'd.id',
                    'd.subject_assign_id',
                    'd.subject_id',
                    'd.except_subject_id',
                    'd.fourth_subject',
                    'd.main_subject',
                    'd.ct_mark',
                    'd.ct_pass_mark',
                    'd.cq_mark',
                    'd.cq_pass_mark',
                    'd.mcq_mark',
                    'd.mcq_pass_mark',
                    'd.practical_mark',
                    'd.practical_pass_mark',
                    'd.total_mark',
                    'd.sorting',
                    's.name_en as subject_name',
                    's.is_child',
                    's.parent_id',
                ])
                ->orderBy('d.sorting')
                ->orderBy('d.id')
                ->get();
        }

        $assignSubjects = [];
        if (Schema::hasTable('student_subject_assigns')) {
            $assignSubjects = DB::table('student_subject_assigns as a')
                ->join('subjects as s', 's.id', '=', 'a.subject_id')
                ->where([
                    'a.department_id' => (int) ($student->department_id ?? 0),
                    'a.academic_class_id' => (int) ($student->academic_class_id ?? 0),
                    'a.student_id' => (int) ($student->id ?? 0),
                ])
                ->where(function ($q) {
                    $q->where('s.is_child', 0)->orWhereNull('s.is_child');
                })
                ->select([
                    'a.id',
                    'a.subject_id',
                    'a.main_subject',
                    's.name_en as subject_name',
                ])
                ->orderBy('a.id')
                ->get();
        }

        return response()->json([
            'subjectAssign' => $subjectAssign
                ? [
                    'id' => (int) ($subjectAssign->id ?? 0),
                    'academic_qualification_id' => $subjectAssign->academic_qualification_id,
                    'department_id' => $subjectAssign->department_id,
                    'academic_class_id' => $subjectAssign->academic_class_id,
                    'main_subject' => $subjectAssign->main_subject,
                    'note' => $subjectAssign->note,
                    'details' => $details,
                ]
                : null,
            'assign_subjects' => $assignSubjects,
        ], 200);
    }

    public function store(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $inputs = $request->all();
        if (!is_array($inputs) || empty($inputs)) {
            return response()->json(['message' => 'Invalid data'], 422);
        }

        if (!Schema::hasTable('student_subject_assigns')) {
            return response()->json(['message' => 'Subject module not ready'], 422);
        }

        DB::beginTransaction();
        try {
            DB::table('student_subject_assigns')
                ->where([
                    'department_id' => (int) ($student->department_id ?? 0),
                    'academic_class_id' => (int) ($student->academic_class_id ?? 0),
                    'student_id' => (int) ($student->id ?? 0),
                ])
                ->delete();

            $rows = [];
            foreach ($inputs as $subject) {
                if (!is_array($subject)) {
                    continue;
                }
                $sid = $subject['subject_id'] ?? null;
                if (empty($sid)) {
                    continue;
                }

                $rows[] = [
                    'department_id' => (int) ($student->department_id ?? 0),
                    'academic_class_id' => (int) ($student->academic_class_id ?? 0),
                    'student_id' => (int) ($student->id ?? 0),
                    'subject_id' => (int) $sid,
                    'main_subject' => !empty($subject['main_subject']) ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($rows)) {
                DB::rollBack();
                return response()->json(['message' => 'Please select subject'], 422);
            }

            DB::table('student_subject_assigns')->insert($rows);

            DB::commit();
            return response()->json(['message' => 'Subject Assign Successfully'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to assign subjects', 'exception' => $e->getMessage()], 422);
        }
    }
}
