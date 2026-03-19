<?php

namespace App\Http\Controllers;

use App\Models\AdmitCard;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicStudentAdmitCardController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!Schema::hasTable('admit_cards') || !Schema::hasTable('students')) {
            return response()->json(['admit_cards' => []], 200);
        }

        $studentId = (string) ($student->student_id ?? '');
        if ($studentId === '') {
            return response()->json(['admit_cards' => []], 200);
        }

        if (!Schema::hasTable('attendance_summaries') || !Schema::hasTable('attendance_summary_details') || !Schema::hasTable('exams')) {
            return response()->json(['admit_cards' => []], 200);
        }

        $rows = DB::table('admit_cards as ac')
            ->leftJoin('exams', 'exams.id', '=', 'ac.exam_id')
            ->leftJoin('attendance_summaries as asu', 'asu.admit_card_id', '=', 'ac.id')
            ->leftJoin('attendance_summary_details as asud', 'asud.attendance_summarie_id', '=', 'asu.id')
            ->select([
                'ac.id',
                'ac.name',
                'ac.issue_date',
                'ac.expired_date',
                'exams.name as exam_name',
                'asu.present_percent',
                'asud.student_id',
                'asud.present_percentage as student_percent',
                'asud.status',
            ])
            ->where('asud.student_id', $studentId)
            ->whereNull('ac.deleted_at')
            ->whereDate('ac.issue_date', '<=', date('Y-m-d'))
            ->whereDate('ac.expired_date', '>=', date('Y-m-d'))
            ->where([
                'ac.academic_session_id' => $student->academic_session_id,
                'ac.academic_qualification_id' => $student->academic_qualification_id,
                'ac.department_id' => $student->department_id,
                'ac.academic_class_id' => $student->academic_class_id,
            ])
            ->orderByDesc('ac.id')
            ->get();

        $out = $rows
            ->map(function ($r) {
                return [
                    'id' => (int) ($r->id ?? 0),
                    'name' => $r->name ?? '',
                    'issue_date' => $r->issue_date ?? null,
                    'expired_date' => $r->expired_date ?? null,
                    'exam_name' => $r->exam_name ?? '',
                    'present_percent' => $r->present_percent ?? null,
                    'student_percent' => $r->student_percent ?? null,
                    'status' => $r->status ?? null,
                ];
            })
            ->values()
            ->all();

        return response()->json(['admit_cards' => $out], 200);
    }

    public function download(Request $request, int $id)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!Schema::hasTable('admit_cards') || !Schema::hasTable('students')) {
            return response()->json(['message' => 'Module not ready'], 422);
        }

        $studentId = (string) ($student->student_id ?? '');
        if ($studentId === '') {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $admitCard = AdmitCard::query()->find((int) $id);
        if (!$admitCard) {
            return response()->json(['message' => 'Admit card not found'], 404);
        }

        if (!Schema::hasTable('attendance_summaries') || !Schema::hasTable('attendance_summary_details') || !Schema::hasTable('exams')) {
            return response()->json(['message' => 'Eligibility data not ready'], 422);
        }

        $eligibility = DB::table('admit_cards as ac')
            ->leftJoin('attendance_summaries as asu', 'asu.admit_card_id', '=', 'ac.id')
            ->leftJoin('attendance_summary_details as asud', 'asud.attendance_summarie_id', '=', 'asu.id')
            ->select(
                'asu.present_percent',
                'asud.present_percentage as student_percent',
                'asud.status'
            )
            ->where('ac.id', (int) $admitCard->id)
            ->where('asud.student_id', $studentId)
            ->first();

        if (!$eligibility) {
            return response()->json(['message' => 'Admit card not found'], 404);
        }

        if (($eligibility->status ?? null) === 'A' && (int) ($eligibility->student_percent ?? 0) < (int) ($eligibility->present_percent ?? 0)) {
            return response()->json(['message' => 'Insufficient attendance'], 422);
        }

        $studentModel = Student::query()->where('student_id', $studentId)->first();
        if (!$studentModel) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $pdf = $this->generatePdf((object) array_merge((array) $admitCard->toArray(), [
            'exam_name' => (string) (optional(DB::table('exams')->select('name')->where('id', (int) ($admitCard->exam_id ?? 0))->first())->name ?? ''),
            'present_percent' => $eligibility->present_percent,
            'student_percent' => $eligibility->student_percent,
            'status' => $eligibility->status,
        ]), $studentModel);

        $examName = Str::slug((string) (optional(DB::table('exams')->select('name')->where('id', (int) ($admitCard->exam_id ?? 0))->first())->name ?? 'exam'));
        $fileName = 'admit-card-' . Str::slug((string) ($studentModel->name ?? 'student')) . '-' . $examName . '.pdf';

        return $pdf->download($fileName);
    }

    private function generatePdf(object $admitCardData, Student $student)
    {
        $admitCard = new AdmitCard();
        foreach ($admitCardData as $key => $value) {
            if ($key !== 'exam_name' && $key !== 'present_percent' && $key !== 'student_percent' && $key !== 'status') {
                $admitCard->$key = $value;
            }
        }

        $studentContext = $student;
        $studentContext->academic_session = (object) [
            'name' => optional(DB::table('academic_sessions')->select('name')->where('id', $student->academic_session_id)->first())->name,
        ];
        $studentContext->qualification = (object) [
            'name' => optional(DB::table('academic_qualifications')->select('name')->where('id', $student->academic_qualification_id)->first())->name,
        ];
        $studentContext->department = (object) [
            'name' => optional(DB::table('departments')->select('name')->where('id', $student->department_id)->first())->name,
        ];
        $studentContext->academic_class = (object) [
            'name' => optional(DB::table('academic_classes')->select('name')->where('id', $student->academic_class_id)->first())->name,
        ];

        $subjects = [];
        if (Schema::hasTable('subject_assigns') && Schema::hasTable('subject_assign_details') && Schema::hasTable('subjects')) {
            $subjectAssign = DB::table('subject_assigns')->where([
                'academic_qualification_id' => $admitCard->academic_qualification_id,
                'department_id' => $admitCard->department_id,
                'academic_class_id' => $admitCard->academic_class_id,
            ])->first();

            if ($subjectAssign) {
                $subjects = DB::table('subject_assign_details as d')
                    ->join('subjects as s', 's.id', '=', 'd.subject_id')
                    ->where('d.subject_assign_id', $subjectAssign->id)
                    ->where('s.is_child', 0)
                    ->where(['d.fourth_subject' => 0, 'd.main_subject' => 0])
                    ->select('s.name_en')
                    ->get()
                    ->map(function ($r) {
                        return ['subject' => ['name_en' => $r->name_en]];
                    })
                    ->all();
            }
        }

        $main_subjects = collect([]);
        $fourth_subjects = collect([]);
        if (Schema::hasTable('student_subject_assigns') && Schema::hasTable('subjects')) {
            $main_subjects = DB::table('student_subject_assigns as a')
                ->join('subjects as s', 's.id', '=', 'a.subject_id')
                ->where('a.student_id', $student->id)
                ->where('s.is_child', 0)
                ->where('a.main_subject', 1)
                ->select('s.name_en')
                ->get()
                ->map(function ($r) {
                    return (object) ['subject' => (object) ['name_en' => $r->name_en]];
                });

            $fourth_subjects = DB::table('student_subject_assigns as a')
                ->join('subjects as s', 's.id', '=', 'a.subject_id')
                ->where('a.student_id', $student->id)
                ->where('s.is_child', 0)
                ->where('a.main_subject', 0)
                ->select('s.name_en')
                ->get()
                ->map(function ($r) {
                    return (object) ['subject' => (object) ['name_en' => $r->name_en]];
                });
        }

        $config = app()->make('siteSettingObj');
        $admit = $admitCard;

        return Pdf::loadView('pdf.admit_card', [
            'admit' => $admit,
            'admitCard' => $admit,
            'student' => $studentContext,
            'subjects' => $subjects,
            'main_subjects' => $main_subjects,
            'fourth_subjects' => $fourth_subjects,
            'config' => $config,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 96,
                'isPhpEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }
}
