<?php

namespace App\Http\Controllers\Backend\Result;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TabulationSheetExport;
use App\Jobs\GenerateBulkMarksheetJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ResultController extends Controller
{
    private function fetchRemoteImageAsBase64(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $imageContext = stream_context_create([
            'http' => ['timeout' => 15, 'ignore_errors' => true],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        try {
            $data = @file_get_contents($url, false, $imageContext);
            if (!$data) {
                return null;
            }

            $type = strtolower(pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg');
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('results')) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'to' => null,
                    'per_page' => (int) ($request->input('pagination') ?? 10),
                    'total' => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = DB::table('results as r')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->select([
                'r.id',
                'r.academic_session_id',
                'r.department_id',
                'r.academic_qualification_id',
                'r.academic_class_id',
                'r.exam_id',
                'r.total_exam_subjects',
                'r.published_date',
                'r.status',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
                'ex.name as exam_name',
            ])
            ->orderByDesc('r.id');

        if ($request->filled('academic_class_id')) {
            $query->where('r.academic_class_id', $request->input('academic_class_id'));
        }
        if ($request->filled('academic_session_id')) {
            $query->where('r.academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('academic_qualification_id')) {
            $query->where('r.academic_qualification_id', $request->input('academic_qualification_id'));
        }
        if ($request->filled('department_id')) {
            $query->where('r.department_id', $request->input('department_id'));
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

    public function downloadMarksheet(Request $request, $id)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $data = $this->marksheet($id, 'pdf')->getData(true);
        if (!is_array($data) || empty($data['id'] ?? null)) {
            abort(404);
        }

        $config = app()->make('siteSettingObj');

        $bgImage = null;
        if (is_array($config)) {
            $bgImage = $this->fetchRemoteImageAsBase64($config['marksheet_image'] ?? null);
        }

        $pdf = Pdf::loadView('pdf.result_marksheet', ['data' => $data, 'config' => $config, 'bgImage' => $bgImage])
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

    public function marksheetAll(Request $request, $id)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        if (!Schema::hasTable('result_details') || !Schema::hasTable('result_marks') || !Schema::hasTable('results')) {
            abort(422, 'Result module not ready');
        }

        $result = DB::table('results')->where('id', $id)->first();
        if (!$result) {
            abort(404);
        }

        $details = DB::table('result_details')->where('result_id', $id)->orderBy('id')->pluck('id')->map(fn($v) => (int) $v)->values()->all();
        $items = [];
        foreach ($details as $detailId) {
            $payload = $this->marksheet($detailId, 'pdf')->getData(true);
            if (is_array($payload) && !empty($payload['id'] ?? null)) {
                $items[] = $payload;
            }
        }

        $config = app()->make('siteSettingObj');

        $bgImage = null;
        if (is_array($config)) {
            $bgImage = $this->fetchRemoteImageAsBase64($config['marksheet_image'] ?? null);
        }

        $pdf = Pdf::loadView('pdf.result_marksheet_bulk', ['items' => $items, 'config' => $config, 'bgImage' => $bgImage])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        if ($request->boolean('view')) {
            return $pdf->stream('all-marksheet.pdf');
        }

        return $pdf->download('all-marksheet.pdf');
    }

    public function marksheetAllData($id)
    {
        if (!Schema::hasTable('result_details') || !Schema::hasTable('result_marks') || !Schema::hasTable('results')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $result = DB::table('results as r')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->select([
                'r.*',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
                'ex.name as exam_name',
            ])
            ->where('r.id', (int) $id)
            ->first();
        if (!$result) {
            return response()->json(['message' => 'Result not found'], 404);
        }

        $detailRows = DB::table('result_details as d')
            ->join('students as std', 'std.id', '=', 'd.student_id')
            ->select([
                'd.id as detail_id',
                'd.student_id',
                'd.total_mark_without_additional',
                'd.gpa_without_additional',
                'd.total_mark',
                'd.gpa',
                'd.letter_grade',
                'd.result_status',
                'd.merit_position_in_department',
                'd.merit_position_in_class',
                'std.student_id as software_id',
                'std.name as student_name',
                'std.mobile as student_mobile',
                'std.college_roll as student_college_roll',
                'std.fathers_name as student_fathers_name',
                'std.mothers_name as student_mothers_name',
                'std.reg_no as student_reg_no',
                'std.student_type as student_type',
            ])
            ->where('d.result_id', (int) $id)
            ->orderBy('std.college_roll')
            ->orderBy('d.id')
            ->get();

        $detailIds = $detailRows->pluck('detail_id')->map(fn($v) => (int) $v)->values()->all();
        if (empty($detailIds)) {
            return response()->json([
                'result_id' => (int) $id,
                'items' => [],
            ]);
        }

        $assignDetailsBySubjectId = [];
        if (Schema::hasTable('subject_assigns') && Schema::hasTable('subject_assign_details')) {
            $subjectAssign = DB::table('subject_assigns')
                ->where('academic_qualification_id', $result->academic_qualification_id ?? null)
                ->where('department_id', $result->department_id ?? null)
                ->where('academic_class_id', $result->academic_class_id ?? null)
                ->first();

            if ($subjectAssign) {
                $assignDetailsBySubjectId = DB::table('subject_assign_details')
                    ->where('subject_assign_id', $subjectAssign->id)
                    ->get()
                    ->keyBy(function ($r) {
                        return (int) $r->subject_id;
                    })
                    ->all();
            }
        }

        $fourthSubjectByStudentId = [];
        if (Schema::hasTable('student_subject_assigns')) {
            $studentIds = $detailRows->pluck('student_id')->map(fn($v) => (int) $v)->values()->all();
            if (!empty($studentIds)) {
                $fourthSubjectByStudentId = DB::table('student_subject_assigns')
                    ->whereIn('student_id', $studentIds)
                    ->where('department_id', $result->department_id ?? null)
                    ->where('academic_class_id', $result->academic_class_id ?? null)
                    ->where('main_subject', 0)
                    ->pluck('subject_id', 'student_id')
                    ->map(fn($v) => (int) $v)
                    ->all();
            }
        }

        $markRows = [];
        if (Schema::hasTable('subjects')) {
            $markRows = DB::table('result_marks as m')
                ->join('subjects as sub', 'sub.id', '=', 'm.subject_id')
                ->leftJoin('subjects as psub', 'psub.id', '=', 'sub.parent_id')
                ->select([
                    'm.result_details_id',
                    'm.subject_id',
                    'm.ct_mark',
                    'm.cq_mark',
                    'm.mcq_mark',
                    'm.practical_mark',
                    'm.obtained_mark',
                    'm.total_mark',
                    'm.gpa',
                    'm.letter_grade',
                    'm.additional_subject',
                    'm.is_absent',
                    'm.sorting',
                    'sub.name_en as subject_name_en',
                    'sub.is_child as subject_is_child',
                    'sub.parent_id as subject_parent_id',
                    'psub.name_en as parent_subject_name_en',
                ])
                ->whereIn('m.result_details_id', $detailIds)
                ->orderBy('m.sorting')
                ->orderBy('m.id')
                ->get();
        }

        $marksByDetailId = [];
        foreach ($markRows as $r) {
            $did = (int) $r->result_details_id;
            if (!isset($marksByDetailId[$did])) {
                $marksByDetailId[$did] = [];
            }
            $marksByDetailId[$did][] = [
                'subject_id' => $r->subject_id,
                'ct_mark' => $r->ct_mark,
                'cq_mark' => $r->cq_mark,
                'mcq_mark' => $r->mcq_mark,
                'practical_mark' => $r->practical_mark,
                'obtained_mark' => $r->obtained_mark,
                'total_mark' => $r->total_mark,
                'gpa' => $r->gpa,
                'letter_grade' => $r->letter_grade,
                'additional_subject' => $r->additional_subject,
                'is_absent' => $r->is_absent,
                'sorting' => $r->sorting,
                'subject' => [
                    'id' => $r->subject_id,
                    'name_en' => $r->subject_name_en,
                    'is_child' => $r->subject_is_child,
                    'parent_id' => $r->subject_parent_id,
                    'parent_name_en' => $r->parent_subject_name_en,
                ],
            ];
        }

        $hasCtMark = empty($assignDetailsBySubjectId);

        $items = [];
        foreach ($detailRows as $d) {
            $did = (int) $d->detail_id;
            $studentId = (int) $d->student_id;

            $marks = $marksByDetailId[$did] ?? [];
            $fourthSubjectId = $fourthSubjectByStudentId[$studentId] ?? null;
            $marks = $this->normalizeMarksheetMarks($marks, $assignDetailsBySubjectId, $fourthSubjectId, $hasCtMark, 'view');

            $items[] = [
                'id' => $did,
                'student' => [
                    'id' => $studentId,
                    'student_id' => $d->software_id,
                    'name' => $d->student_name,
                    'mobile' => $d->student_mobile,
                    'college_roll' => $d->student_college_roll,
                    'fathers_name' => $d->student_fathers_name,
                    'mothers_name' => $d->student_mothers_name,
                    'reg_no' => $d->student_reg_no,
                    'student_type' => $d->student_type,
                ],
                'result_details' => [
                    'total_mark_without_additional' => $d->total_mark_without_additional,
                    'total_mark' => $d->total_mark,
                    'gpa_without_additional' => $d->gpa_without_additional,
                    'gpa' => $d->gpa,
                    'letter_grade' => $d->letter_grade,
                    'result_status' => $d->result_status,
                    'merit_position_in_department' => $d->merit_position_in_department,
                    'merit_position_in_class' => $d->merit_position_in_class,
                ],
                'marks' => $marks,
                'result' => [
                    'academic_session' => ['id' => $result->academic_session_id ?? null, 'name' => $result->academic_session_name ?? ''],
                    'qualification' => ['id' => $result->academic_qualification_id ?? null, 'name' => $result->academic_qualification_name ?? ''],
                    'department' => ['id' => $result->department_id ?? null, 'name' => $result->department_name ?? ''],
                    'academic_class' => ['id' => $result->academic_class_id ?? null, 'name' => $result->academic_class_name ?? ''],
                    'exam' => ['id' => $result->exam_id ?? null, 'name' => $result->exam_name ?? ''],
                    'total_exam_subjects' => $result->total_exam_subjects ?? null,
                    'published_date' => $result->published_date ?? null,
                    'has_ct_mark' => $hasCtMark,
                ],
            ];
        }

        return response()->json([
            'result_id' => (int) $id,
            'items' => $items,
        ]);
    }

    private function normalizeMarksheetMarks(array $marks, array $assignDetailsBySubjectId, ?int $fourthSubjectId, bool &$hasCtMark, string $mode = 'pdf'): array
    {
        $bySubjectId = [];
        foreach ($marks as $m) {
            $bySubjectId[(int) ($m['subject_id'] ?? 0)] = $m;
        }

        foreach ($marks as $idx => $m) {
            $sub = (array) ($m['subject'] ?? []);
            $subjectId = (int) ($m['subject_id'] ?? 0);
            $parentId = (int) ($sub['parent_id'] ?? 0);

            $finalAssign = $assignDetailsBySubjectId[$subjectId] ?? null;
            if ($parentId > 0) {
                $childAssign = $assignDetailsBySubjectId[$subjectId] ?? null;
                if ($childAssign && (int) ($childAssign->total_mark ?? 0) > 0) {
                    $finalAssign = $childAssign;
                } else {
                    $finalAssign = $assignDetailsBySubjectId[$parentId] ?? $finalAssign;
                }
            }

            $m['ct_allocated'] = true;
            $m['cq_allocated'] = true;
            $m['mcq_allocated'] = true;
            $m['practical_allocated'] = true;

            if ($finalAssign) {
                if (((int) ($finalAssign->ct_mark ?? 0)) === 0) {
                    $m['ct_mark'] = null;
                    $m['ct_allocated'] = false;
                } else {
                    $hasCtMark = true;
                }
                if (((int) ($finalAssign->cq_mark ?? 0)) === 0) {
                    $m['cq_mark'] = null;
                    $m['cq_allocated'] = false;
                }
                if (((int) ($finalAssign->mcq_mark ?? 0)) === 0) {
                    $m['mcq_mark'] = null;
                    $m['mcq_allocated'] = false;
                }
                if (((int) ($finalAssign->practical_mark ?? 0)) === 0) {
                    $m['practical_mark'] = null;
                    $m['practical_allocated'] = false;
                }
            }

            $m['is_fourth_subject'] = ($fourthSubjectId && ($subjectId === $fourthSubjectId || ($parentId > 0 && $parentId === $fourthSubjectId))) ? 1 : 0;
            $marks[$idx] = $m;
        }

        $parentIdsWithChildren = [];
        foreach ($marks as $m) {
            $sub = (array) ($m['subject'] ?? []);
            $pid = (int) ($sub['parent_id'] ?? 0);
            if ($pid > 0) {
                $parentIdsWithChildren[$pid] = true;
            }
        }

        $hasChildSubjects = !empty($parentIdsWithChildren);
        if (!$hasChildSubjects) {
            foreach ($marks as &$m) {
                $m['parent_subject_name'] = null;
            }
            unset($m);
            return array_values($marks);
        }

        if (Schema::hasTable('grade_management')) {
            $gradeRanges = DB::table('grade_management')
                ->select(['from_mark', 'to_mark', 'grade', 'gpa'])
                ->orderBy('from_mark')
                ->get();
        } else {
            $gradeRanges = null;
        }

        $calcGrade = static function (float $mark) use ($gradeRanges): array {
            if (!$gradeRanges) {
                return ['letter_grade' => null, 'gpa' => 0];
            }
            foreach ($gradeRanges as $r) {
                $from = (float) ($r->from_mark ?? 0);
                $to = (float) ($r->to_mark ?? 0);
                if ($mark >= $from && $mark <= $to) {
                    return ['letter_grade' => $r->grade, 'gpa' => (float) ($r->gpa ?? 0)];
                }
            }
            return ['letter_grade' => null, 'gpa' => 0];
        };

        // View mode: keep child rows (grouped by parent_id), but show total/grade once per group.
        if ($mode === 'view') {
            $groups = [];
            $groupOrder = [];
            foreach ($marks as $m) {
                $sub = (array) ($m['subject'] ?? []);
                $pid = (int) ($sub['parent_id'] ?? 0);
                $groupId = $pid > 0 ? $pid : (int) ($m['subject_id'] ?? 0);
                if (!isset($groups[$groupId])) {
                    $groups[$groupId] = [
                        'rows' => [],
                        'has_children' => false,
                        'parent_name' => null,
                    ];
                    $groupOrder[] = $groupId;
                }
                if ($pid > 0) {
                    $groups[$groupId]['has_children'] = true;
                    if ($groups[$groupId]['parent_name'] === null) {
                        $groups[$groupId]['parent_name'] = (string) ($sub['parent_name_en'] ?? '');
                    }
                }
                $groups[$groupId]['rows'][] = $m;
            }

            $final = [];
            foreach ($groupOrder as $gid) {
                $g = $groups[$gid] ?? null;
                if (!$g) continue;

                $rows = $g['rows'] ?? [];
                $hasChildren = (bool) ($g['has_children'] ?? false);
                if (!$hasChildren) {
                    foreach ($rows as $m) {
                        $m['parent_subject_name'] = null;
                        $final[] = $m;
                    }
                    continue;
                }

                $childRows = [];
                $childIds = [];
                foreach ($rows as $m) {
                    $sub = (array) ($m['subject'] ?? []);
                    if ((int) ($sub['parent_id'] ?? 0) > 0) {
                        $childRows[] = $m;
                        $childIds[] = (int) ($m['subject_id'] ?? 0);
                    }
                }

                // Fallback: if for some reason we don't have child rows, behave like no-children.
                if (empty($childRows)) {
                    foreach ($rows as $m) {
                        $m['parent_subject_name'] = null;
                        $final[] = $m;
                    }
                    continue;
                }

                $sumTotal = 0.0;
                $sumObtained = 0.0;
                $sumCt = 0.0;
                $absent = false;
                $additional = 0;

                foreach ($childRows as $cm) {
                    $sumTotal += (float) ($cm['total_mark'] ?? 0);
                    $sumObtained += (float) ($cm['obtained_mark'] ?? 0);
                    $sumCt += (float) ($cm['ct_mark'] ?? 0);
                    if ((int) ($cm['is_absent'] ?? 0) === 1) {
                        $absent = true;
                    }
                    if ((int) ($cm['additional_subject'] ?? 0) === 1) {
                        $additional = 1;
                    }
                }

                $avgForGrade = count($childRows) > 0 ? ($sumTotal / count($childRows)) : $sumTotal;
                $grade = $calcGrade($avgForGrade);

                $parentName = (string) ($g['parent_name'] ?? '');
                if ($parentName === '') {
                    $sub0 = (array) (($childRows[0]['subject'] ?? []) ?: []);
                    $parentName = (string) ($sub0['parent_name_en'] ?? '');
                }
                $codes = array_values(array_filter(array_unique($childIds)));
                sort($codes);
                $suffix = !empty($codes) ? (' (' . implode(', ', $codes) . ')') : '';
                $parentLabel = trim($parentName . $suffix);

                foreach ($childRows as $i => $m) {
                    $m['parent_subject_name'] = $parentLabel;
                    if ($i === 0) {
                        $m['total_mark'] = $sumTotal;
                        $m['letter_grade'] = $absent ? 'ABS' : ($grade['letter_grade'] ?? ($m['letter_grade'] ?? null));
                        $m['gpa'] = $absent ? 0 : ($grade['gpa'] ?? ($m['gpa'] ?? 0));
                    }
                    $final[] = $m;
                }
            }

            return array_values($final);
        }

        // PDF mode: show parent subjects only (child rows are aggregated or synthesized).
        $childGroups = [];
        foreach ($marks as $m) {
            $sub = (array) ($m['subject'] ?? []);
            $pid = (int) ($sub['parent_id'] ?? 0);
            if ($pid > 0) {
                $childGroups[$pid] ??= [];
                $childGroups[$pid][] = $m;
            }
        }

        $final = [];
        $added = [];

        foreach ($marks as $m) {
            $sub = (array) ($m['subject'] ?? []);
            $subjectId = (int) ($m['subject_id'] ?? 0);
            $parentId = (int) ($sub['parent_id'] ?? 0);

            // If this is a child subject row, skip it (we'll display the parent row).
            if ($parentId > 0) {
                // If parent mark row already exists, we'll let it render when we reach it.
                if (isset($bySubjectId[$parentId])) {
                    continue;
                }

                // Edge case: only child mark exists, parent mark row is missing.
                // Synthesize a parent row by aggregating child marks.
                if (!isset($added[$parentId])) {
                    $children = $childGroups[$parentId] ?? [$m];
                    $sumTotal = 0.0;
                    $sumObtained = 0.0;
                    $sumCt = 0.0;
                    $absent = false;
                    $additional = 0;
                    $sorting = null;

                    foreach ($children as $cm) {
                        $sumTotal += (float) ($cm['total_mark'] ?? 0);
                        $sumObtained += (float) ($cm['obtained_mark'] ?? 0);
                        $sumCt += (float) ($cm['ct_mark'] ?? 0);
                        if ((int) ($cm['is_absent'] ?? 0) === 1) {
                            $absent = true;
                        }
                        if ((int) ($cm['additional_subject'] ?? 0) === 1) {
                            $additional = 1;
                        }
                        if ($sorting === null) {
                            $sorting = $cm['sorting'] ?? null;
                        } else {
                            $sorting = min((float) $sorting, (float) ($cm['sorting'] ?? $sorting));
                        }
                    }

                    $avgForGrade = count($children) > 0 ? ($sumTotal / count($children)) : $sumTotal;
                    $grade = $calcGrade($avgForGrade);

                    $synth = $m;
                    $synth['subject_id'] = $parentId;
                    $synth['ct_mark'] = $sumCt;
                    $synth['obtained_mark'] = $sumObtained;
                    $synth['total_mark'] = $sumTotal;
                    $synth['sorting'] = $sorting;
                    $synth['is_absent'] = $absent ? 1 : 0;
                    $synth['additional_subject'] = $additional;
                    $synth['letter_grade'] = $absent ? 'ABS' : ($grade['letter_grade'] ?? null);
                    $synth['gpa'] = $absent ? 0 : ($grade['gpa'] ?? 0);
                    $synth['is_fourth_subject'] = ($fourthSubjectId && ($parentId === $fourthSubjectId)) ? 1 : ($synth['is_fourth_subject'] ?? 0);
                    $synth['subject'] = [
                        'id' => $parentId,
                        'name_en' => (string) ($sub['parent_name_en'] ?? ($sub['name_en'] ?? '')),
                        'is_child' => 0,
                        'parent_id' => 0,
                        'parent_name_en' => null,
                    ];
                    $synth['parent_subject_name'] = null;
                    $final[] = $synth;
                    $added[$parentId] = true;
                }
                continue;
            }

            if (isset($added[$subjectId])) {
                continue;
            }

            // If this is a parent subject row but its aggregate is missing/blank, compute display values
            // from child subjects (old ERP creates/updates a parent aggregate row; in new ERP it may be missing).
            if (isset($childGroups[$subjectId])) {
                $curTotal = (float) ($m['total_mark'] ?? 0);
                $children = $childGroups[$subjectId];
                $sumTotal = 0.0;
                $sumObtained = 0.0;
                $sumCt = 0.0;
                $absent = false;

                foreach ($children as $cm) {
                    $sumTotal += (float) ($cm['total_mark'] ?? 0);
                    $sumObtained += (float) ($cm['obtained_mark'] ?? 0);
                    $sumCt += (float) ($cm['ct_mark'] ?? 0);
                    if ((int) ($cm['is_absent'] ?? 0) === 1) {
                        $absent = true;
                    }
                }

                if ($curTotal <= 0 && $sumTotal > 0) {
                    $avgForGrade = count($children) > 0 ? ($sumTotal / count($children)) : $sumTotal;
                    $grade = $calcGrade($avgForGrade);
                    $m['ct_mark'] = $sumCt;
                    $m['obtained_mark'] = $sumObtained;
                    $m['total_mark'] = $sumTotal;
                    $m['is_absent'] = $absent ? 1 : 0;
                    $m['letter_grade'] = $absent ? 'ABS' : ($grade['letter_grade'] ?? ($m['letter_grade'] ?? null));
                    $m['gpa'] = $absent ? 0 : ($grade['gpa'] ?? ($m['gpa'] ?? 0));
                }
            }

            // Ensure parent display row carries 4th-subject flag even if the selected subject is a child.
            $m['is_fourth_subject'] = ($m['is_fourth_subject'] ?? 0) ? 1 : (($fourthSubjectId && isset($parentIdsWithChildren[$subjectId]) && $fourthSubjectId === $subjectId) ? 1 : 0);
            $m['parent_subject_name'] = null;
            $final[] = $m;
            $added[$subjectId] = true;
        }

        return array_values($final);
    }

    public function downloadBulkMarksheet(Request $request)
    {
        if (!Schema::hasTable('bulk_marksheet_exports')) {
            abort(422, 'Bulk marksheet export is not ready. Run migrations.');
        }

        $searchParams = [];
        if ($request->filled('search_params')) {
            $decoded = json_decode((string) $request->input('search_params'), true);
            if (is_array($decoded)) {
                $searchParams = $decoded;
            }
        }

        if (empty($searchParams)) {
            $resultId = (int) ($request->input('result_id') ?? 0);
            if ($resultId > 0) {
                $searchParams = ['result_id' => $resultId, 'type' => (string) ($request->input('type') ?? 'select')];
            }
        }

        if (empty($searchParams)) {
            abort(422, 'Invalid parameters');
        }

        $adminId = Auth::guard('admin')->id();
        $exportId = (int) DB::table('bulk_marksheet_exports')->insertGetId([
            'admin_id' => $adminId,
            'status' => 'pending',
            'search_params' => json_encode($searchParams),
            'file_path' => null,
            'error_message' => null,
            'started_at' => null,
            'finished_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        GenerateBulkMarksheetJob::dispatch($exportId);

        return response()->json([
            'export_id' => $exportId,
            'message' => 'Your marksheet is being generated. Please wait…',
        ]);
    }

    public function bulkMarksheetStatus(int $id)
    {
        if (!Schema::hasTable('bulk_marksheet_exports')) {
            return response()->json(['status' => 'failed', 'message' => 'Bulk export is not ready'], 422);
        }

        $export = DB::table('bulk_marksheet_exports')->where('id', $id)->first();
        if (!$export) {
            return response()->json(['status' => 'failed', 'message' => 'Export not found'], 404);
        }

        $status = (string) ($export->status ?? 'pending');
        $message = 'Queued – waiting to start…';
        if ($status === 'processing') $message = 'Generating PDF, please wait…';
        if ($status === 'done') $message = 'Ready! Click the button to download.';
        if ($status === 'failed') $message = 'Generation failed: ' . ((string) ($export->error_message ?? 'Unknown error.'));

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function downloadBulkMarksheetFile(int $id)
    {
        if (!Schema::hasTable('bulk_marksheet_exports')) {
            abort(422, 'Bulk export is not ready');
        }

        $export = DB::table('bulk_marksheet_exports')->where('id', $id)->first();
        if (!$export) {
            abort(404);
        }

        if ((string) ($export->status ?? '') !== 'done' || empty($export->file_path ?? null)) {
            abort(404, 'File not ready yet.');
        }

        $path = (string) $export->file_path;
        if (!Storage::exists($path)) {
            abort(404, 'File not found on disk.');
        }

        return Storage::download($path, 'bulk_marksheet.pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
        ]);

        if (!Schema::hasTable('results')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $admin = Auth::guard('admin')->user();
        $ip = $request->ip();

        $allowIds = $request->input('child_subject_enabled_subject_ids');
        if (!is_array($allowIds)) {
            $allowIds = [];
        }
        $allowIds = collect($allowIds)
            ->filter(fn($v) => $v !== null && $v !== '')
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->toArray();

        $payloadKey = [
            'academic_session_id' => $request->input('academic_session_id'),
            'department_id' => $request->input('department_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'academic_class_id' => $request->input('academic_class_id'),
            'exam_id' => $request->input('exam_id'),
        ];

        $existing = DB::table('results')->where($payloadKey)->first();

        $payload = $payloadKey + [
            'total_exam_subjects' => (int) ($request->input('total_exam_subjects') ?? 7),
            'child_subject_enabled' => (int) ($request->input('child_subject_enabled') ?? 0),
            'child_subject_enabled_subject_ids' => !empty($allowIds) ? json_encode($allowIds) : null,
            'updated_by' => $admin->name ?? null,
            'updated_ip' => $ip,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('results')->where('id', $existing->id)->update($payload);
            return response()->json(['message' => 'Create Successfully!', 'id' => $existing->id], 200);
        }

        $payload['status'] = 'draft';
        $payload['created_by'] = $admin->name ?? null;
        $payload['created_ip'] = $ip;
        $payload['created_at'] = now();

        $id = DB::table('results')->insertGetId($payload);
        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function show(Request $request, $id)
    {
        if (!Schema::hasTable('results')) {
            return response()->json(null, 404);
        }

        $row = DB::table('results as r')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->select([
                'r.*',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
                'ex.name as exam_name',
            ])
            ->where('r.id', $id)
            ->first();

        if (!$row) {
            return response()->json(null, 404);
        }

        if ($request->boolean('result_view')) {
            if (!Schema::hasTable('result_details') || !Schema::hasTable('students')) {
                return response()->json(['message' => 'Result module not ready'], 422);
            }

            $searchType = (string) ($request->input('type') ?? '');
            $failedSubject = (int) ($request->input('failed_subject') ?? 0);
            $passedSubject = (int) ($request->input('passed_subject') ?? 0);
            $excludeFourth = $request->boolean('exclude_fourth_subject');
            $field = (string) ($request->input('field_name') ?? '');
            $value = (string) ($request->input('value') ?? '');

            $detailsQuery = DB::table('result_details as d')
                ->join('students as std', 'std.id', '=', 'd.student_id')
                ->select([
                    'd.id',
                    'd.result_id',
                    'd.student_id',
                    'd.total_mark',
                    'd.gpa',
                    'd.letter_grade',
                    'd.result_status',
                    'std.student_id as software_id',
                    'std.name',
                    'std.mobile',
                    'std.college_roll',
                ])
                ->where('d.result_id', $row->id);

            if (Schema::hasTable('result_marks') && Schema::hasTable('subjects')) {
                $deptId = (int) ($row->department_id ?? 0);
                $classId = (int) ($row->academic_class_id ?? 0);

                $examCountSql = "(SELECT COUNT(DISTINCT rm.subject_id) FROM result_marks rm JOIN subjects sub ON sub.id = rm.subject_id WHERE rm.result_details_id = d.id AND (sub.is_child = 0 OR sub.is_child IS NULL))";

                if ($excludeFourth && $searchType === 'unmerit') {
                    $failedCountSql = "(SELECT COUNT(DISTINCT rm.subject_id) FROM result_marks rm JOIN subjects sub ON sub.id = rm.subject_id JOIN result_details rd2 ON rd2.id = rm.result_details_id LEFT JOIN student_subject_assigns ssa ON ssa.subject_id = rm.subject_id AND ssa.student_id = rd2.student_id AND ssa.department_id = {$deptId} AND ssa.academic_class_id = {$classId} WHERE rm.result_details_id = d.id AND (rm.letter_grade = 'F' OR rm.is_absent = 1) AND (sub.is_child = 0 OR sub.is_child IS NULL) AND (ssa.main_subject != 0 OR ssa.main_subject IS NULL))";
                } else {
                    $failedCountSql = "(SELECT COUNT(DISTINCT rm.subject_id) FROM result_marks rm JOIN subjects sub ON sub.id = rm.subject_id WHERE rm.result_details_id = d.id AND (rm.letter_grade = 'F' OR rm.is_absent = 1) AND (sub.is_child = 0 OR sub.is_child IS NULL))";
                }

                $passedCountSql = "(SELECT COUNT(DISTINCT rm.subject_id) FROM result_marks rm JOIN subjects sub ON sub.id = rm.subject_id WHERE rm.result_details_id = d.id AND rm.letter_grade != 'F' AND rm.is_absent = 0 AND (sub.is_child = 0 OR sub.is_child IS NULL))";

                $detailsQuery
                    ->addSelect(DB::raw("{$examCountSql} as exam_subject_count"))
                    ->addSelect(DB::raw("{$failedCountSql} as failed_subject_count"))
                    ->addSelect(DB::raw("{$passedCountSql} as passed_subject_count"));
            }

            $allowed = [
                'student_id' => 'std.student_id',
                'name' => 'std.name',
                'mobile' => 'std.mobile',
                'college_roll' => 'std.college_roll',
            ];
            $col = $allowed[$field] ?? null;
            if ($col && $value !== '') {
                $detailsQuery->where($col, 'like', '%' . $value . '%');
            }

            if ($failedSubject > 0) {
                $detailsQuery->having('failed_subject_count', '=', $failedSubject);
            }

            if ($passedSubject > 0) {
                $detailsQuery->having('passed_subject_count', '=', $passedSubject);
            }

            if ($searchType === 'merit') {
                $detailsQuery->where('d.result_status', 'PASSED')->orderByDesc('d.gpa')->orderByDesc('d.total_mark');
            } elseif ($searchType === 'unmerit') {
                $detailsQuery->where('d.result_status', 'FAILED')->orderBy('failed_subject_count', 'asc')->orderByDesc('d.total_mark');
            } else {
                $detailsQuery->orderByRaw('CAST(std.college_roll as UNSIGNED) asc');
            }

            $details = $detailsQuery->get();

            $marksByDetailId = [];
            if (($searchType === 'unmerit' || $searchType === 'all') && Schema::hasTable('result_marks') && Schema::hasTable('subjects')) {
                $detailIds = $details->pluck('id')->filter()->values()->all();
                if (!empty($detailIds)) {
                    $marksQuery = DB::table('result_marks as m')
                        ->join('subjects as sub', 'sub.id', '=', 'm.subject_id')
                        ->select([
                            'm.result_details_id',
                            'm.subject_id',
                            'm.letter_grade',
                            'm.is_absent',
                            'm.total_mark',
                            'sub.name_en as subject_name_en',
                        ])
                        ->whereIn('m.result_details_id', $detailIds)
                        ->where(function ($q) use ($searchType) {
                            if ($searchType === 'all') {
                                $q->where('m.letter_grade', '!=', 'F')->where('m.is_absent', 0);
                            } else {
                                $q->where('m.letter_grade', 'F')->orWhere('m.is_absent', 1);
                            }
                        })
                        ->orderBy('m.sorting')
                        ->get();

                    foreach ($marksQuery as $m) {
                        $marksByDetailId[$m->result_details_id] ??= [];
                        $marksByDetailId[$m->result_details_id][] = [
                            'subject_id' => $m->subject_id,
                            'subject' => ['id' => $m->subject_id, 'name_en' => $m->subject_name_en],
                            'letter_grade' => $m->letter_grade,
                            'is_absent' => (int) ($m->is_absent ?? 0),
                            'total_mark' => $m->total_mark,
                        ];
                    }
                }
            }

            $detailsPayload = $details->map(function ($d) use ($marksByDetailId) {
                return [
                    'id' => $d->id,
                    'result_id' => $d->result_id,
                    'student_id' => $d->software_id,
                    'name' => $d->name,
                    'mobile' => $d->mobile,
                    'college_roll' => $d->college_roll,
                    'total_mark' => $d->total_mark,
                    'gpa' => $d->gpa,
                    'letter_grade' => $d->letter_grade,
                    'result_status' => $d->result_status,
                    'exam_subject_count' => $d->exam_subject_count ?? null,
                    'failed_subject_count' => $d->failed_subject_count ?? null,
                    'passed_subject_count' => $d->passed_subject_count ?? null,
                    'marks' => $marksByDetailId[$d->id] ?? [],
                ];
            })->values()->all();

            $payload = [
                'id' => $row->id,
                'academic_session_id' => $row->academic_session_id,
                'department_id' => $row->department_id,
                'academic_qualification_id' => $row->academic_qualification_id,
                'academic_class_id' => $row->academic_class_id,
                'exam_id' => $row->exam_id,
                'published_date' => $row->published_date,
                'status' => $row->status,
                'academic_session' => ['id' => $row->academic_session_id, 'name' => $row->academic_session_name],
                'qualification' => ['id' => $row->academic_qualification_id, 'name' => $row->academic_qualification_name],
                'department' => ['id' => $row->department_id, 'name' => $row->department_name],
                'academic_class' => ['id' => $row->academic_class_id, 'name' => $row->academic_class_name],
                'exam' => ['id' => $row->exam_id, 'name' => $row->exam_name],
                'details' => $detailsPayload,
            ];

            $excelHeader = [
                'Session: ' . ($row->academic_session_name ?? ''),
                'Academic Level: ' . ($row->academic_qualification_name ?? ''),
                'Department/Group: ' . ($row->department_name ?? ''),
                'Academic Class: ' . ($row->academic_class_name ?? ''),
                'Exam Name: ' . ($row->exam_name ?? ''),
            ];

            return response()->json([
                'result' => $payload,
                'excel_header' => $excelHeader,
            ]);
        }

        $allowIds = [];
        if (!empty($row->child_subject_enabled_subject_ids)) {
            $decoded = json_decode((string) $row->child_subject_enabled_subject_ids, true);
            if (is_array($decoded)) {
                $allowIds = array_values(array_unique(array_map('intval', $decoded)));
            }
        }

        return response()->json([
            'id' => $row->id,
            'academic_session_id' => $row->academic_session_id,
            'department_id' => $row->department_id,
            'academic_qualification_id' => $row->academic_qualification_id,
            'academic_class_id' => $row->academic_class_id,
            'exam_id' => $row->exam_id,
            'total_exam_subjects' => $row->total_exam_subjects,
            'published_date' => $row->published_date,
            'status' => $row->status,
            'child_subject_enabled' => (int) ($row->child_subject_enabled ?? 0),
            'child_subject_enabled_subject_ids' => $allowIds,
            'academic_session_name' => $row->academic_session_name,
            'academic_qualification_name' => $row->academic_qualification_name,
            'department_name' => $row->department_name,
            'academic_class_name' => $row->academic_class_name,
            'exam_name' => $row->exam_name,
        ]);
    }

    public function report(Request $request)
    {
        $resultId = (int) ($request->input('result_id') ?? 0);

        if ($resultId <= 0) {
            $resultId = (int) DB::table('results')
                ->where('academic_session_id', $request->input('academic_session_id'))
                ->where('academic_qualification_id', $request->input('academic_qualification_id'))
                ->where('department_id', $request->input('department_id'))
                ->where('academic_class_id', $request->input('academic_class_id'))
                ->where('exam_id', $request->input('exam_id'))
                ->orderByDesc('id')
                ->value('id');
        }

        if ($resultId <= 0) {
            return response()->json([
                'result' => array_merge($request->all(), ['details' => []]),
                'excel_header' => [],
            ], 200);
        }

        $request->merge([
            'result_view' => true,
            'value' => (string) ($request->input('search_keyword') ?? ''),
        ]);

        return $this->show($request, $resultId);
    }

    public function subjectwiseResultData(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
            'subject_id' => ['required'],
        ]);

        if (!Schema::hasTable('results') || !Schema::hasTable('result_details') || !Schema::hasTable('result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $resultRow = DB::table('results as r')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->where([
                'r.academic_session_id' => $request->input('academic_session_id'),
                'r.academic_qualification_id' => $request->input('academic_qualification_id'),
                'r.department_id' => $request->input('department_id'),
                'r.academic_class_id' => $request->input('academic_class_id'),
                'r.exam_id' => $request->input('exam_id'),
            ])
            ->orderByDesc('r.id')
            ->select([
                'r.id',
                'r.academic_session_id',
                'r.academic_qualification_id',
                'r.department_id',
                'r.academic_class_id',
                'r.exam_id',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'cls.name as academic_class_name',
                'ex.name as exam_name',
            ])
            ->first();

        if (!$resultRow) {
            return response()->json([
                'result' => array_merge($request->all(), ['details' => []]),
                'excel_header' => [],
            ], 200);
        }

        $subjectId = (int) $request->input('subject_id');
        $fieldName = (string) ($request->input('field_name') ?? '');
        $searchValue = (string) ($request->input('search_keyword') ?? '');

        $allowedFields = [
            'student_id' => 'std.student_id',
            'name' => 'std.name',
            'mobile' => 'std.mobile',
            'college_roll' => 'std.college_roll',
        ];

        $detailsQuery = DB::table('result_details as d')
            ->join('students as std', 'std.id', '=', 'd.student_id')
            ->join('result_marks as rm', 'rm.result_details_id', '=', 'd.id')
            ->where('d.result_id', $resultRow->id)
            ->where('rm.subject_id', $subjectId)
            ->select([
                'std.student_id',
                'std.name',
                'std.mobile',
                'std.college_roll',
                'rm.subject_id',
                'rm.ct_mark',
                'rm.cq_mark',
                'rm.mcq_mark',
                'rm.practical_mark',
                'rm.obtained_mark',
                'rm.total_mark',
                'rm.letter_grade',
                'rm.gpa',
                'rm.is_absent',
            ])
            ->orderBy('std.college_roll', 'asc');

        if (!empty($fieldName) && !empty($searchValue) && isset($allowedFields[$fieldName])) {
            $detailsQuery->where($allowedFields[$fieldName], 'like', '%' . $searchValue . '%');
        }

        if (Schema::hasColumn('students', 'status')) {
            $detailsQuery->where('std.status', 'active');
        }

        $details = $detailsQuery->get()->map(function ($r) {
            return [
                'student_id' => $r->student_id,
                'name' => $r->name,
                'mobile' => $r->mobile,
                'college_roll' => $r->college_roll,
                'subject_id' => (int) $r->subject_id,
                'ct_mark' => $r->ct_mark,
                'cq_mark' => $r->cq_mark,
                'mcq_mark' => $r->mcq_mark,
                'practical_mark' => $r->practical_mark,
                'obtained_mark' => $r->obtained_mark,
                'total_mark' => $r->total_mark,
                'letter_grade' => $r->letter_grade,
                'gpa' => $r->gpa,
                'is_absent' => (int) ($r->is_absent ?? 0),
            ];
        })->values()->all();

        $ctAllocated = true;
        $cqAllocated = true;
        $mcqAllocated = true;
        $practicalAllocated = true;

        if (Schema::hasTable('subject_assigns') && Schema::hasTable('subject_assign_details')) {
            $subjectAssign = DB::table('subject_assigns')
                ->where('academic_qualification_id', $request->input('academic_qualification_id'))
                ->where('department_id', $request->input('department_id'))
                ->where('academic_class_id', $request->input('academic_class_id'))
                ->first();

            if ($subjectAssign) {
                $assignDetail = DB::table('subject_assign_details')
                    ->where('subject_assign_id', $subjectAssign->id)
                    ->where('subject_id', $subjectId)
                    ->first();

                if ($assignDetail) {
                    $ctAllocated = ((int) ($assignDetail->ct_mark ?? 0)) > 0;
                    $cqAllocated = ((int) ($assignDetail->cq_mark ?? 0)) > 0;
                    $mcqAllocated = ((int) ($assignDetail->mcq_mark ?? 0)) > 0;
                    $practicalAllocated = ((int) ($assignDetail->practical_mark ?? 0)) > 0;
                }
            }
        }

        foreach ($details as &$d) {
            $d['ct_allocated'] = $ctAllocated;
            $d['cq_allocated'] = $cqAllocated;
            $d['mcq_allocated'] = $mcqAllocated;
            $d['practical_allocated'] = $practicalAllocated;
        }
        unset($d);

        $payload = [
            'id' => (int) $resultRow->id,
            'academic_session' => ['id' => (int) $resultRow->academic_session_id, 'name' => $resultRow->academic_session_name],
            'qualification' => ['id' => (int) $resultRow->academic_qualification_id, 'name' => $resultRow->academic_qualification_name],
            'department' => ['id' => (int) $resultRow->department_id, 'name' => $resultRow->department_name],
            'academic_class' => ['id' => (int) $resultRow->academic_class_id, 'name' => $resultRow->academic_class_name],
            'exam' => ['id' => (int) $resultRow->exam_id, 'name' => $resultRow->exam_name],
            'details' => $details,
        ];

        $excelHeader = [
            'Session: ' . ($resultRow->academic_session_name ?? ''),
            'Academic Level: ' . ($resultRow->academic_qualification_name ?? ''),
            'Department/Group: ' . ($resultRow->department_name ?? ''),
            'Academic Class: ' . ($resultRow->academic_class_name ?? ''),
            'Exam Name: ' . ($resultRow->exam_name ?? ''),
        ];

        return response()->json([
            'result' => $payload,
            'excel_header' => $excelHeader,
        ], 200);
    }

    public function tabulationSheetData(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
        ]);

        if (!Schema::hasTable('results') || !Schema::hasTable('result_details') || !Schema::hasTable('result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $resultRow = DB::table('results as r')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->where([
                'r.academic_session_id' => $request->input('academic_session_id'),
                'r.department_id' => $request->input('department_id'),
                'r.academic_qualification_id' => $request->input('academic_qualification_id'),
                'r.academic_class_id' => $request->input('academic_class_id'),
                'r.exam_id' => $request->input('exam_id'),
            ])
            ->select([
                'r.id',
                'r.child_subject_enabled',
                'r.child_subject_enabled_subject_ids',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'cls.name as academic_class_name',
                'ex.name as exam_name',
            ])
            ->first();

        if (!$resultRow) {
            return response()->json([
                'academic_session' => '',
                'academic_level' => '',
                'department' => '',
                'academic_class' => '',
                'exam' => '',
                'result_sheet' => [],
                'subjects' => [],
                'subject_chunks' => [],
            ], 200);
        }

        $subjects = $this->tabulationSubjectLists($request);

        $allowIds = $this->decodeIdList($resultRow->child_subject_enabled_subject_ids ?? null);
        $filteredSubjects = $this->filterTabulationSubjects($subjects, (int) ($resultRow->child_subject_enabled ?? 0), $allowIds);

        $resultSheet = $this->tabulationFormat((int) $resultRow->id, $filteredSubjects, (string) ($request->input('field_name') ?? ''), (string) ($request->input('search_keyword') ?? ''));

        return response()->json([
            'academic_session' => $resultRow->academic_session_name ?? '',
            'academic_level' => $resultRow->academic_qualification_name ?? '',
            'department' => $resultRow->department_name ?? '',
            'academic_class' => $resultRow->academic_class_name ?? '',
            'exam' => $resultRow->exam_name ?? '',
            'result_sheet' => $resultSheet,
            'subjects' => $filteredSubjects,
            'subject_chunks' => array_chunk($filteredSubjects, 3),
        ], 200);
    }

    public function exportTabulationSheet(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
        ]);

        if (!Schema::hasTable('results') || !Schema::hasTable('result_details') || !Schema::hasTable('result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $resultRow = DB::table('results')
            ->select('id', 'child_subject_enabled', 'child_subject_enabled_subject_ids')
            ->where([
                'academic_session_id' => $request->input('academic_session_id'),
                'department_id' => $request->input('department_id'),
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'academic_class_id' => $request->input('academic_class_id'),
                'exam_id' => $request->input('exam_id'),
            ])
            ->first();

        if (!$resultRow) {
            return response()->json(['message' => 'Result not found'], 404);
        }

        $subjects = $this->tabulationSubjectLists($request);
        $allowIds = $this->decodeIdList($resultRow->child_subject_enabled_subject_ids ?? null);
        $filteredSubjects = $this->filterTabulationSubjects($subjects, (int) ($resultRow->child_subject_enabled ?? 0), $allowIds);

        $resultSheet = $this->tabulationFormat((int) $resultRow->id, $filteredSubjects, (string) ($request->input('field_name') ?? ''), (string) ($request->input('search_keyword') ?? ''));
        $exportData = $this->formatForTabulationExcel($resultSheet);

        $fileName = 'Tabulation_Sheet_' . date('Y_m_d_H_i_s') . '.xlsx';
        return Excel::download(new TabulationSheetExport($exportData, $filteredSubjects, (int) ($resultRow->child_subject_enabled ?? 0)), $fileName);
    }

    public function tabulationSheetCtData(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
        ]);

        if (!Schema::hasTable('class_test_results') || !Schema::hasTable('class_test_result_details') || !Schema::hasTable('class_test_result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $resultRow = DB::table('class_test_results as r')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->where([
                'r.academic_session_id' => $request->input('academic_session_id'),
                'r.department_id' => $request->input('department_id'),
                'r.academic_qualification_id' => $request->input('academic_qualification_id'),
                'r.academic_class_id' => $request->input('academic_class_id'),
                'r.exam_id' => $request->input('exam_id'),
            ])
            ->select([
                'r.id',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'cls.name as academic_class_name',
                'ex.name as exam_name',
            ])
            ->first();

        $subjects = $this->tabulationSubjectLists($request);

        if (!$resultRow) {
            return response()->json([
                'academic_session' => '',
                'academic_level' => '',
                'department' => '',
                'academic_class' => '',
                'exam' => '',
                'result_sheet' => [],
                'subjects' => $subjects,
            ], 200);
        }

        $resultSheet = $this->tabulationFormatCt((int) $resultRow->id, $subjects, (string) ($request->input('field_name') ?? ''), (string) ($request->input('search_keyword') ?? ''));

        return response()->json([
            'academic_session' => $resultRow->academic_session_name ?? '',
            'academic_level' => $resultRow->academic_qualification_name ?? '',
            'department' => $resultRow->department_name ?? '',
            'academic_class' => $resultRow->academic_class_name ?? '',
            'exam' => $resultRow->exam_name ?? '',
            'result_sheet' => $resultSheet,
            'subjects' => $subjects,
        ], 200);
    }

    public function tabulationSheetV2Data(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
        ]);

        if (!Schema::hasTable('results') || !Schema::hasTable('result_details') || !Schema::hasTable('result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $resultRow = DB::table('results as r')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->where([
                'r.academic_session_id' => $request->input('academic_session_id'),
                'r.department_id' => $request->input('department_id'),
                'r.academic_qualification_id' => $request->input('academic_qualification_id'),
                'r.academic_class_id' => $request->input('academic_class_id'),
                'r.exam_id' => $request->input('exam_id'),
            ])
            ->select([
                'r.id',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'cls.name as academic_class_name',
                'ex.name as exam_name',
            ])
            ->first();

        if (!$resultRow) {
            return response()->json([
                'academic_session' => '',
                'academic_level' => '',
                'department' => '',
                'academic_class' => '',
                'exam' => '',
                'result_sheet' => [],
                'subjects' => [],
                'subject_chunks' => [],
            ], 200);
        }

        $subjects = $this->tabulationSubjectLists($request);
        $resultSheet = $this->tabulationFormatV2((int) $resultRow->id, $subjects, (string) ($request->input('field_name') ?? ''), (string) ($request->input('search_keyword') ?? ''));

        return response()->json([
            'academic_session' => $resultRow->academic_session_name ?? '',
            'academic_level' => $resultRow->academic_qualification_name ?? '',
            'department' => $resultRow->department_name ?? '',
            'academic_class' => $resultRow->academic_class_name ?? '',
            'exam' => $resultRow->exam_name ?? '',
            'result_sheet' => $resultSheet,
            'subjects' => $subjects,
            'subject_chunks' => array_chunk($subjects, 3),
        ], 200);
    }

    public function gradeSummaryData(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
        ]);

        if (!Schema::hasTable('results') || !Schema::hasTable('result_details') || !Schema::hasTable('result_marks') || !Schema::hasTable('subjects')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $resultRow = DB::table('results as r')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->where([
                'r.academic_session_id' => $request->input('academic_session_id'),
                'r.department_id' => $request->input('department_id'),
                'r.academic_qualification_id' => $request->input('academic_qualification_id'),
                'r.academic_class_id' => $request->input('academic_class_id'),
                'r.exam_id' => $request->input('exam_id'),
            ])
            ->select([
                'r.id',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'cls.name as academic_class_name',
                'ex.name as exam_name',
            ])
            ->first();

        if (!$resultRow) {
            return response()->json([
                'academic_session' => '',
                'academic_level' => '',
                'department' => '',
                'academic_class' => '',
                'exam' => '',
                'grade_summary' => [],
                'subject_summary' => [],
            ], 200);
        }

        $subjectSummary = DB::table('result_marks as rm')
            ->selectRaw('rm.subject_id, sub.name_en as subject_name, rm.letter_grade, COUNT(rm.id) as grade_count')
            ->join('result_details as rd', 'rm.result_details_id', '=', 'rd.id')
            ->join('subjects as sub', 'rm.subject_id', '=', 'sub.id')
            ->where('rd.result_id', (int) $resultRow->id)
            ->orderBy('rm.letter_grade', 'asc')
            ->groupBy('rm.subject_id', 'sub.name_en', 'rm.letter_grade')
            ->get()
            ->map(function ($item) {
                if ((string) $item->letter_grade === '0') {
                    $item->letter_grade = 'Absent';
                } elseif (is_null($item->letter_grade) || $item->letter_grade === '') {
                    $item->letter_grade = 'Undefined';
                }
                return $item;
            })
            ->groupBy('subject_id');

        $gradeSummary = DB::table('result_details')
            ->selectRaw('letter_grade, COUNT(id) as grade_count')
            ->where('result_id', (int) $resultRow->id)
            ->groupBy('letter_grade')
            ->get()
            ->map(function ($item) {
                if ((string) $item->letter_grade === '0') {
                    $item->letter_grade = 'Absent';
                } elseif (is_null($item->letter_grade) || $item->letter_grade === '') {
                    $item->letter_grade = 'Undefined';
                }
                return $item;
            })
            ->sortBy(function ($item) {
                $order = [
                    'A+' => 1,
                    'A' => 2,
                    'A-' => 3,
                    'B' => 4,
                    'C' => 5,
                    'D' => 6,
                    'F' => 7,
                    'Absent' => 8,
                    'Undefined' => 9,
                ];
                return $order[$item->letter_grade] ?? 99;
            })
            ->values();

        return response()->json([
            'academic_session' => $resultRow->academic_session_name ?? '',
            'academic_level' => $resultRow->academic_qualification_name ?? '',
            'department' => $resultRow->department_name ?? '',
            'academic_class' => $resultRow->academic_class_name ?? '',
            'exam' => $resultRow->exam_name ?? '',
            'grade_summary' => $gradeSummary,
            'subject_summary' => $subjectSummary,
        ], 200);
    }

    private function tabulationSubjectLists(Request $request): array
    {
        if (!Schema::hasTable('subject_assigns') || !Schema::hasTable('subject_assign_details') || !Schema::hasTable('subjects')) {
            return [];
        }

        $subjectAssign = DB::table('subject_assigns')
            ->where([
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'department_id' => $request->input('department_id'),
                'academic_class_id' => $request->input('academic_class_id'),
            ])
            ->first();

        if (!$subjectAssign) {
            return [];
        }

        $parents = DB::table('subject_assign_details as d')
            ->join('subjects as s', 's.id', '=', 'd.subject_id')
            ->where('d.subject_assign_id', $subjectAssign->id)
            ->where('s.is_child', 0)
            ->select([
                'd.subject_id',
                'd.sorting',
                's.name_en',
                's.is_child',
                's.parent_id',
            ])
            ->orderBy('d.sorting')
            ->get();

        $subjects = [];
        foreach ($parents as $p) {
            $children = DB::table('subjects')
                ->where('parent_id', $p->subject_id)
                ->select(['id', 'name_en', 'is_child', 'parent_id'])
                ->orderBy('id')
                ->get()
                ->map(function ($c) {
                    return [
                        'id' => (int) $c->id,
                        'name_en' => $c->name_en,
                        'is_child' => (int) ($c->is_child ?? 0),
                        'parent_id' => $c->parent_id,
                    ];
                })
                ->values()
                ->all();

            $subjects[] = [
                'subject_id' => (int) $p->subject_id,
                'subject' => [
                    'id' => (int) $p->subject_id,
                    'name_en' => $p->name_en,
                    'is_child' => (int) ($p->is_child ?? 0),
                    'parent_id' => $p->parent_id,
                    'childSubjects' => $children,
                ],
            ];
        }

        return $subjects;
    }

    private function filterTabulationSubjects(array $subjects, int $childSubjectEnabled, array $allowIds = []): array
    {
        if (empty($subjects)) {
            return [];
        }

        $ordered = [];
        $processed = [];

        foreach ($subjects as $subject) {
            $subjectData = $subject['subject'] ?? [];
            $subjectId = (int) ($subjectData['id'] ?? ($subject['subject_id'] ?? 0));
            if (!$subjectId) {
                continue;
            }

            if (in_array($subjectId, $processed, true)) {
                continue;
            }

            $childSubjects = $subjectData['childSubjects'] ?? $subjectData['child_subjects'] ?? [];
            $hasChildren = is_array($childSubjects) && count($childSubjects) > 0;
            $shouldExpand = $childSubjectEnabled === 1 || in_array($subjectId, $allowIds, true);

            if ($hasChildren && $shouldExpand) {
                foreach ($childSubjects as $child) {
                    $childId = (int) ($child['id'] ?? 0);
                    if (!$childId || in_array($childId, $processed, true)) {
                        continue;
                    }
                    $ordered[] = [
                        'subject_id' => $childId,
                        'subject' => $child,
                    ];
                    $processed[] = $childId;
                }
                $processed[] = $subjectId;
            } else {
                $ordered[] = $subject;
                $processed[] = $subjectId;
            }
        }

        return $ordered;
    }

    private function decodeIdList($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_unique(array_map('intval', $raw)));
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_values(array_unique(array_map('intval', $decoded)));
            }
        }

        return [];
    }

    private function tabulationFormat(int $resultId, array $subjects, string $fieldName = '', string $searchValue = ''): array
    {
        $subjectIds = array_values(array_filter(array_map(function ($s) {
            return (int) ($s['subject_id'] ?? 0);
        }, $subjects)));

        $allowedFields = [
            'student_id' => 'std.student_id',
            'name' => 'std.name',
            'mobile' => 'std.mobile',
            'college_roll' => 'std.college_roll',
        ];

        $detailsQuery = DB::table('result_details as d')
            ->join('students as std', 'std.id', '=', 'd.student_id')
            ->where('d.result_id', $resultId)
            ->select([
                'd.id',
                'd.student_id as student_pk',
                'd.total_mark',
                'd.gpa',
                'd.letter_grade',
                'd.result_status',
                'std.student_id as software_id',
                'std.name',
                'std.mobile',
                'std.college_roll',
            ])
            ->orderByRaw('CAST(std.college_roll as UNSIGNED) asc');

        if (!empty($fieldName) && !empty($searchValue) && isset($allowedFields[$fieldName])) {
            $detailsQuery->where($allowedFields[$fieldName], 'like', '%' . $searchValue . '%');
        }

        if (Schema::hasColumn('students', 'status')) {
            $detailsQuery->where('std.status', 'active');
        }

        $details = $detailsQuery->get();
        $detailIds = $details->pluck('id')->map(fn($v) => (int) $v)->values()->all();

        $marksByDetail = [];
        if (!empty($detailIds) && !empty($subjectIds)) {
            $marks = DB::table('result_marks')
                ->whereIn('result_details_id', $detailIds)
                ->whereIn('subject_id', $subjectIds)
                ->select([
                    'id',
                    'result_details_id',
                    'subject_id',
                    'ct_mark',
                    'cq_mark',
                    'mcq_mark',
                    'practical_mark',
                    'obtained_mark',
                    'total_mark',
                    'gpa',
                    'letter_grade',
                    'additional_subject',
                    'is_absent',
                    'sorting',
                ])
                ->orderBy('sorting')
                ->get();

            foreach ($marks as $m) {
                $did = (int) $m->result_details_id;
                $sid = (int) $m->subject_id;
                $marksByDetail[$did] ??= [];
                $marksByDetail[$did][$sid] = [
                    'id' => (int) $m->id,
                    'result_details_id' => (int) $m->result_details_id,
                    'subject_id' => (int) $m->subject_id,
                    'ct_mark' => $m->ct_mark,
                    'cq_mark' => $m->cq_mark,
                    'mcq_mark' => $m->mcq_mark,
                    'practical_mark' => $m->practical_mark,
                    'obtained_mark' => $m->obtained_mark,
                    'total_mark' => $m->total_mark,
                    'gpa' => $m->gpa,
                    'letter_grade' => $m->letter_grade,
                    'additional_subject' => $m->additional_subject,
                    'is_absent' => $m->is_absent,
                    'sorting' => $m->sorting,
                ];
            }
        }

        $sheet = [];
        foreach ($details as $d) {
            $studentArr = [
                'name' => $d->name ?? '',
                'mobile' => $d->mobile ?? '',
                'software_id' => $d->software_id ?? '',
                'college_roll' => $d->college_roll ?? '',
                'total_mark' => $d->total_mark,
                'gpa' => $d->gpa,
                'letter_grade' => $d->letter_grade,
                'result_status' => $d->result_status,
                'subjects' => [],
            ];

            $did = (int) $d->id;
            foreach ($subjects as $sub) {
                $sid = (int) ($sub['subject_id'] ?? 0);
                if (!$sid) {
                    $studentArr['subjects'][] = [];
                    continue;
                }
                $studentArr['subjects'][] = $marksByDetail[$did][$sid] ?? [];
            }

            $sheet[] = $studentArr;
        }

        return $sheet;
    }

    private function tabulationFormatCt(int $classTestResultId, array $subjects, string $fieldName = '', string $searchValue = ''): array
    {
        $subjectIds = array_values(array_filter(array_map(function ($s) {
            return (int) ($s['subject_id'] ?? 0);
        }, $subjects)));

        $allowedFields = [
            'student_id' => 'std.student_id',
            'name' => 'std.name',
            'mobile' => 'std.mobile',
            'college_roll' => 'std.college_roll',
        ];

        $detailsQuery = DB::table('class_test_result_details as d')
            ->join('students as std', 'std.id', '=', 'd.student_id')
            ->where('d.class_test_result_id', $classTestResultId)
            ->select([
                'd.id',
                'd.total_mark',
                'd.result_status',
                'std.student_id as software_id',
                'std.name',
                'std.mobile',
                'std.college_roll',
            ])
            ->orderByRaw('CAST(std.college_roll as UNSIGNED) asc');

        if (!empty($fieldName) && !empty($searchValue) && isset($allowedFields[$fieldName])) {
            $detailsQuery->where($allowedFields[$fieldName], 'like', '%' . $searchValue . '%');
        }

        if (Schema::hasColumn('students', 'status')) {
            $detailsQuery->where('std.status', 'active');
        }

        $details = $detailsQuery->get();
        $detailIds = $details->pluck('id')->map(fn($v) => (int) $v)->values()->all();

        $marksByDetail = [];
        if (!empty($detailIds) && !empty($subjectIds)) {
            $marks = DB::table('class_test_result_marks')
                ->whereIn('class_test_result_details_id', $detailIds)
                ->whereIn('subject_id', $subjectIds)
                ->select(['class_test_result_details_id', 'subject_id', 'mark'])
                ->get();

            foreach ($marks as $m) {
                $did = (int) $m->class_test_result_details_id;
                $sid = (int) $m->subject_id;
                $marksByDetail[$did] ??= [];
                $marksByDetail[$did][$sid] = $m->mark;
            }
        }

        $sheet = [];
        foreach ($details as $d) {
            $studentArr = [
                'name' => $d->name ?? '',
                'mobile' => $d->mobile ?? '',
                'software_id' => $d->software_id ?? '',
                'college_roll' => $d->college_roll ?? '',
                'total_mark' => $d->total_mark,
                'result_status' => $d->result_status,
                'subjects' => [],
            ];

            $did = (int) $d->id;
            foreach ($subjects as $sub) {
                $sid = (int) ($sub['subject_id'] ?? 0);
                $studentArr['subjects'][] = [
                    'subject_id' => $sid,
                    'ct_mark' => $sid ? ($marksByDetail[$did][$sid] ?? null) : null,
                ];
            }

            $sheet[] = $studentArr;
        }

        return $sheet;
    }

    private function tabulationFormatV2(int $resultId, array $subjects, string $fieldName = '', string $searchValue = ''): array
    {
        $subjectChunks = array_chunk($subjects, 3);

        $base = $this->tabulationFormat($resultId, $subjects, $fieldName, $searchValue);
        if (empty($base)) {
            return [];
        }

        $out = [];
        foreach ($base as $row) {
            $studentArr = $row;
            $studentArr['subject_chunks'] = [];

            foreach ($subjectChunks as $chunkIndex => $chunk) {
                $sliceStart = $chunkIndex * 3;
                $studentArr['subject_chunks'][$chunkIndex] = array_slice($row['subjects'] ?? [], $sliceStart, 3);
            }

            $out[] = $studentArr;
        }

        return $out;
    }

    private function formatForTabulationExcel(array $resultSheet): array
    {
        $exportData = [];
        $sl = 1;

        foreach ($resultSheet as $student) {
            $row = [
                $sl++,
                $student['college_roll'] ?? '',
                $student['name'] ?? '',
            ];

            foreach (($student['subjects'] ?? []) as $subMark) {
                $row[] = $subMark['cq_mark'] ?? '-';
                $row[] = $subMark['mcq_mark'] ?? '-';
                $row[] = $subMark['practical_mark'] ?? '-';
                $row[] = $subMark['total_mark'] ?? '-';
                $row[] = $subMark['letter_grade'] ?? '-';
                $row[] = $subMark['gpa'] ?? '-';
            }

            $row[] = $student['total_mark'] ?? '';
            $row[] = $student['gpa'] ?? '';
            $row[] = $student['letter_grade'] ?? '';

            $exportData[] = $row;
        }

        return $exportData;
    }

    public function studentsForMarksEntry(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'subject_id' => ['required'],
            'exam_id' => ['required'],
        ]);

        if (!Schema::hasTable('subjects') || !Schema::hasTable('subject_assigns') || !Schema::hasTable('subject_assign_details') || !Schema::hasTable('students')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $subjectID = (int) $request->input('subject_id');
        $examID = (int) $request->input('exam_id');
        $resultID = (int) ($request->input('result_id') ?? 0);
        $selectedStudent = (int) ($request->input('selected_student') ?? 0);

        $subjectAssign = DB::table('subject_assigns')->where([
            'department_id' => $request->input('department_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'academic_class_id' => $request->input('academic_class_id'),
        ])->first();

        if (!$subjectAssign) {
            return response()->json(['message' => 'Subject not assign for this class'], 422);
        }

        $parentSubId = DB::table('subjects')->where('id', $subjectID)->value('parent_id');
        $checkSubjectId = $parentSubId ? (int) $parentSubId : $subjectID;

        $isCommonSubject = DB::table('subject_assign_details')
            ->where('subject_assign_id', $subjectAssign->id)
            ->where('subject_id', $checkSubjectId)
            ->where('main_subject', 0)
            ->where('fourth_subject', 0)
            ->exists();

        $resultDetailsDict = collect();
        $subjectIds = [$subjectID];
        $childEnabled = false;
        $childSubjectsPayload = [];
        $effectiveParentId = $subjectID;

        if (!empty($resultID) && Schema::hasTable('results') && Schema::hasTable('result_details') && Schema::hasTable('result_marks')) {
            if ($request->filled('total_exam_subjects')) {
                DB::table('results')->where('id', $resultID)->update(['total_exam_subjects' => (int) $request->input('total_exam_subjects'), 'updated_at' => now()]);
            }

            $resultRow = DB::table('results')->select('id', 'child_subject_enabled', 'child_subject_enabled_subject_ids')->where('id', $resultID)->first();
            $allowIds = [];
            if ($resultRow && !empty($resultRow->child_subject_enabled_subject_ids)) {
                $decoded = json_decode((string) $resultRow->child_subject_enabled_subject_ids, true);
                if (is_array($decoded)) {
                    $allowIds = array_values(array_unique(array_map('intval', $decoded)));
                }
            }

            $effectiveParentId = (int) (DB::table('subjects')->where('id', $subjectID)->value('parent_id') ?: $subjectID);

            $childEnabled = ((int) ($resultRow->child_subject_enabled ?? 0) === 1) || in_array($effectiveParentId, $allowIds, true);

            if ($childEnabled) {
                $childSubjects = DB::table('subjects')->where('parent_id', $effectiveParentId)->pluck('id')->toArray();
                $subjectIds = !empty($childSubjects) ? array_values(array_map('intval', $childSubjects)) : [$subjectID];
            }

            if (!empty($subjectIds)) {
                $childSubjectsPayload = DB::table('subjects')
                    ->whereIn('id', $subjectIds)
                    ->select(['id', 'name_en', 'parent_id', 'is_child'])
                    ->orderBy('id')
                    ->get()
                    ->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'name_en' => $s->name_en,
                            'parent_id' => $s->parent_id,
                            'is_child' => $s->is_child,
                        ];
                    })
                    ->values()
                    ->all();
            }

            $details = DB::table('result_details')->where('result_id', $resultID)->get();
            if ($details->count() > 0) {
                $detailIds = $details->pluck('id')->all();

                $marks = DB::table('result_marks as m')
                    ->leftJoin('subjects as s', 's.id', '=', 'm.subject_id')
                    ->whereIn('m.result_details_id', $detailIds)
                    ->whereIn('m.subject_id', $subjectIds)
                    ->orderBy('m.sorting')
                    ->select([
                        'm.*',
                        's.name_en as subject_name_en',
                    ])
                    ->get()
                    ->groupBy(function ($m) {
                        return (int) $m->result_details_id;
                    });

                $detailsWithMarks = $details->map(function ($d) use ($marks, $subjectIds) {
                    $arr = (array) $d;
                    $mks = $marks[(int) $d->id] ?? collect();

                    $bySub = $mks->keyBy(function ($m) {
                        return (int) $m->subject_id;
                    });

                    $finalMarks = [];
                    foreach ($subjectIds as $sid) {
                        if (isset($bySub[(int) $sid])) {
                            $m = $bySub[(int) $sid];
                            $finalMarks[] = [
                                'id' => $m->id,
                                'result_details_id' => $m->result_details_id,
                                'subject_id' => $m->subject_id,
                                'ct_mark' => $m->ct_mark,
                                'cq_mark' => $m->cq_mark,
                                'mcq_mark' => $m->mcq_mark,
                                'practical_mark' => $m->practical_mark,
                                'obtained_mark' => $m->obtained_mark,
                                'total_mark' => $m->total_mark,
                                'gpa' => $m->gpa,
                                'letter_grade' => $m->letter_grade,
                                'additional_subject' => $m->additional_subject,
                                'is_absent' => $m->is_absent,
                                'sorting' => $m->sorting,
                                'subject' => ['id' => $m->subject_id, 'name_en' => $m->subject_name_en, 'is_child' => null],
                            ];
                        } else {
                            $finalMarks[] = [
                                'result_details_id' => $d->id,
                                'subject_id' => $sid,
                                'ct_mark' => null,
                                'cq_mark' => null,
                                'mcq_mark' => null,
                                'practical_mark' => null,
                                'obtained_mark' => null,
                                'total_mark' => null,
                                'gpa' => 0,
                                'letter_grade' => 'F',
                                'additional_subject' => 0,
                                'is_absent' => 0,
                            ];
                        }
                    }

                    $arr['marks'] = $finalMarks;
                    return $arr;
                });

                $resultDetailsDict = $detailsWithMarks->keyBy(function ($d) {
                    return (int) ($d['student_id'] ?? 0);
                });
            }
        }

        $lookupSubjectId = (int) (DB::table('subjects')->where('id', $subjectID)->value('parent_id') ?: $subjectID);

        $subjectMarks = DB::table('subject_assign_details as d')
            ->join('subjects as s', 's.id', '=', 'd.subject_id')
            ->where('d.subject_assign_id', $subjectAssign->id)
            ->where('d.subject_id', $lookupSubjectId)
            ->select([
                'd.subject_id',
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
                's.name_en as subject_name_en',
            ])
            ->first();

        if (!$subjectMarks) {
            return response()->json(['message' => 'Subject not assign for this class'], 422);
        }

        $studentsQuery = DB::table('students')
            ->select('id', 'student_id', 'name', 'college_roll')
            ->where([
                'academic_session_id' => $request->input('academic_session_id'),
                'department_id' => $request->input('department_id'),
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'academic_class_id' => $request->input('academic_class_id'),
                'status' => 'active',
            ])
            ->orderByRaw('CAST(college_roll as UNSIGNED) asc');

        if (!empty($selectedStudent) && !$isCommonSubject && Schema::hasTable('student_subject_assigns')) {
            $pluck = DB::table('student_subject_assigns')->where([
                'department_id' => $request->input('department_id'),
                'academic_class_id' => $request->input('academic_class_id'),
                'subject_id' => $checkSubjectId,
            ])->pluck('student_id')->toArray();

            if (!empty($pluck)) {
                $studentsQuery->whereIn('id', $pluck);
            } else {
                $studentsQuery->whereRaw('1=0');
            }
        }

        $students = $studentsQuery->get();

        $exam = null;
        if (Schema::hasTable('exams')) {
            $exam = DB::table('exams')->where('id', $examID)->first();
        }
        $classTestId = (int) ($exam->class_test_exam_id ?? 0);

        $classTest = null;
        if (!empty($classTestId) && Schema::hasTable('class_test_results')) {
            $classTest = DB::table('class_test_results')->where([
                'academic_session_id' => $request->input('academic_session_id'),
                'department_id' => $request->input('department_id'),
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'academic_class_id' => $request->input('academic_class_id'),
                'exam_id' => $classTestId,
                'status' => 'published',
            ])->first();
        }

        $finalDetails = [];
        $studentsWithout = [];
        foreach ($students as $std) {
            if ($resultDetailsDict->has((int) $std->id)) {
                $detail = $resultDetailsDict->get((int) $std->id);
                $detail['student'] = [
                    'id' => $std->id,
                    'student_id' => $std->student_id,
                    'name' => $std->name,
                    'college_roll' => $std->college_roll,
                ];
                $finalDetails[] = $detail;
            } else {
                $studentsWithout[] = [
                    'id' => $std->id,
                    'student_id' => $std->student_id,
                    'name' => $std->name,
                    'college_roll' => $std->college_roll,
                ];
            }
        }

        if (!empty($studentsWithout)) {
            $formatted = $this->marksFormat($studentsWithout, $subjectMarks, $classTest, $subjectIds);
            $finalDetails = array_merge($finalDetails, $formatted);
        }

        usort($finalDetails, function ($a, $b) {
            $rollA = $a['student']['college_roll'] ?? '';
            $rollB = $b['student']['college_roll'] ?? '';

            if ($rollA === $rollB) {
                return 0;
            }
            if (empty($rollA)) {
                return 1;
            }
            if (empty($rollB)) {
                return -1;
            }
            return ((int) $rollA) - ((int) $rollB);
        });

        return response()->json([
            'details' => $finalDetails,
            'class_test_marks_added' => !empty($classTest) ? 1 : 0,
            'child_enabled' => $childEnabled ? 1 : 0,
            'subject_ids' => array_values(array_map('intval', $subjectIds)),
            'effective_parent_subject_id' => (int) $effectiveParentId,
            'child_subjects' => $childSubjectsPayload,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => ['required', 'array'],
            'details' => ['required'],
        ]);

        if (!Schema::hasTable('results') || !Schema::hasTable('result_details') || !Schema::hasTable('result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $type = (string) ($request->input('type') ?? 'single');
        $subject = $request->input('subject');
        $details = $request->input('details');

        if ($type === 'all' && is_array($details)) {
            foreach ($details as $d) {
                $this->singleResultUpdate($id, $d, $subject, $request);
            }
        } else {
            if (!is_array($details)) {
                return response()->json(['message' => 'Invalid payload'], 422);
            }
            $this->singleResultUpdate($id, $details, $subject, $request);
        }

        $this->resultGradeSync($id);
        $this->meritPositionSync($id, 'merit_position_in_department');
        $this->meritPositionSync($id, 'merit_position_in_class');

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('results')) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $row = DB::table('results')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (($row->status ?? '') === 'deactive') {
            DB::table('results')->where('id', $id)->delete();
        } else {
            DB::table('results')->where('id', $id)->update(['status' => 'deactive', 'updated_at' => now()]);
        }

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function published(Request $request)
    {
        $request->validate([
            'result_id' => ['required'],
            'published_date' => ['nullable'],
        ]);

        if (!Schema::hasTable('results')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $result = DB::table('results')->where('id', $request->input('result_id'))->first();
        if (!$result) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $status = ($result->status ?? 'draft') === 'draft' ? 'published' : 'draft';
        $publishedDate = $request->input('published_date');
        $publishedDate = !empty($publishedDate) ? date('Y-m-d', strtotime((string) $publishedDate)) : null;

        DB::table('results')->where('id', $result->id)->update([
            'status' => $status,
            'published_date' => $publishedDate,
            'updated_at' => now(),
        ]);

        $msg = $status === 'published' ? 'Published Successfully!' : 'Unpublished Successfully!';
        return response()->json(['message' => $msg], 200);
    }

    public function syncResult(Request $request, $id)
    {
        if (!Schema::hasTable('results') || !Schema::hasTable('result_details') || !Schema::hasTable('result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $result = DB::table('results')->where('id', $id)->first();
        if (!$result) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $this->resultGradeSync($id);
        $this->meritPositionSync($id, 'merit_position_in_department');
        $this->meritPositionSync($id, 'merit_position_in_class');

        return response()->json(['message' => 'Result Sync Successfully!'], 200);
    }

    public function syncSubject(Request $request, $id)
    {
        if (!Schema::hasTable('results') || !Schema::hasTable('result_details') || !Schema::hasTable('result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $result = DB::table('results')->where('id', $id)->first();
        if (!$result) {
            return response()->json(['message' => 'Invalid result'], 422);
        }

        if (!Schema::hasTable('subject_assigns') || !Schema::hasTable('subject_assign_details') || !Schema::hasTable('student_subject_assigns') || !Schema::hasTable('subjects')) {
            return response()->json(['message' => 'Subject module not ready'], 422);
        }

        $deleted = 0;

        DB::transaction(function () use ($result, &$deleted) {
            $subjectAssign = DB::table('subject_assigns')->where([
                'department_id' => $result->department_id,
                'academic_qualification_id' => $result->academic_qualification_id,
                'academic_class_id' => $result->academic_class_id,
            ])->first();

            if (!$subjectAssign) {
                abort(422, 'Subject not assign for this class');
            }

            $commonSubjectIds = DB::table('subject_assign_details')
                ->where('subject_assign_id', $subjectAssign->id)
                ->where('main_subject', 0)
                ->where('fourth_subject', 0)
                ->pluck('subject_id')
                ->toArray();

            $details = DB::table('result_details')->where('result_id', $result->id)->select('id', 'student_id')->get();

            foreach ($details as $detail) {
                $assignedSubjectIds = DB::table('student_subject_assigns')->where([
                    'department_id' => $result->department_id,
                    'academic_class_id' => $result->academic_class_id,
                    'student_id' => $detail->student_id,
                ])->pluck('subject_id')->toArray();

                $allowed = array_values(array_unique(array_merge($commonSubjectIds, $assignedSubjectIds)));

                if (!empty($allowed)) {
                    $childIds = DB::table('subjects')->whereIn('parent_id', $allowed)->pluck('id')->toArray();
                    $allowed = array_values(array_unique(array_merge($allowed, $childIds)));
                }

                if (empty($allowed)) {
                    $deleted += DB::table('result_marks')->where('result_details_id', $detail->id)->delete();
                    continue;
                }

                $deleted += DB::table('result_marks')->where('result_details_id', $detail->id)->whereNotIn('subject_id', $allowed)->delete();
            }
        });

        return response()->json([
            'message' => 'Subject sync completed',
            'deleted' => $deleted,
        ], 200);
    }

    public function marksheet($id, string $mode = 'view')
    {
        if (!Schema::hasTable('result_details') || !Schema::hasTable('result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $baseQuery = DB::table('result_details as d')
            ->join('students as std', 'std.id', '=', 'd.student_id')
            ->join('results as r', 'r.id', '=', 'd.result_id')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->select([
                'd.id as detail_id',
                'd.result_id',
                'd.student_id',
                'd.total_mark_without_additional',
                'd.gpa_without_additional',
                'd.total_mark',
                'd.gpa',
                'd.letter_grade',
                'd.result_status',
                'd.merit_position_in_department',
                'd.merit_position_in_class',
                'std.student_id as software_id',
                'std.name as student_name',
                'std.mobile as student_mobile',
                'std.college_roll as student_college_roll',
                'std.fathers_name as student_fathers_name',
                'std.mothers_name as student_mothers_name',
                'std.reg_no as student_reg_no',
                'std.student_type as student_type',
                'r.academic_session_id',
                'r.department_id',
                'r.academic_qualification_id',
                'r.academic_class_id',
                'r.exam_id',
                'r.total_exam_subjects',
                'r.published_date',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
                'ex.name as exam_name',
            ]);

        $detail = (clone $baseQuery)->where('d.id', $id)->first();
        if (!$detail) {
            $detail = (clone $baseQuery)->where('d.student_id', $id)->orderByDesc('d.id')->first();
        }

        if (!$detail) {
            return response()->json(['message' => 'Marksheet not found'], 404);
        }

        $marks = [];
        if (Schema::hasTable('subjects')) {
            $marks = DB::table('result_marks as m')
                ->join('subjects as sub', 'sub.id', '=', 'm.subject_id')
                ->leftJoin('subjects as psub', 'psub.id', '=', 'sub.parent_id')
                ->select([
                    'm.subject_id',
                    'm.ct_mark',
                    'm.cq_mark',
                    'm.mcq_mark',
                    'm.practical_mark',
                    'm.obtained_mark',
                    'm.total_mark',
                    'm.gpa',
                    'm.letter_grade',
                    'm.additional_subject',
                    'm.is_absent',
                    'm.sorting',
                    'sub.name_en as subject_name_en',
                    'sub.is_child as subject_is_child',
                    'sub.parent_id as subject_parent_id',
                    'psub.name_en as parent_subject_name_en',
                ])
                ->where('m.result_details_id', $detail->detail_id)
                ->orderBy('m.sorting')
                ->orderBy('m.id')
                ->get()
                ->map(function ($m) {
                    return [
                        'subject_id' => $m->subject_id,
                        'ct_mark' => $m->ct_mark,
                        'cq_mark' => $m->cq_mark,
                        'mcq_mark' => $m->mcq_mark,
                        'practical_mark' => $m->practical_mark,
                        'obtained_mark' => $m->obtained_mark,
                        'total_mark' => $m->total_mark,
                        'gpa' => $m->gpa,
                        'letter_grade' => $m->letter_grade,
                        'additional_subject' => $m->additional_subject,
                        'is_absent' => $m->is_absent,
                        'sorting' => $m->sorting,
                        'subject' => [
                            'id' => $m->subject_id,
                            'name_en' => $m->subject_name_en,
                            'is_child' => $m->subject_is_child,
                            'parent_id' => $m->subject_parent_id,
                            'parent_name_en' => $m->parent_subject_name_en,
                        ],
                    ];
                })
                ->values()
                ->all();
        }

        $assignDetailsBySubjectId = [];
        if (Schema::hasTable('subject_assigns') && Schema::hasTable('subject_assign_details')) {
            $subjectAssign = DB::table('subject_assigns')
                ->where('academic_qualification_id', $detail->academic_qualification_id ?? null)
                ->where('department_id', $detail->department_id ?? null)
                ->where('academic_class_id', $detail->academic_class_id ?? null)
                ->first();

            if ($subjectAssign) {
                $assignDetailsBySubjectId = DB::table('subject_assign_details')
                    ->where('subject_assign_id', $subjectAssign->id)
                    ->get()
                    ->keyBy(function ($r) {
                        return (int) $r->subject_id;
                    })
                    ->all();
            }
        }

        $fourthSubjectId = null;
        if (Schema::hasTable('student_subject_assigns')) {
            $fourthSubjectId = (int) (DB::table('student_subject_assigns')
                ->where('student_id', $detail->student_id)
                ->where('department_id', $detail->department_id ?? null)
                ->where('academic_class_id', $detail->academic_class_id ?? null)
                ->where('main_subject', 0)
                ->value('subject_id') ?? 0);
            if ($fourthSubjectId <= 0) {
                $fourthSubjectId = null;
            }
        }

        $hasCtMark = empty($assignDetailsBySubjectId);
        $marks = $this->normalizeMarksheetMarks($marks, $assignDetailsBySubjectId, $fourthSubjectId, $hasCtMark, $mode);

        return response()->json([
            'id' => $detail->detail_id,
            'student' => [
                'id' => $detail->student_id,
                'student_id' => $detail->software_id,
                'name' => $detail->student_name,
                'mobile' => $detail->student_mobile,
                'college_roll' => $detail->student_college_roll,
                'fathers_name' => $detail->student_fathers_name,
                'mothers_name' => $detail->student_mothers_name,
                'reg_no' => $detail->student_reg_no,
                'student_type' => $detail->student_type,
            ],
            'result_details' => [
                'total_mark_without_additional' => $detail->total_mark_without_additional,
                'total_mark' => $detail->total_mark,
                'gpa_without_additional' => $detail->gpa_without_additional,
                'gpa' => $detail->gpa,
                'letter_grade' => $detail->letter_grade,
                'result_status' => $detail->result_status,
                'merit_position_in_department' => $detail->merit_position_in_department,
                'merit_position_in_class' => $detail->merit_position_in_class,
            ],
            'marks' => $marks,
            'result' => [
                'academic_session' => ['id' => $detail->academic_session_id, 'name' => $detail->academic_session_name],
                'qualification' => ['id' => $detail->academic_qualification_id, 'name' => $detail->academic_qualification_name],
                'department' => ['id' => $detail->department_id, 'name' => $detail->department_name],
                'academic_class' => ['id' => $detail->academic_class_id, 'name' => $detail->academic_class_name],
                'exam' => ['id' => $detail->exam_id, 'name' => $detail->exam_name],
                'total_exam_subjects' => $detail->total_exam_subjects,
                'published_date' => $detail->published_date,
                'has_ct_mark' => $hasCtMark,
            ],
        ]);
    }

    private function marksFormat(array $students, object $subjectMarks, ?object $classTest, array $subjectIds): array
    {
        $details = [];

        $ctMarkByStudentId = [];
        if ($classTest && Schema::hasTable('class_test_result_details') && Schema::hasTable('class_test_result_marks')) {
            $ids = array_map(fn($s) => (int) ($s['id'] ?? 0), $students);
            $ids = array_values(array_filter($ids));

            if (!empty($ids)) {
                $detailMap = DB::table('class_test_result_details')
                    ->where('class_test_result_id', $classTest->id)
                    ->whereIn('student_id', $ids)
                    ->pluck('id', 'student_id')
                    ->all();

                $detailIds = array_values(array_map('intval', $detailMap));
                if (!empty($detailIds)) {
                    $marks = DB::table('class_test_result_marks')
                        ->whereIn('class_test_result_details_id', $detailIds)
                        ->where('subject_id', (int) $subjectMarks->subject_id)
                        ->select('class_test_result_details_id', 'mark')
                        ->get()
                        ->keyBy(function ($m) {
                            return (int) $m->class_test_result_details_id;
                        });

                    foreach ($detailMap as $studentId => $detailId) {
                        $ctMarkByStudentId[(int) $studentId] = $marks[(int) $detailId]->mark ?? '';
                    }
                }
            }
        }

        foreach ($students as $student) {
            $ctMark = $ctMarkByStudentId[(int) $student['id']] ?? '';

            $marks = [];
            foreach ($subjectIds as $sid) {
                $marks[] = [
                    'subject' => [
                        'name_en' => $subjectMarks->subject_name_en ?? '',
                    ],
                    'subject_id' => (int) $sid,
                    'ct_mark' => $ctMark,
                    'cq_mark' => '',
                    'mcq_mark' => '',
                    'practical_mark' => '',
                    'obtained_mark' => '',
                    'total_mark' => '',
                    'gpa' => '',
                    'letter_grade' => '',
                    'is_absent' => 0,
                ];
            }

            $details[] = [
                'student' => [
                    'student_id' => $student['student_id'] ?? '',
                    'name' => $student['name'] ?? '',
                    'college_roll' => $student['college_roll'] ?? '',
                ],
                'student_id' => (int) ($student['id'] ?? 0),
                'total_mark' => 0,
                'gpa' => 0,
                'letter_grade' => 0,
                'result_status' => 'FAILED',
                'marks' => $marks,
            ];
        }

        return $details;
    }

    private function singleResultUpdate(int $resultId, array $details, array $subject, Request $request): void
    {
        $studentId = (int) ($details['student_id'] ?? 0);
        if (empty($studentId)) {
            return;
        }

        $marksList = $details['marks'] ?? [];
        $firstMark = [];
        if (is_array($marksList)) {
            $firstMark = (is_array($marksList[0] ?? null) ? $marksList[0] : []);
        }

        $subjectId = (int) ($subject['subject_id'] ?? $subject['id'] ?? ($firstMark['subject_id'] ?? 0));
        if (empty($subjectId)) {
            return;
        }

        $marks = $firstMark;
        if (is_array($marksList)) {
            foreach ($marksList as $m) {
                if (!is_array($m)) continue;
                if ((int) ($m['subject_id'] ?? 0) === $subjectId) {
                    $marks = $m;
                    break;
                }
            }
        }

        $admin = Auth::guard('admin')->user();
        $ip = $request->ip();

        $result = DB::table('results')->where('id', $resultId)->first();
        if (!$result) {
            return;
        }

        $detailRow = DB::table('result_details')->where(['result_id' => $resultId, 'student_id' => $studentId])->first();
        $detailId = $detailRow ? (int) $detailRow->id : (int) DB::table('result_details')->insertGetId([
            'result_id' => $resultId,
            'student_id' => $studentId,
            'total_mark_without_additional' => 0,
            'total_mark' => 0,
            'gpa_without_additional' => 0,
            'gpa' => 0,
            'letter_grade' => 'F',
            'result_status' => 'FAILED',
        ]);

        $additionalSubject = 0;
        if (Schema::hasTable('student_subject_assigns')) {
            $opt = DB::table('student_subject_assigns')->where([
                'department_id' => $result->department_id,
                'academic_class_id' => $result->academic_class_id,
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'main_subject' => 0,
            ])->first();
            $additionalSubject = $opt ? 1 : 0;
        }

        $passMarks = [
            'ct_pass' => $subject['ct_pass_mark'] ?? 0,
            'cq_pass' => $subject['cq_pass_mark'] ?? 0,
            'mcq_pass' => $subject['mcq_pass_mark'] ?? 0,
            'practical_pass' => $subject['practical_pass_mark'] ?? 0,
        ];

        $isAbsent = !empty($marks['is_absent']) ? 1 : 0;

        $ct = (float) ($marks['ct_mark'] ?? 0);
        $cq = (float) ($marks['cq_mark'] ?? 0);
        $mcq = (float) ($marks['mcq_mark'] ?? 0);
        $prac = (float) ($marks['practical_mark'] ?? 0);

        $obtainedRaw = $cq + $mcq + $prac;
        $obtained = $request->boolean('is_convert') ? ($obtainedRaw * 80 / 100) : $obtainedRaw;
        $total = $obtained + $ct;

        $letterGrade = null;
        $gpa = 0;

        if ($isAbsent) {
            $letterGrade = 'ABS';
            $gpa = 0;
        } else {
            $failed = false;
            $ctPass = (float) ($passMarks['ct_pass'] ?? 0);
            $cqPass = (float) ($passMarks['cq_pass'] ?? 0);
            $mcqPass = (float) ($passMarks['mcq_pass'] ?? 0);
            $pracPass = (float) ($passMarks['practical_pass'] ?? 0);

            if ($ctPass > 0 && $ct < $ctPass) $failed = true;
            if ($cqPass > 0 && $cq < $cqPass) $failed = true;
            if ($mcqPass > 0 && $mcq < $mcqPass) $failed = true;
            if ($pracPass > 0 && $prac < $pracPass) $failed = true;

            if ($failed) {
                $letterGrade = 'F';
                $gpa = 0;
            } else {
                if (Schema::hasTable('grade_management')) {
                    $row = DB::table('grade_management')
                        ->where('from_mark', '<=', $total)
                        ->where('to_mark', '>=', $total)
                        ->first();
                    if ($row) {
                        $letterGrade = $row->grade;
                        $gpa = (float) ($row->gpa ?? 0);
                    } else {
                        $letterGrade = 'F';
                        $gpa = 0;
                    }
                } else {
                    $letterGrade = 'F';
                    $gpa = 0;
                }
            }
        }

        $payload = [
            'result_details_id' => $detailId,
            'subject_id' => $subjectId,
            'ct_mark' => $marks['ct_mark'] ?? null,
            'cq_mark' => $marks['cq_mark'] ?? null,
            'mcq_mark' => $marks['mcq_mark'] ?? null,
            'practical_mark' => $marks['practical_mark'] ?? null,
            'obtained_mark' => number_format($obtained, 2, '.', ''),
            'total_mark' => number_format($total, 2, '.', ''),
            'gpa' => $gpa,
            'letter_grade' => $letterGrade,
            'is_absent' => $isAbsent,
            'pass_marks' => json_encode($passMarks),
            'additional_subject' => $additionalSubject,
            'updated_by' => $admin->name ?? null,
            'updated_ip' => $ip,
            'updated_at' => now(),
        ];

        $existing = DB::table('result_marks')->where(['result_details_id' => $detailId, 'subject_id' => $subjectId])->first();
        if ($existing) {
            DB::table('result_marks')->where('id', $existing->id)->update($payload);
        } else {
            $payload['created_by'] = $admin->name ?? null;
            $payload['created_ip'] = $ip;
            $payload['created_at'] = now();
            DB::table('result_marks')->insert($payload);
        }
    }

    private function resultGradeSync(int $resultId): void
    {
        if (!Schema::hasTable('result_details') || !Schema::hasTable('result_marks') || !Schema::hasTable('subjects')) {
            return;
        }

        $result = DB::table('results')->where('id', $resultId)->first();
        if (!$result) {
            return;
        }

        $detailRows = DB::table('result_details')->where('result_id', $resultId)->select('id')->get();
        $detailIds = $detailRows->pluck('id')->map(fn($v) => (int) $v)->values()->all();
        if (empty($detailIds)) {
            return;
        }

        foreach ($detailIds as $detailId) {
            $baseQuery = DB::table('result_marks as m')
                ->join('subjects as s', 's.id', '=', 'm.subject_id')
                ->where('m.result_details_id', $detailId)
                ->where('s.is_child', 0);

            $count = (clone $baseQuery)->count();
            if ($count === 0) {
                continue;
            }

            $totalMark = (float) ((clone $baseQuery)->sum('m.total_mark') ?? 0);
            $totalGpa = (float) ((clone $baseQuery)->where('m.additional_subject', '!=', 1)->sum('m.gpa') ?? 0);
            $totalSubject = (int) ((clone $baseQuery)->where('m.additional_subject', '!=', 1)->count('m.subject_id') ?? 0);
            $totalAbsent = (int) ((clone $baseQuery)->where('m.additional_subject', '!=', 1)->where('m.is_absent', 1)->count('m.subject_id') ?? 0);

            if ($totalSubject > 0 && $totalSubject === $totalAbsent) {
                DB::table('result_details')->where('id', $detailId)->update([
                    'total_mark' => 0,
                    'result_status' => 'ABSENT',
                    'letter_grade' => 'ABS',
                    'gpa' => 0,
                    'total_mark_without_additional' => 0,
                    'gpa_without_additional' => 0,
                ]);
                continue;
            }

            $addt = (clone $baseQuery)->where('m.additional_subject', 1)->first();
            $addtTotalMark = (float) ($addt->total_mark ?? 0);
            $totalMarkWithoutAdditional = $totalMark - $addtTotalMark;

            $updateArr = [
                'total_mark' => $totalMark,
                'result_status' => 'FAILED',
                'letter_grade' => 'F',
                'gpa' => 0,
                'total_mark_without_additional' => 0,
                'gpa_without_additional' => 0,
            ];

            if ($totalAbsent > 0) {
                DB::table('result_details')->where('id', $detailId)->update($updateArr);
                continue;
            }

            $failed = (clone $baseQuery)->where('m.additional_subject', '!=', 1)->where('m.letter_grade', 'F')->first();
            $gradeEmpty = (clone $baseQuery)->where('m.additional_subject', '!=', 1)->whereNull('m.letter_grade')->first();
            $gpaEmpty = (clone $baseQuery)->where('m.additional_subject', '!=', 1)->where('m.gpa', 0)->first();

            if ($failed || $gradeEmpty || $gpaEmpty) {
                DB::table('result_details')->where('id', $detailId)->update($updateArr);
                continue;
            }

            $totalGpaWithoutAdditional = 0;
            if ($totalGpa > 0 && $totalSubject > 0) {
                $totalGpaWithoutAdditional = $totalGpa / $totalSubject;
            }

            $addtGpa = (float) ($addt->gpa ?? 0);
            if ($addtGpa > 2) {
                $gpa = 0;
                if ($totalSubject > 0) {
                    $add = $addtGpa - 2;
                    $gpa = ($totalGpa + $add) / $totalSubject;
                    $gpa = $gpa > 5 ? 5 : $gpa;
                }
            } else {
                $gpa = $totalGpaWithoutAdditional;
            }

            $totalSubWithAddt = (int) ((clone $baseQuery)->count('m.subject_id') ?? 0);
            $letterGrade = null;
            if (Schema::hasTable('grade_management')) {
                $letterGrade = DB::table('grade_management')->where('from_gpa', '<=', $gpa)->where('to_gpa', '>=', $gpa)->value('grade');
            }

            if ($totalSubWithAddt >= (int) ($result->total_exam_subjects ?? 0)) {
                $updateArr = [
                    'total_mark' => $totalMark,
                    'result_status' => 'PASSED',
                    'letter_grade' => $letterGrade,
                    'gpa' => $gpa,
                    'total_mark_without_additional' => $totalMarkWithoutAdditional,
                    'gpa_without_additional' => $totalGpaWithoutAdditional,
                ];
            }

            DB::table('result_details')->where('id', $detailId)->update($updateArr);
        }
    }

    private function meritPositionSync(int $resultId, string $type): void
    {
        if (!Schema::hasTable('result_details') || !Schema::hasTable('results')) {
            return;
        }

        $result = DB::table('results')->where('id', $resultId)->first();
        if (!$result) {
            return;
        }

        $resetQuery = DB::table('result_details as rd')
            ->join('results as rs', 'rs.id', '=', 'rd.result_id')
            ->where('rs.academic_session_id', $result->academic_session_id)
            ->where('rs.academic_qualification_id', $result->academic_qualification_id)
            ->where('rs.exam_id', $result->exam_id);

        if ($type === 'merit_position_in_department' || $type === 'merit_position_in_class') {
            $resetQuery->where('rs.department_id', $result->department_id);
        }

        if ($type === 'merit_position_in_class') {
            $resetQuery->where('rs.academic_class_id', $result->academic_class_id);
        }

        $resetIds = $resetQuery->select('rd.id')->pluck('rd.id')->toArray();
        if (!empty($resetIds)) {
            DB::table('result_details')->whereIn('id', $resetIds)->update([$type => null]);
        }

        $query = DB::table('result_details as rd')
            ->join('results as rs', 'rs.id', '=', 'rd.result_id')
            ->where('rs.academic_session_id', $result->academic_session_id)
            ->where('rs.academic_qualification_id', $result->academic_qualification_id)
            ->where('rs.exam_id', $result->exam_id)
            ->where('rd.result_status', 'PASSED')
            ->orderByDesc('rd.gpa')
            ->orderByDesc('rd.total_mark');

        if ($type === 'merit_position_in_department' || $type === 'merit_position_in_class') {
            $query->where('rs.department_id', $result->department_id);
        }

        if ($type === 'merit_position_in_class') {
            $query->where('rs.academic_class_id', $result->academic_class_id);
        }

        $rows = $query->select('rd.id')->get();
        foreach ($rows as $idx => $row) {
            DB::table('result_details')->where('id', $row->id)->update([$type => $idx + 1]);
        }
    }
}
