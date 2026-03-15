<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdmitCard;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class AdmitCardController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('admit_cards')) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'to' => null,
                    'per_page' => 10,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        if ($request->boolean('allData')) {
            return AdmitCard::query()
                ->select('id', 'academic_session_id', 'academic_qualification_id', 'department_id', 'academic_class_id', 'expired_date', 'name')
                ->whereDate('expired_date', '>=', date('Y-m-d'))
                ->orderByDesc('id')
                ->get();
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = DB::table('admit_cards as ac')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'ac.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'ac.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'ac.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'ac.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'ac.exam_id')
            ->select([
                'ac.id',
                'ac.academic_session_id',
                'ac.academic_qualification_id',
                'ac.department_id',
                'ac.academic_class_id',
                'ac.exam_id',
                'ac.name',
                'ac.issue_date',
                'ac.expired_date',
                'ac.created_at',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
                'ex.name as exam_name',
            ])
            ->whereNull('ac.deleted_at')
            ->orderByDesc('ac.id');

        if ($request->filled('academic_session_id')) {
            $query->where('ac.academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('academic_qualification_id')) {
            $query->where('ac.academic_qualification_id', $request->input('academic_qualification_id'));
        }
        if ($request->filled('department_id')) {
            $query->where('ac.department_id', $request->input('department_id'));
        }
        if ($request->filled('academic_class_id')) {
            $query->where('ac.academic_class_id', $request->input('academic_class_id'));
        }

        $field = (string) ($request->input('field_name') ?? '');
        $value = (string) ($request->input('value') ?? '');
        if ($field !== '' && $field !== '0' && $value !== '') {
            if ($field === 'name') {
                $query->where('ac.name', 'like', '%' . $value . '%');
            }
        }

        $datas = $query->paginate($perPage);

        return response()->json([
            'data' => $datas->items(),
            'meta' => [
                'current_page' => $datas->currentPage(),
                'from' => $datas->firstItem(),
                'to' => $datas->lastItem(),
                'per_page' => $datas->perPage(),
                'total' => $datas->total(),
                'last_page' => $datas->lastPage(),
            ],
        ]);
    }

    public function show($id)
    {
        if (!Schema::hasTable('admit_cards')) {
            return response()->json(['message' => 'Admit Card module not ready'], 422);
        }

        $row = DB::table('admit_cards as ac')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'ac.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'ac.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'ac.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'ac.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'ac.exam_id')
            ->select([
                'ac.*',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
                'ex.name as exam_name',
            ])
            ->whereNull('ac.deleted_at')
            ->where('ac.id', $id)
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(['admit_card' => $row]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['nullable'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
            'name' => ['required'],
            'issue_date' => ['required'],
            'expired_date' => ['required'],
        ]);

        if (!Schema::hasTable('admit_cards')) {
            return response()->json(['message' => 'Admit Card module not ready'], 422);
        }

        $exists = DB::table('admit_cards')->where([
            'academic_session_id' => $request->input('academic_session_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'department_id' => $request->input('department_id'),
            'academic_class_id' => $request->input('academic_class_id'),
            'name' => $request->input('name'),
        ])->whereNull('deleted_at')->first();

        if (!empty($exists)) {
            return response()->json(['error' => 'Already create this admit card'], 200);
        }

        $admin = Auth::guard('admin')->user();
        $ip = $request->ip();

        $id = DB::table('admit_cards')->insertGetId([
            'academic_session_id' => $request->input('academic_session_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'department_id' => $request->input('department_id'),
            'academic_class_id' => $request->input('academic_class_id'),
            'exam_id' => $request->input('exam_id'),
            'name' => $request->input('name'),
            'issue_date' => date('Y-m-d', strtotime((string) $request->input('issue_date'))),
            'expired_date' => date('Y-m-d', strtotime((string) $request->input('expired_date'))),
            'created_by' => $admin->name ?? null,
            'created_ip' => $ip,
            'updated_by' => $admin->name ?? null,
            'updated_ip' => $ip,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['nullable'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
            'name' => ['required'],
            'issue_date' => ['required'],
            'expired_date' => ['required'],
        ]);

        if (!Schema::hasTable('admit_cards')) {
            return response()->json(['message' => 'Admit Card module not ready'], 422);
        }

        $row = DB::table('admit_cards')->whereNull('deleted_at')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $admin = Auth::guard('admin')->user();
        $ip = $request->ip();

        DB::table('admit_cards')->where('id', $id)->update([
            'academic_session_id' => $request->input('academic_session_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'department_id' => $request->input('department_id'),
            'academic_class_id' => $request->input('academic_class_id'),
            'exam_id' => $request->input('exam_id'),
            'name' => $request->input('name'),
            'issue_date' => date('Y-m-d', strtotime((string) $request->input('issue_date'))),
            'expired_date' => date('Y-m-d', strtotime((string) $request->input('expired_date'))),
            'updated_by' => $admin->name ?? null,
            'updated_ip' => $ip,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('admit_cards')) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $row = DB::table('admit_cards')->whereNull('deleted_at')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        DB::table('admit_cards')->where('id', $id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function getStudentAdmitCard($studentId)
    {
        if (!Schema::hasTable('admit_cards') || !Schema::hasTable('students')) {
            return response()->json(['success' => false, 'message' => 'Module not ready'], 422);
        }

        $student = Student::where('student_id', $studentId)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        if (!Schema::hasTable('attendance_summaries') || !Schema::hasTable('attendance_summary_details') || !Schema::hasTable('exams')) {
            return response()->json([
                'success' => true,
                'admitCards' => [],
            ]);
        }

        $query = DB::table('admit_cards as ac')
            ->leftJoin('exams', 'exams.id', '=', 'ac.exam_id')
            ->leftJoin('attendance_summaries as asu', 'asu.admit_card_id', '=', 'ac.id')
            ->leftJoin('attendance_summary_details as asud', 'asud.attendance_summarie_id', '=', 'asu.id')
            ->select([
                'ac.id',
                'ac.name',
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
            ]);

        $admitCards = $query->get();

        return response()->json([
            'success' => true,
            'admitCards' => $admitCards,
        ]);
    }

    public function downloadAllAdmitCards(Request $request)
    {
        ini_set('memory_limit', '3G');

        $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['required'],
            'academic_session_id' => ['required'],
            'academic_class_id' => ['required'],
        ]);

        if (!Schema::hasTable('admit_cards') || !Schema::hasTable('students')) {
            return response()->json(['success' => false, 'message' => 'Module not ready'], 422);
        }

        $studentIds = $request->input('student_ids') ?? [];
        $academicSessionId = $request->input('academic_session_id');
        $academicClassId = $request->input('academic_class_id');

        $chunkSize = 10;
        $addedFiles = 0;

        $zip = new ZipArchive();
        $zipFileName = 'admit-cards-' . time() . '.zip';
        $zipPath = storage_path('app/public/temp/' . $zipFileName);

        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            Log::error("Failed to create zip file at: {$zipPath}");
            return response()->json(['success' => false, 'message' => 'Could not create zip file'], 500);
        }

        $hasAdmit = DB::table('admit_cards')
            ->where('academic_class_id', $academicClassId)
            ->where('academic_session_id', $academicSessionId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$hasAdmit) {
            $zip->close();
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'No admit cards found for the given academic session/class.',
            ], 404);
        }

        if (!Schema::hasTable('attendance_summaries') || !Schema::hasTable('attendance_summary_details') || !Schema::hasTable('exams')) {
            $zip->close();
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Attendance summary/admit card eligibility data not ready.',
            ], 404);
        }

        Student::whereIn('student_id', $studentIds)
            ->chunk($chunkSize, function ($students) use ($zip, $academicSessionId, $academicClassId, &$addedFiles) {
                foreach ($students as $student) {
                    $admitCards = DB::table('admit_cards as ac')
                        ->leftJoin('exams', 'exams.id', '=', 'ac.exam_id')
                        ->leftJoin('attendance_summaries as asu', 'asu.admit_card_id', '=', 'ac.id')
                        ->leftJoin('attendance_summary_details as asud', 'asud.attendance_summarie_id', '=', 'asu.id')
                        ->select(
                            'ac.*',
                            'exams.name as exam_name',
                            'asu.present_percent',
                            'asud.student_id',
                            'asud.present_percentage as student_percent',
                            'asud.status'
                        )
                        ->where('asud.student_id', $student->student_id)
                        ->whereNull('ac.deleted_at')
                        ->whereDate('ac.issue_date', '<=', date('Y-m-d'))
                        ->whereDate('ac.expired_date', '>=', date('Y-m-d'))
                        ->where([
                            'ac.academic_session_id' => $academicSessionId,
                            'ac.academic_class_id' => $academicClassId,
                            'ac.academic_qualification_id' => $student->academic_qualification_id,
                            'ac.department_id' => $student->department_id,
                        ])
                        ->get();

                    if ($admitCards->isEmpty()) {
                        continue;
                    }

                    foreach ($admitCards as $admitCardData) {
                        if (
                            $admitCardData->status === 'A' &&
                            (int) $admitCardData->student_percent < (int) $admitCardData->present_percent
                        ) {
                            continue;
                        }

                        try {
                            $pdf = $this->generateAdmitCardPdfOptimized($admitCardData, $student);

                            $studentName = Str::slug($student->name);
                            $examName = Str::slug($admitCardData->exam_name ?? 'exam');
                            $fileName = "admit-card-{$studentName}-{$examName}.pdf";

                            $zip->addFromString($fileName, $pdf->output());
                            $addedFiles++;
                            unset($pdf);
                        } catch (\Throwable $e) {
                            Log::error('Admit card pdf generation failed: ' . $e->getMessage());
                            continue;
                        }
                    }

                    unset($admitCards);
                    gc_collect_cycles();
                }
            });

        $zip->close();

        if ($addedFiles === 0) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'No eligible admit cards found for download.',
            ], 404);
        }

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function downloadAdmitCard($id, $stdID)
    {
        if (!Schema::hasTable('admit_cards') || !Schema::hasTable('students')) {
            return redirect()->back()->with('error', 'Module not ready');
        }

        $admitCard = AdmitCard::find($id);
        if (!$admitCard) {
            return redirect()->back()->with('error', 'Admit card not found');
        }

        $student = Student::where('student_id', $stdID)->first();
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found');
        }

        if (!Schema::hasTable('attendance_summaries') || !Schema::hasTable('attendance_summary_details') || !Schema::hasTable('exams')) {
            return redirect()->back()->with('error', 'Eligibility data not ready');
        }

        $eligibility = DB::table('admit_cards as ac')
            ->leftJoin('attendance_summaries as asu', 'asu.admit_card_id', '=', 'ac.id')
            ->leftJoin('attendance_summary_details as asud', 'asud.attendance_summarie_id', '=', 'asu.id')
            ->select(
                'asu.present_percent',
                'asud.present_percentage as student_percent',
                'asud.status'
            )
            ->where('ac.id', $admitCard->id)
            ->where('asud.student_id', $student->student_id)
            ->first();

        if (!$eligibility) {
            return redirect()->back()->with('error', 'Admit card not found');
        }

        if ($eligibility->status === 'A') {
            return redirect()->back()->with('error', 'Insufficient attendance');
        }

        if ($eligibility->status === 'A' && (int) $eligibility->student_percent < (int) $eligibility->present_percent) {
            return redirect()->back()->with('error', 'Insufficient attendance percentage');
        }

        $pdf = $this->generateAdmitCardPdfOptimized((object) array_merge((array) $admitCard->toArray(), [
            'exam_name' => optional(DB::table('exams')->select('name')->where('id', $admitCard->exam_id)->first())->name,
            'present_percent' => $eligibility->present_percent,
            'student_percent' => $eligibility->student_percent,
            'status' => $eligibility->status,
        ]), $student);

        $examName = Str::slug(optional(DB::table('exams')->select('name')->where('id', $admitCard->exam_id)->first())->name ?? 'exam');
        $fileName = "admit-card-{$student->name}-{$examName}.pdf";

        return $pdf->download($fileName);
    }

    private function generateAdmitCardPdfOptimized($admitCardData, $student)
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
