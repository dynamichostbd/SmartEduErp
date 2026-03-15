<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubjectAssignController extends Controller
{
    public function index($id)
    {
        $student = DB::table('students')->select('id', 'academic_qualification_id', 'department_id', 'academic_class_id')->where('id', $id)->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $subjectAssign = DB::table('subject_assigns')
            ->where([
                'academic_qualification_id' => $student->academic_qualification_id,
                'department_id' => $student->department_id,
                'academic_class_id' => $student->academic_class_id,
            ])
            ->first();

        $details = [];
        if ($subjectAssign) {
            $details = DB::table('subject_assign_details as d')
                ->join('subjects as s', 's.id', '=', 'd.subject_id')
                ->where('d.subject_assign_id', $subjectAssign->id)
                ->where('s.is_child', 0)
                ->select(
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
                    's.parent_id'
                )
                ->orderBy('d.sorting')
                ->get();
        }

        $assignSubjects = DB::table('student_subject_assigns as a')
            ->join('subjects as s', 's.id', '=', 'a.subject_id')
            ->where([
                'a.department_id' => $student->department_id,
                'a.academic_class_id' => $student->academic_class_id,
                'a.student_id' => $student->id,
            ])
            ->where('s.is_child', 0)
            ->select('a.id', 'a.subject_id', 'a.main_subject', 's.name_en as subject_name')
            ->get();

        return response()->json([
            'subjectAssign' => $subjectAssign
                ? [
                    'id' => $subjectAssign->id,
                    'academic_qualification_id' => $subjectAssign->academic_qualification_id,
                    'department_id' => $subjectAssign->department_id,
                    'academic_class_id' => $subjectAssign->academic_class_id,
                    'main_subject' => $subjectAssign->main_subject,
                    'note' => $subjectAssign->note,
                    'details' => $details,
                ]
                : null,
            'assign_subjects' => $assignSubjects,
        ]);
    }

    public function subjectLists(Request $request)
    {
        $departmentId = $request->input('department_id') ?: (Auth::guard('admin')->user()->department_id ?? null);

        $subjectAssign = DB::table('subject_assigns')
            ->where([
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'department_id' => $departmentId,
                'academic_class_id' => $request->input('academic_class_id'),
            ])
            ->first();

        if (!$subjectAssign) {
            return response()->json([]);
        }

        $details = DB::table('subject_assign_details as d')
            ->join('subjects as s', 's.id', '=', 'd.subject_id')
            ->where('d.subject_assign_id', $subjectAssign->id)
            ->where('s.is_child', 0)
            ->select(
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
                's.parent_id'
            )
            ->orderBy('d.sorting')
            ->get();

        return response()->json($details);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer',
            'data' => 'required|array',
        ]);

        $student = DB::table('students')->select('id', 'department_id', 'academic_class_id')->where('id', $request->input('student_id'))->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        DB::beginTransaction();
        try {
            DB::table('student_subject_assigns')
                ->where([
                    'department_id' => $student->department_id,
                    'academic_class_id' => $student->academic_class_id,
                    'student_id' => $student->id,
                ])
                ->delete();

            $rows = [];
            foreach (($request->input('data') ?? []) as $subject) {
                if (empty($subject['subject_id'])) {
                    continue;
                }
                $rows[] = [
                    'department_id' => $student->department_id,
                    'academic_class_id' => $student->academic_class_id,
                    'student_id' => $student->id,
                    'subject_id' => (int) $subject['subject_id'],
                    'main_subject' => !empty($subject['main_subject']) ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('student_subject_assigns')->insert($rows);
            }

            DB::commit();
            return response()->json(['message' => 'Subject Assign Successfully'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to assign subjects', 'exception' => $e->getMessage()], 422);
        }
    }
}
