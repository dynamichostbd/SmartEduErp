<?php

namespace App\Http\Controllers\Backend\Result;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassTestResultController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('class_test_results')) {
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

        $query = DB::table('class_test_results as r')
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
                'r.exam_date',
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

        $field = (string) ($request->input('field_name') ?? '');
        $value = $request->input('value');
        if ($value !== null && $value !== '') {
            $allowed = [
                'status' => 'r.status',
                'exam_date' => 'r.exam_date',
                'published_date' => 'r.published_date',
            ];
            $col = $allowed[$field] ?? null;
            if ($col) {
                $query->where($col, 'like', '%' . $value . '%');
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

    public function store(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'exam_id' => ['required'],
            'exam_date' => ['required'],
        ]);

        if (!Schema::hasTable('class_test_results')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $payload = [
            'academic_session_id' => $request->input('academic_session_id'),
            'department_id' => $request->input('department_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'academic_class_id' => $request->input('academic_class_id'),
            'exam_id' => $request->input('exam_id'),
        ];

        $examDate = (string) $request->input('exam_date');
        $payloadInsert = $payload + [
            'exam_date' => date('Y-m-d', strtotime($examDate)),
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $existing = DB::table('class_test_results')->where($payload)->first();
        if ($existing) {
            return response()->json(['message' => 'Create Successfully!', 'id' => $existing->id], 200);
        }

        $id = DB::table('class_test_results')->insertGetId($payloadInsert);
        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function show(Request $request, $id)
    {
        if (!Schema::hasTable('class_test_results')) {
            return response()->json(null, 404);
        }

        $result = DB::table('class_test_results as r')
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

        if (!$result) {
            return response()->json(null, 404);
        }

        if ($request->boolean('result_view')) {
            $searchType = (string) ($request->input('type') ?? '');
            $field = (string) ($request->input('field_name') ?? '');
            $value = (string) ($request->input('value') ?? '');

            $detailsQuery = DB::table('class_test_result_details as d')
                ->join('students as std', 'std.id', '=', 'd.student_id')
                ->select([
                    'd.id',
                    'd.class_test_result_id',
                    'd.student_id',
                    'd.total_mark',
                    'd.result_status',
                    'std.student_id as software_id',
                    'std.name',
                    'std.mobile',
                    'std.college_roll',
                ])
                ->where('d.class_test_result_id', $result->id);

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

            if ($searchType === 'merit') {
                $detailsQuery->where('d.result_status', 'PASSED')->orderByDesc('d.total_mark');
            } elseif ($searchType === 'unmerit') {
                $detailsQuery->where('d.result_status', 'FAILED')->orderBy('d.total_mark', 'asc');
            } else {
                $detailsQuery->orderByRaw('CAST(std.college_roll as UNSIGNED) asc');
            }

            $details = $detailsQuery->get();

            $failedMarksByDetailId = [];
            if ($searchType === 'unmerit' && Schema::hasTable('class_test_result_marks') && Schema::hasTable('subjects')) {
                $detailIds = $details->pluck('id')->filter()->values()->all();
                if (!empty($detailIds)) {
                    $marks = DB::table('class_test_result_marks as m')
                        ->join('subjects as sub', 'sub.id', '=', 'm.subject_id')
                        ->select([
                            'm.class_test_result_details_id',
                            'm.subject_id',
                            'm.mark',
                            'm.pass_mark',
                            'm.exam_mark',
                            'm.result_status',
                            'sub.name_en as subject_name_en',
                        ])
                        ->whereIn('m.class_test_result_details_id', $detailIds)
                        ->where('m.result_status', 'FAILED')
                        ->get();

                    foreach ($marks as $m) {
                        $failedMarksByDetailId[$m->class_test_result_details_id] ??= [];
                        $failedMarksByDetailId[$m->class_test_result_details_id][] = [
                            'subject_id' => $m->subject_id,
                            'subject' => ['id' => $m->subject_id, 'name_en' => $m->subject_name_en],
                            'mark' => $m->mark,
                            'pass_mark' => $m->pass_mark,
                            'exam_mark' => $m->exam_mark,
                            'result_status' => $m->result_status,
                        ];
                    }
                }
            }

            $detailsPayload = $details->map(function ($d) use ($failedMarksByDetailId) {
                return [
                    'id' => $d->id,
                    'class_test_result_id' => $d->class_test_result_id,
                    'student_id' => $d->software_id,
                    'name' => $d->name,
                    'mobile' => $d->mobile,
                    'college_roll' => $d->college_roll,
                    'total_mark' => $d->total_mark,
                    'result_status' => $d->result_status,
                    'marks' => $failedMarksByDetailId[$d->id] ?? [],
                ];
            })->values()->all();

            $payload = [
                'id' => $result->id,
                'academic_session_id' => $result->academic_session_id,
                'department_id' => $result->department_id,
                'academic_qualification_id' => $result->academic_qualification_id,
                'academic_class_id' => $result->academic_class_id,
                'exam_id' => $result->exam_id,
                'exam_date' => $result->exam_date,
                'published_date' => $result->published_date,
                'status' => $result->status,
                'academic_session' => ['id' => $result->academic_session_id, 'name' => $result->academic_session_name],
                'qualification' => ['id' => $result->academic_qualification_id, 'name' => $result->academic_qualification_name],
                'department' => ['id' => $result->department_id, 'name' => $result->department_name],
                'academic_class' => ['id' => $result->academic_class_id, 'name' => $result->academic_class_name],
                'exam' => ['id' => $result->exam_id, 'name' => $result->exam_name],
                'details' => $detailsPayload,
            ];

            $excelHeader = [
                'Session: ' . ($result->academic_session_name ?? ''),
                'Academic Level: ' . ($result->academic_qualification_name ?? ''),
                'Department/Group: ' . ($result->department_name ?? ''),
                'Academic Class: ' . ($result->academic_class_name ?? ''),
                'Exam Name: ' . ($result->exam_name ?? ''),
            ];

            return response()->json([
                'result' => $payload,
                'excel_header' => $excelHeader,
            ]);
        }

        return response()->json([
            'id' => $result->id,
            'academic_session_id' => $result->academic_session_id,
            'department_id' => $result->department_id,
            'academic_qualification_id' => $result->academic_qualification_id,
            'academic_class_id' => $result->academic_class_id,
            'exam_id' => $result->exam_id,
            'exam_date' => $result->exam_date,
            'published_date' => $result->published_date,
            'status' => $result->status,
            'academic_session_name' => $result->academic_session_name,
            'academic_qualification_name' => $result->academic_qualification_name,
            'department_name' => $result->department_name,
            'academic_class_name' => $result->academic_class_name,
            'exam_name' => $result->exam_name,
        ]);
    }

    public function studentsForMarksEntry(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'subject_id' => ['required'],
        ]);

        $subjectId = (int) $request->input('subject_id');
        $resultId = (int) ($request->input('result_id') ?? 0);
        $selectedStudent = (int) ($request->input('selected_student') ?? 0);

        if (!Schema::hasTable('students') || !Schema::hasTable('subject_assigns') || !Schema::hasTable('subject_assign_details')) {
            return response()->json(['message' => 'Subject not assign for this class'], 422);
        }

        $subjectAssign = DB::table('subject_assigns')
            ->where([
                'department_id' => $request->input('department_id'),
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'academic_class_id' => $request->input('academic_class_id'),
            ])
            ->first();

        if (!$subjectAssign) {
            return response()->json(['message' => 'Subject not assign for this class'], 422);
        }

        $subjectMarks = DB::table('subject_assign_details as d')
            ->join('subjects as s', 's.id', '=', 'd.subject_id')
            ->where('d.subject_assign_id', $subjectAssign->id)
            ->where('d.subject_id', $subjectId)
            ->select([
                'd.subject_id',
                'd.ct_mark',
                'd.ct_pass_mark',
                's.name_en as subject_name_en',
            ])
            ->first();

        if (!$subjectMarks) {
            return response()->json(['message' => 'Subject not assign for this class'], 422);
        }

        $existingDetails = [];
        $pluckStudents = [];

        if (!empty($resultId) && Schema::hasTable('class_test_result_details') && Schema::hasTable('class_test_result_marks')) {
            $existingRows = DB::table('class_test_result_details as d')
                ->join('students as std', 'std.id', '=', 'd.student_id')
                ->join('class_test_result_marks as m', function ($j) use ($subjectId) {
                    $j->on('m.class_test_result_details_id', '=', 'd.id')
                        ->where('m.subject_id', '=', $subjectId);
                })
                ->where('d.class_test_result_id', $resultId)
                ->select([
                    'd.id',
                    'd.student_id',
                    'd.total_mark',
                    'd.result_status',
                    'std.student_id as software_id',
                    'std.name',
                    'std.college_roll',
                    'm.subject_id',
                    'm.mark',
                    'm.exam_mark',
                    'm.pass_mark',
                    'm.result_status as mark_result_status',
                ])
                ->get();

            $existingDetails = $existingRows->map(function ($r) use ($subjectMarks) {
                return [
                    'id' => $r->id,
                    'student' => [
                        'student_id' => $r->software_id,
                        'name' => $r->name,
                        'college_roll' => $r->college_roll,
                    ],
                    'student_id' => $r->student_id,
                    'total_mark' => $r->total_mark,
                    'result_status' => $r->result_status,
                    'marks' => [[
                        'subject' => ['name_en' => $subjectMarks->subject_name_en ?? ''],
                        'subject_id' => $r->subject_id,
                        'mark' => $r->mark,
                        'exam_mark' => $r->exam_mark,
                        'pass_mark' => $r->pass_mark,
                        'result_status' => $r->mark_result_status,
                    ]],
                ];
            })->values()->all();

            $pluckStudents = $existingRows->pluck('student_id')->filter()->values()->all();
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

        if (!empty($pluckStudents)) {
            $studentsQuery->whereNotIn('id', $pluckStudents);
        }

        if (!empty($selectedStudent) && Schema::hasTable('student_subject_assigns')) {
            $subStudents = DB::table('student_subject_assigns')
                ->where([
                    'department_id' => $request->input('department_id'),
                    'academic_class_id' => $request->input('academic_class_id'),
                    'subject_id' => $subjectId,
                ])
                ->pluck('student_id')
                ->toArray();

            if (!empty($subStudents)) {
                $studentsQuery->whereIn('id', $subStudents);
            } else {
                $studentsQuery->whereRaw('1=0');
            }
        }

        $students = $studentsQuery->get()->toArray();

        $newDetails = [];
        foreach ($students as $student) {
            $newDetails[] = [
                'student' => [
                    'student_id' => $student->student_id ?? '',
                    'name' => $student->name ?? '',
                    'college_roll' => $student->college_roll ?? '',
                ],
                'student_id' => $student->id,
                'total_mark' => 0,
                'result_status' => 'FAILED',
                'marks' => [[
                    'subject' => ['name_en' => $subjectMarks->subject_name_en ?? ''],
                    'subject_id' => '',
                    'mark' => '',
                    'exam_mark' => $subjectMarks->ct_mark,
                    'pass_mark' => $subjectMarks->ct_pass_mark,
                ]],
            ];
        }

        $details = array_merge($existingDetails, $newDetails);

        usort($details, function ($a, $b) {
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

        return response()->json($details);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject_id' => ['required'],
            'details' => ['required', 'array'],
        ]);

        if (!Schema::hasTable('class_test_result_details') || !Schema::hasTable('class_test_result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $subjectId = (int) $request->input('subject_id');
        $details = $request->input('details');
        $studentId = $details['student_id'] ?? null;
        if (empty($studentId)) {
            return response()->json(['message' => 'Student not found'], 422);
        }

        $marks = $details['marks'][0] ?? [];
        $markVal = $marks['mark'] ?? null;

        $admin = Auth::guard('admin')->user();
        $ip = $request->ip();

        $detailRow = DB::table('class_test_result_details')
            ->where([
                'class_test_result_id' => $id,
                'student_id' => $studentId,
            ])
            ->first();

        if ($detailRow) {
            $detailId = $detailRow->id;
        } else {
            $detailId = DB::table('class_test_result_details')->insertGetId([
                'class_test_result_id' => $id,
                'student_id' => $studentId,
                'total_mark' => 0,
                'result_status' => 'FAILED',
            ]);
        }

        $passMark = $marks['pass_mark'] ?? null;
        $resultStatus = null;
        if ($markVal !== null && $markVal !== '') {
            $resultStatus = ((float) $markVal) < ((float) $passMark) ? 'FAILED' : 'PASSED';
        }

        $existingMark = DB::table('class_test_result_marks')
            ->where([
                'class_test_result_details_id' => $detailId,
                'subject_id' => $subjectId,
            ])
            ->first();

        $markPayload = [
            'class_test_result_details_id' => $detailId,
            'subject_id' => $subjectId,
            'mark' => $markVal,
            'exam_mark' => $marks['exam_mark'] ?? null,
            'pass_mark' => $passMark,
            'result_status' => $resultStatus,
            'updated_by' => $admin->name ?? null,
            'updated_ip' => $ip,
            'updated_at' => now(),
        ];

        if ($existingMark) {
            DB::table('class_test_result_marks')->where('id', $existingMark->id)->update($markPayload);
        } else {
            $markPayload['created_by'] = $admin->name ?? null;
            $markPayload['created_ip'] = $ip;
            $markPayload['created_at'] = now();
            DB::table('class_test_result_marks')->insert($markPayload);
        }

        $this->recalculateDetailStatus($detailId);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('class_test_results')) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $row = DB::table('class_test_results')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (($row->status ?? '') === 'deactive') {
            if (Schema::hasTable('class_test_result_details')) {
                $detailIds = DB::table('class_test_result_details')->where('class_test_result_id', $id)->pluck('id')->toArray();
                if (!empty($detailIds) && Schema::hasTable('class_test_result_marks')) {
                    DB::table('class_test_result_marks')->whereIn('class_test_result_details_id', $detailIds)->delete();
                }
                DB::table('class_test_result_details')->where('class_test_result_id', $id)->delete();
            }
            DB::table('class_test_results')->where('id', $id)->delete();
        } else {
            DB::table('class_test_results')->where('id', $id)->update(['status' => 'deactive', 'updated_at' => now()]);
        }

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function published(Request $request)
    {
        $request->validate([
            'result_id' => ['required'],
            'published_date' => ['nullable'],
        ]);

        if (!Schema::hasTable('class_test_results')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $result = DB::table('class_test_results')->where('id', $request->input('result_id'))->first();
        if (!$result) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $status = ($result->status ?? 'draft') === 'published' ? 'draft' : 'published';
        $publishedDate = $request->input('published_date');
        $publishedDate = !empty($publishedDate) ? date('Y-m-d', strtotime((string) $publishedDate)) : null;

        DB::table('class_test_results')->where('id', $result->id)->update([
            'status' => $status,
            'published_date' => $publishedDate,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Published Successfully!'], 200);
    }

    public function syncResult($id)
    {
        if (!Schema::hasTable('class_test_results') || !Schema::hasTable('class_test_result_details') || !Schema::hasTable('class_test_result_marks')) {
            return response()->json(['message' => 'Result module not ready'], 422);
        }

        $classTestResult = DB::table('class_test_results')->where('id', $id)->first();
        if (!$classTestResult) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $subjectAssign = null;
        if (Schema::hasTable('subject_assigns')) {
            $subjectAssign = DB::table('subject_assigns')->where([
                'academic_qualification_id' => $classTestResult->academic_qualification_id,
                'department_id' => $classTestResult->department_id,
                'academic_class_id' => $classTestResult->academic_class_id,
            ])->first();
        }

        if (!$subjectAssign || !Schema::hasTable('subject_assign_details')) {
            return response()->json(['message' => 'Result Sync Successfully!'], 200);
        }

        $passMarks = DB::table('subject_assign_details')
            ->where('subject_assign_id', $subjectAssign->id)
            ->pluck('ct_pass_mark', 'subject_id')
            ->all();

        $detailIds = DB::table('class_test_result_details')
            ->where('class_test_result_id', $classTestResult->id)
            ->pluck('id')
            ->toArray();

        if (empty($detailIds)) {
            return response()->json(['message' => 'Result Sync Successfully!'], 200);
        }

        $marks = DB::table('class_test_result_marks')
            ->whereIn('class_test_result_details_id', $detailIds)
            ->select('id', 'class_test_result_details_id', 'subject_id', 'mark')
            ->get();

        foreach ($marks as $m) {
            $pass = $passMarks[$m->subject_id] ?? null;
            if ($pass !== null && $m->mark !== null && $m->mark !== '') {
                $newStatus = ((float) $m->mark) < ((float) $pass) ? 'FAILED' : 'PASSED';
                DB::table('class_test_result_marks')->where('id', $m->id)->update(['result_status' => $newStatus, 'updated_at' => now()]);
            } else {
                DB::table('class_test_result_marks')->where('id', $m->id)->update(['result_status' => 'FAILED', 'updated_at' => now()]);
            }
        }

        foreach ($detailIds as $detailId) {
            $this->recalculateDetailStatus($detailId);
        }

        return response()->json(['message' => 'Result Sync Successfully!'], 200);
    }

    public function marksheet($id)
    {
        if (!Schema::hasTable('class_test_result_details') || !Schema::hasTable('class_test_result_marks')) {
            return response()->json([]);
        }

        $detail = DB::table('class_test_result_details as d')
            ->join('students as std', 'std.id', '=', 'd.student_id')
            ->join('class_test_results as r', 'r.id', '=', 'd.class_test_result_id')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'r.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'r.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'r.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'r.department_id')
            ->leftJoin('exams as ex', 'ex.id', '=', 'r.exam_id')
            ->select([
                'd.id as detail_id',
                'd.class_test_result_id',
                'd.student_id',
                'd.total_mark',
                'd.result_status',
                'std.student_id as software_id',
                'std.name as student_name',
                'std.mobile as student_mobile',
                'std.college_roll as student_college_roll',
                'r.academic_session_id',
                'r.department_id',
                'r.academic_qualification_id',
                'r.academic_class_id',
                'r.exam_id',
                'r.exam_date',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
                'ex.name as exam_name',
            ])
            ->where('d.id', $id)
            ->first();

        if (!$detail) {
            return response()->json([]);
        }

        $marks = [];
        if (Schema::hasTable('subjects')) {
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
                ->where('m.class_test_result_details_id', $detail->detail_id)
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
            'id' => $detail->detail_id,
            'student' => [
                'id' => $detail->student_id,
                'student_id' => $detail->software_id,
                'name' => $detail->student_name,
                'mobile' => $detail->student_mobile,
                'college_roll' => $detail->student_college_roll,
            ],
            'marks' => $marks,
            'class_test_result' => [
                'academic_session' => ['id' => $detail->academic_session_id, 'name' => $detail->academic_session_name],
                'qualification' => ['id' => $detail->academic_qualification_id, 'name' => $detail->academic_qualification_name],
                'department' => ['id' => $detail->department_id, 'name' => $detail->department_name],
                'academic_class' => ['id' => $detail->academic_class_id, 'name' => $detail->academic_class_name],
                'exam' => ['id' => $detail->exam_id, 'name' => $detail->exam_name],
            ],
        ]);
    }

    private function recalculateDetailStatus(int $detailId): void
    {
        $totalMark = (float) (DB::table('class_test_result_marks')->where('class_test_result_details_id', $detailId)->sum('mark') ?? 0);
        $failed = DB::table('class_test_result_marks')
            ->where('class_test_result_details_id', $detailId)
            ->where('result_status', 'FAILED')
            ->exists();

        DB::table('class_test_result_details')->where('id', $detailId)->update([
            'total_mark' => $totalMark,
            'result_status' => $failed ? 'FAILED' : 'PASSED',
        ]);
    }
}
