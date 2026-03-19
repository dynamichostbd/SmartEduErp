<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Backend\Result\ResultController as BackendResultController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicStudentResultController extends Controller
{
    public function search(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'academic_session_id' => ['required'],
            'department_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
        ]);

        if (!Schema::hasTable('exams')) {
            return response()->json(['error' => 'Exam not found'], 422);
        }

        $exam = DB::table('exams')->where('id', (int) $request->input('exam_id'))->first();
        if (!$exam) {
            return response()->json(['error' => 'Exam not found'], 422);
        }

        $cond = [
            'academic_session_id' => (int) $request->input('academic_session_id'),
            'department_id' => (int) $request->input('department_id'),
            'academic_qualification_id' => (int) $request->input('academic_qualification_id'),
            'academic_class_id' => (int) $request->input('academic_class_id'),
            'exam_id' => (int) $request->input('exam_id'),
        ];

        $examType = (string) ($exam->exam_type ?? '');

        if ($examType === 'ct') {
            return $this->searchClassTest($cond, (int) ($student->id ?? 0));
        }

        return $this->searchTerm($cond, (int) ($student->id ?? 0), $examType ?: 'term');
    }

    private function searchTerm(array $cond, int $studentId, string $examType)
    {
        if (!Schema::hasTable('results') || !Schema::hasTable('result_details')) {
            return response()->json(['error' => 'Result not found'], 200);
        }

        $result = DB::table('results')->where($cond)->first();
        if (!$result) {
            return response()->json(['error' => 'Result not found'], 200);
        }

        if (Schema::hasColumn('results', 'status')) {
            if ((string) ($result->status ?? '') !== 'published') {
                return response()->json(['error' => 'Result not published'], 200);
            }
        }

        if (Schema::hasColumn('results', 'published_date')) {
            $pub = $result->published_date ?? null;
            if (!empty($pub) && strtotime((string) $pub) > strtotime(date('Y-m-d'))) {
                return response()->json(['error' => 'Result not published'], 200);
            }
        }

        $detailId = (int) DB::table('result_details')
            ->where('result_id', (int) ($result->id ?? 0))
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->value('id');

        if ($detailId <= 0) {
            return response()->json(['error' => 'Result not found'], 200);
        }

        $payload = app(BackendResultController::class)->marksheet($detailId, 'view')->getData(true);
        if (!is_array($payload) || empty($payload['id'] ?? null)) {
            return response()->json(['error' => 'Result not found'], 200);
        }

        return response()->json([
            'result' => $payload,
            'exam_type' => $examType,
        ], 200);
    }

    private function searchClassTest(array $cond, int $studentId)
    {
        if (!Schema::hasTable('class_test_results') || !Schema::hasTable('class_test_result_details')) {
            return response()->json(['error' => 'Result not found'], 200);
        }

        $result = DB::table('class_test_results')->where($cond)->first();
        if (!$result) {
            return response()->json(['error' => 'Result not found'], 200);
        }

        if (Schema::hasColumn('class_test_results', 'status')) {
            if ((string) ($result->status ?? '') !== 'published') {
                return response()->json(['error' => 'Result not published'], 200);
            }
        }

        if (Schema::hasColumn('class_test_results', 'published_date')) {
            $pub = $result->published_date ?? null;
            if (!empty($pub) && strtotime((string) $pub) > strtotime(date('Y-m-d'))) {
                return response()->json(['error' => 'Result not published'], 200);
            }
        }

        $detail = DB::table('class_test_result_details as d')
            ->join('students as std', 'std.id', '=', 'd.student_id')
            ->select([
                'd.id',
                'd.class_test_result_id',
                'd.student_id',
                'd.total_mark',
                'd.result_status',
                'std.student_id as software_id',
                'std.name as student_name',
                'std.mobile as student_mobile',
                'std.college_roll as student_college_roll',
            ])
            ->where('d.class_test_result_id', (int) ($result->id ?? 0))
            ->where('d.student_id', $studentId)
            ->first();

        if (!$detail) {
            return response()->json(['error' => 'Result not found'], 200);
        }

        $marks = [];
        if (Schema::hasTable('class_test_result_marks') && Schema::hasTable('subjects')) {
            $marks = DB::table('class_test_result_marks as m')
                ->join('subjects as sub', 'sub.id', '=', 'm.subject_id')
                ->select([
                    'm.subject_id',
                    'm.mark',
                    'm.exam_mark',
                    'm.pass_mark',
                    'm.result_status',
                    'sub.name_en as subject_name_en',
                ])
                ->where('m.class_test_result_details_id', (int) ($detail->id ?? 0))
                ->orderBy('m.id')
                ->get()
                ->map(function ($m) {
                    return [
                        'subject_id' => $m->subject_id,
                        'mark' => $m->mark,
                        'exam_mark' => $m->exam_mark,
                        'pass_mark' => $m->pass_mark,
                        'result_status' => $m->result_status,
                        'subject' => ['id' => $m->subject_id, 'name_en' => $m->subject_name_en],
                    ];
                })
                ->values()
                ->all();
        }

        return response()->json([
            'result' => [
                'id' => (int) ($detail->id ?? 0),
                'student' => [
                    'id' => (int) ($detail->student_id ?? 0),
                    'student_id' => $detail->software_id ?? '',
                    'name' => $detail->student_name ?? '',
                    'mobile' => $detail->student_mobile ?? '',
                    'college_roll' => $detail->student_college_roll ?? '',
                ],
                'marks' => $marks,
                'class_test_result' => [
                    'id' => (int) ($result->id ?? 0),
                    'academic_session_id' => $result->academic_session_id ?? null,
                    'department_id' => $result->department_id ?? null,
                    'academic_qualification_id' => $result->academic_qualification_id ?? null,
                    'academic_class_id' => $result->academic_class_id ?? null,
                    'exam_id' => $result->exam_id ?? null,
                ],
            ],
            'exam_type' => 'ct',
        ], 200);
    }

    public function downloadMarksheet(Request $request, $id)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            abort(401);
        }

        if (!Schema::hasTable('result_details') || !Schema::hasTable('results')) {
            abort(422, 'Result module not ready');
        }

        $detailRow = DB::table('result_details')->where('id', (int) $id)->where('student_id', (int) ($student->id ?? 0))->first();
        if (!$detailRow) {
            abort(404);
        }

        $resultRow = DB::table('results')->where('id', (int) ($detailRow->result_id ?? 0))->first();
        if (!$resultRow) {
            abort(404);
        }

        if (Schema::hasColumn('results', 'status')) {
            if ((string) ($resultRow->status ?? '') !== 'published') {
                abort(404);
            }
        }

        if (Schema::hasColumn('results', 'published_date')) {
            $pub = $resultRow->published_date ?? null;
            if (!empty($pub) && strtotime((string) $pub) > strtotime(date('Y-m-d'))) {
                abort(404);
            }
        }

        $data = app(BackendResultController::class)->marksheet((int) $id, 'pdf')->getData(true);
        if (!is_array($data) || empty($data['id'] ?? null)) {
            abort(404);
        }

        $config = app()->make('siteSettingObj');

        $pdf = Pdf::loadView('pdf.result_marksheet', ['data' => $data, 'config' => $config, 'bgImage' => null])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        if ($request->boolean('view')) {
            return $pdf->stream('marksheet.pdf');
        }

        return $pdf->download('marksheet.pdf');
    }
}
