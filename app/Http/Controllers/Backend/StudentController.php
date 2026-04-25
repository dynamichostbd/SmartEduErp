<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStudentZip;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StudentController extends Controller
{
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

    protected function baseFilteredQuery(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $departmentId = $request->input('department_id') ?: ($admin->department_id ?? null);
        $hostelId = $request->input('hostel_id') ?: ($admin->hostel_id ?? null);

        $subjectIds = $request->input('subject_ids') ?: [];
        $fourthSubjectId = $request->input('fourth_subject_id') ?: null;
        $classId = $request->input('academic_class_id') ?: null;

        $query = Student::query();

        $ids = $request->input('ids');
        if (is_array($ids) && count($ids) > 0) {
            $query->whereIn('students.id', $ids);
        }

        $field = $request->input('field_name');
        $value = $request->input('value');

        if (!empty($field) && $value !== null && $value !== '') {
            if ($field === 'admission_id') {
                $query->where('students.' . $field, $value);
            } else {
                $query->where('students.' . $field, 'like', '%' . $value . '%');
            }
        }

        if ($request->filled('academic_qualification_id')) {
            $query->where('students.academic_qualification_id', $request->academic_qualification_id);
        }

        if ($request->filled('student_type')) {
            $query->where('students.student_type', $request->student_type);
        }

        if ($request->filled('status')) {
            $query->where('students.status', $request->status);
        }

        if ($request->filled('gender')) {
            $query->where('students.gender', $request->gender);
        }

        if ($request->filled('academic_class_id')) {
            $query->where('students.academic_class_id', $request->academic_class_id);
        }

        if ($request->filled('academic_session_id')) {
            $query->where('students.academic_session_id', $request->academic_session_id);
        }

        if ($departmentId !== null && $departmentId !== '') {
            $query->where('students.department_id', $departmentId);
        }

        if ($hostelId !== null && $hostelId !== '') {
            $query->where('students.hostel_id', $hostelId);
        }

        if (!empty($subjectIds) && is_array($subjectIds)) {
            $subjectIds = array_values(array_filter($subjectIds, fn($v) => $v !== null && $v !== ''));
            if (count($subjectIds) > 0) {
                $query->whereIn('students.id', function ($q) use ($subjectIds, $departmentId, $classId) {
                    $q->from('student_subject_assigns')
                        ->select('student_id')
                        ->when($classId, fn($sq) => $sq->where('academic_class_id', $classId))
                        ->when($departmentId, fn($sq) => $sq->where('department_id', $departmentId))
                        ->where('main_subject', 1)
                        ->whereIn('subject_id', $subjectIds)
                        ->groupBy('student_id')
                        ->havingRaw('COUNT(DISTINCT subject_id) = ?', [count($subjectIds)]);
                });
            }
        }

        if (!empty($fourthSubjectId)) {
            $query->whereExists(function ($q) use ($fourthSubjectId, $departmentId, $classId) {
                $q->from('student_subject_assigns')
                    ->whereColumn('student_subject_assigns.student_id', 'students.id')
                    ->when($classId, fn($sq) => $sq->where('academic_class_id', $classId))
                    ->when($departmentId, fn($sq) => $sq->where('department_id', $departmentId))
                    ->where('main_subject', 0)
                    ->where('subject_id', $fourthSubjectId);
            });
        }

        return $query;
    }

    protected function applyListJoinsAndSelect($query)
    {
        $subjectsCountSub = DB::table('student_subject_assigns')
            ->selectRaw('student_id, COUNT(*) as subjects_count')
            ->groupBy('student_id');

        $query->leftJoinSub($subjectsCountSub, 'ssa_count', function ($join) {
            $join->on('ssa_count.student_id', '=', 'students.id');
        });

        $query
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'students.academic_session_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'students.academic_class_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'students.academic_qualification_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'students.department_id');

        return $query->select([
            'students.id',
            'students.profile',
            'students.student_id',
            'students.name',
            'students.mobile',
            'students.admission_id',
            'students.college_roll',
            'students.reg_no',
            'students.student_type',
            'students.status',
            'students.department_id',
            'students.academic_class_id',
            'students.academic_session_id',
            'students.academic_qualification_id',
            DB::raw('COALESCE(ssa_count.subjects_count, 0) as subjects_count'),
            'ses.name as academic_session_name',
            'cls.name as academic_class_name',
            'q.name as academic_qualification_name',
            'dept.name as department_name',
        ]);
    }

    public function index(Request $request)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? $perPage : 10;

        if ($request->boolean('allData')) {
            $query = $this->baseFilteredQuery($request)
                ->select([
                    'students.id',
                    'students.name',
                    'students.mobile',
                    'students.college_roll',
                    'students.student_id',
                    'students.academic_class_id',
                    'students.academic_qualification_id',
                    'students.department_id',
                    'students.admission_id',
                    'students.reg_no',
                ])
                ->orderBy('students.college_roll', 'asc')
                ->get();

            return response()->json($query);
        }

        $query = $this->applyListJoinsAndSelect($this->baseFilteredQuery($request))
            ->orderBy('students.college_roll', 'asc');

        $datas = $query->paginate($perPage);

        return response()->json($datas);
    }

    public function adminAdmitCard(Request $request)
    {
        return $this->adminAdmitCardList($request, 'four');
    }

    public function adminAdmitCardTwoInOne(Request $request)
    {
        return $this->adminAdmitCardList($request, 'two');
    }

    private function adminAdmitCardList(Request $request, string $mode)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = $this->baseFilteredQuery($request);

        if ($request->boolean('allData')) {
            $rows = $query
                ->select([
                    'students.id',
                    'students.profile',
                    'students.student_id',
                    'students.name',
                    'students.mobile',
                    'students.admission_id',
                    'students.college_roll',
                    'students.reg_no',
                    'students.student_type',
                    'students.status',
                ])
                ->orderBy('students.college_roll', 'asc')
                ->get();

            return response()->json($rows);
        }

        $rows = $this->applyListJoinsAndSelect($query)
            ->orderBy('students.college_roll', 'asc')
            ->paginate($perPage);

        return response()->json($rows);
    }

    public function downloadBulkAdminAdmit(Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(600);

        $searchParams = json_decode((string) $request->input('search_params'), true);
        if (empty($searchParams) || !is_array($searchParams)) {
            return back();
        }

        $students = $this->getBulkStudentsFromSearchParams($searchParams, false);

        $site = (object) (app()->make('siteSettingObj') ?? []);
        $bgImage = $site->admin_admit_card ?? null;

        [$logo, $principle_signature] = $this->getPdfLogoAndSignature($site);

        $pdf = Pdf::loadView('pdf.admin_admit_card', compact('students', 'searchParams', 'site', 'bgImage', 'logo', 'principle_signature'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->stream('admit_card.pdf');
    }

    public function downloadBulkAdminAdmitTwoInOne(Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(600);

        $includeBack = (bool) ((int) $request->input('include_back') === 1);

        $searchParams = json_decode((string) $request->input('search_params'), true);
        if (empty($searchParams) || !is_array($searchParams)) {
            return back();
        }

        $students = $this->getBulkStudentsFromSearchParams($searchParams, true);

        $site = (object) (app()->make('siteSettingObj') ?? []);
        $bgFront = $site->admin_admit_card_front ?? ($site->admin_admit_card ?? null);
        $bgBack = $site->admin_admit_card_back ?? ($site->admin_admit_card ?? null);

        [$logo, $principle_signature] = $this->getPdfLogoAndSignature($site);

        $pdf = Pdf::loadView('pdf.admin_admit_card_two_in_one', compact('students', 'searchParams', 'site', 'bgFront', 'bgBack', 'logo', 'principle_signature', 'includeBack'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->stream('admit_card_two_in_one.pdf');
    }

    public function seatCard(Request $request)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = $this->baseFilteredQuery($request);

        if ($request->boolean('allData')) {
            $subjectId = $request->input('subject_id');
            if (!empty($subjectId)) {
                $query->whereExists(function ($q) use ($subjectId) {
                    $q->from('student_subject_assigns')
                        ->whereColumn('student_subject_assigns.student_id', 'students.id')
                        ->where('student_subject_assigns.subject_id', $subjectId);
                });
            }

            $rows = $query
                ->select([
                    'students.id',
                    'students.profile',
                    'students.student_id',
                    'students.name',
                    'students.mobile',
                    'students.admission_id',
                    'students.college_roll',
                    'students.reg_no',
                    'students.student_type',
                    'students.status',
                ])
                ->orderBy('students.college_roll', 'asc')
                ->get();

            return response()->json($rows);
        }

        $rows = $this->applyListJoinsAndSelect($query)
            ->orderBy('students.college_roll', 'asc')
            ->paginate($perPage);

        return response()->json($rows);
    }

    public function downloadBulkSeatCard(Request $request)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(600);

        $searchParams = json_decode((string) $request->input('search_params'), true);
        if (empty($searchParams) || !is_array($searchParams)) {
            return back();
        }

        $students = $this->getBulkStudentsFromSearchParams($searchParams, false);

        $site = (object) (app()->make('siteSettingObj') ?? []);
        $bgImage = $this->fetchRemoteImageAsBase64($site->seat_card ?? null);

        [$logo, $principle_signature] = $this->getPdfLogoAndSignature($site);

        $pdf = Pdf::loadView('pdf.seat_card', compact('students', 'searchParams', 'site', 'bgImage', 'logo', 'principle_signature'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->stream('seat_card.pdf');
    }

    private function getBulkStudentsFromSearchParams(array $searchParams, bool $withSubjects)
    {
        $request = new Request($searchParams);
        $query = $this->baseFilteredQuery($request);

        $perPage = (int) ($searchParams['pagination'] ?? 10);
        $page = (int) ($searchParams['page'] ?? 1);
        $page = $page > 0 ? $page : 1;
        $perPage = $perPage > 0 ? $perPage : 10;

        $students = $query
            ->select([
                'students.id',
                'students.name',
                'students.student_type',
                'students.reg_no',
                'students.fathers_name',
                'students.mothers_name',
                'students.college_roll',
                'students.student_id',
                'students.profile',
                'students.academic_session_id',
                'students.academic_class_id',
                'students.department_id',
                'students.academic_qualification_id',
            ])
            ->orderBy('students.college_roll', 'asc')
            ->forPage($page, $perPage)
            ->get();

        if ($students->isEmpty()) {
            return $students;
        }

        $sessionNames = DB::table('academic_sessions')->select('id', 'name')->whereIn('id', $students->pluck('academic_session_id')->unique()->filter()->values())->get()->keyBy('id');
        $classNames = DB::table('academic_classes')->select('id', 'name')->whereIn('id', $students->pluck('academic_class_id')->unique()->filter()->values())->get()->keyBy('id');
        $deptNames = DB::table('departments')->select('id', 'name')->whereIn('id', $students->pluck('department_id')->unique()->filter()->values())->get()->keyBy('id');
        $qualNames = DB::table('academic_qualifications')->select('id', 'name')->whereIn('id', $students->pluck('academic_qualification_id')->unique()->filter()->values())->get()->keyBy('id');

        foreach ($students as $s) {
            $s->academic_session = (object) ['name' => optional($sessionNames->get($s->academic_session_id))->name];
            $s->academic_class = (object) ['name' => optional($classNames->get($s->academic_class_id))->name];
            $s->department = (object) ['name' => optional($deptNames->get($s->department_id))->name];
            $s->qualification = (object) ['name' => optional($qualNames->get($s->academic_qualification_id))->name];
        }

        if ($withSubjects) {
            $this->hydrateStudentSubjectsForTwoInOne($students);
        }

        $this->hydrateStudentProfileBase64($students);

        return $students;
    }

    private function hydrateStudentSubjectsForTwoInOne($students)
    {
        if (!Schema::hasTable('student_subject_assigns') || !Schema::hasTable('subjects')) {
            foreach ($students as $s) {
                $s->compulsory_subjects = collect();
                $s->main_subjects = collect();
                $s->fourth_subjects = collect();
            }
            return;
        }

        $studentIds = $students->pluck('id')->unique()->filter()->values();

        $assignRows = DB::table('student_subject_assigns as a')
            ->join('subjects as s', 's.id', '=', 'a.subject_id')
            ->whereIn('a.student_id', $studentIds)
            ->where('s.is_child', 0)
            ->select('a.student_id', 'a.main_subject', 's.name_en')
            ->get();

        $assignByStudent = $assignRows->groupBy('student_id');

        $keys = $students
            ->map(function ($s) {
                return implode('|', [$s->academic_qualification_id, $s->department_id, $s->academic_class_id]);
            })
            ->unique()
            ->values();

        $compulsoryByKey = [];
        if (Schema::hasTable('subject_assigns') && Schema::hasTable('subject_assign_details')) {
            foreach ($keys as $key) {
                [$qid, $did, $cid] = array_pad(explode('|', $key), 3, null);
                if (!$qid || !$did || !$cid) continue;

                $assign = DB::table('subject_assigns')
                    ->where([
                        'academic_qualification_id' => $qid,
                        'department_id' => $did,
                        'academic_class_id' => $cid,
                    ])
                    ->first();

                $compulsory = collect();
                if ($assign) {
                    $compulsory = DB::table('subject_assign_details as d')
                        ->join('subjects as s', 's.id', '=', 'd.subject_id')
                        ->where('d.subject_assign_id', $assign->id)
                        ->where('s.is_child', 0)
                        ->where(['d.main_subject' => 0, 'd.fourth_subject' => 0])
                        ->select('s.name_en')
                        ->get()
                        ->map(function ($r) {
                            return (object) ['subject' => (object) ['name_en' => $r->name_en]];
                        });
                }
                $compulsoryByKey[$key] = $compulsory;
            }
        }

        foreach ($students as $s) {
            $key = implode('|', [$s->academic_qualification_id, $s->department_id, $s->academic_class_id]);
            $s->compulsory_subjects = $compulsoryByKey[$key] ?? collect();

            $rows = $assignByStudent->get($s->id, collect());
            $s->main_subjects = $rows
                ->where('main_subject', 1)
                ->values()
                ->map(function ($r) {
                    return (object) ['subject' => (object) ['name_en' => $r->name_en]];
                });
            $s->fourth_subjects = $rows
                ->where('main_subject', 0)
                ->values()
                ->map(function ($r) {
                    return (object) ['subject' => (object) ['name_en' => $r->name_en]];
                });
        }
    }

    private function hydrateStudentProfileBase64($students)
    {
        $imageContext = stream_context_create([
            'http' => ['timeout' => 30, 'ignore_errors' => true],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $makeJpegThumbBase64 = function (string $imageData, int $maxWidth = 400, int $maxHeight = 400, int $quality = 80) {
            try {
                $img = @imagecreatefromstring($imageData);
                if ($img === false) {
                    return null;
                }

                $w = imagesx($img);
                $h = imagesy($img);
                if (empty($w) || empty($h)) {
                    imagedestroy($img);
                    return null;
                }

                $ratio = min($maxWidth / $w, $maxHeight / $h, 1);
                $newW = (int) max(1, floor($w * $ratio));
                $newH = (int) max(1, floor($h * $ratio));

                $thumb = imagecreatetruecolor($newW, $newH);
                $white = imagecolorallocate($thumb, 255, 255, 255);
                imagefill($thumb, 0, 0, $white);
                imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);

                ob_start();
                imagejpeg($thumb, null, $quality);
                $jpegData = ob_get_clean();

                imagedestroy($img);
                imagedestroy($thumb);

                if (empty($jpegData)) {
                    return null;
                }

                return 'data:image/jpeg;base64,' . base64_encode($jpegData);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $chunkSize = 50;
        foreach ($students->chunk($chunkSize) as $chunk) {
            foreach ($chunk as $student) {
                $student->profile_base64 = null;
                if (!empty($student->profile)) {
                    try {
                        $profileData = @file_get_contents($student->profile, false, $imageContext);
                        if ($profileData) {
                            $student->profile_base64 = $makeJpegThumbBase64($profileData);
                        }
                        unset($profileData);
                    } catch (\Throwable $e) {
                        $student->profile_base64 = null;
                    }
                }
            }
            gc_collect_cycles();
        }
    }

    private function getPdfLogoAndSignature(object $site)
    {
        $imageContext = stream_context_create([
            'http' => ['timeout' => 30, 'ignore_errors' => true],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $fetchAsBase64 = function (?string $url, bool $convertPngToJpeg = false) use ($imageContext) {
            if (empty($url)) return null;
            try {
                $data = @file_get_contents($url, false, $imageContext);
                if (!$data) return null;

                $type = strtolower(pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg');

                if ($convertPngToJpeg && $type === 'png') {
                    try {
                        $image = @imagecreatefromstring($data);
                        if ($image === false) return null;
                        $width = imagesx($image);
                        $height = imagesy($image);
                        $newImage = imagecreatetruecolor($width, $height);
                        $white = imagecolorallocate($newImage, 255, 255, 255);
                        imagefill($newImage, 0, 0, $white);
                        imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);

                        ob_start();
                        imagejpeg($newImage, null, 90);
                        $jpegData = ob_get_clean();

                        imagedestroy($image);
                        imagedestroy($newImage);

                        if (empty($jpegData)) return null;
                        return 'data:image/jpeg;base64,' . base64_encode($jpegData);
                    } catch (\Throwable $e) {
                        return null;
                    }
                }

                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $logo = $fetchAsBase64($site->logo ?? null, true);
        $sign = $fetchAsBase64($site->principle_signature ?? null, true);

        return [$logo, $sign];
    }

    private function fetchRemoteImageAsBase64(?string $url)
    {
        if (empty($url)) return null;

        $imageContext = stream_context_create([
            'http' => ['timeout' => 30, 'ignore_errors' => true],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        try {
            $data = @file_get_contents($url, false, $imageContext);
            if (!$data) return null;
            $type = strtolower(pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg');
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function print(Request $request)
    {
        $limit = (int) ($request->input('limit') ?? 1000);
        $limit = $limit > 0 ? min($limit, 5000) : 1000;

        $students = $this->applyListJoinsAndSelect($this->baseFilteredQuery($request))
            ->orderBy('students.college_roll', 'asc')
            ->limit($limit)
            ->get();

        return response()->view('prints.students', ['students' => $students]);
    }

    public function exportCsv(Request $request)
    {
        $limit = (int) ($request->input('limit') ?? 5000);
        $limit = $limit > 0 ? min($limit, 20000) : 5000;

        $rows = $this->applyListJoinsAndSelect($this->baseFilteredQuery($request))
            ->orderBy('students.college_roll', 'asc')
            ->limit($limit)
            ->get();

        $fileName = 'students_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Software ID',
                'Student Name',
                'Mobile',
                'Student Type',
                'Admission ID',
                'Roll',
                'Reg No',
                'Session',
                'Department',
                'Qualification',
                'Class',
                'Subjects Count',
                'Status',
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->student_id,
                    $r->name,
                    $r->mobile,
                    $r->student_type,
                    $r->admission_id,
                    $r->college_roll,
                    $r->reg_no,
                    $r->academic_session_name,
                    $r->department_name,
                    $r->academic_qualification_name,
                    $r->academic_class_name,
                    $r->subjects_count,
                    $r->status,
                ]);
            }

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_class_id' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'student_type' => ['required'],
            'name' => ['required'],
            'password' => ['required'],
            'mobile' => ['required', 'digits:11', 'regex:/^01[0-9]+$/', Rule::unique('students', 'mobile')],
            'email' => ['nullable', 'email', Rule::unique('students', 'email')],
            'status' => ['nullable', Rule::in(['active', 'deactive'])],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Others'])],
        ]);

        try {
            $qualificationId = $request->input('academic_qualification_id');
            $regNo = $request->input('reg_no');
            if (!empty($regNo)) {
                $exists = Student::query()
                    ->where('academic_qualification_id', $qualificationId)
                    ->where('reg_no', $regNo)
                    ->exists();
                if ($exists) {
                    return response()->json(['error' => 'Already Registered This Reg. No'], 200);
                }
            }

            $collegeRoll = $request->input('college_roll');
            if (!empty($collegeRoll)) {
                $exists = Student::query()
                    ->where('academic_qualification_id', $qualificationId)
                    ->where('college_roll', $collegeRoll)
                    ->exists();
                if ($exists) {
                    return response()->json(['error' => 'Already Registered This College Roll'], 200);
                }
            }

            $admin = Auth::guard('admin')->user();

            $fillable = (new Student())->getFillable();
            $fillable = array_values(array_diff($fillable, ['otp']));
            $data = $request->only($fillable);

            if (empty($data['department_id'])) {
                $data['department_id'] = $admin->department_id ?? null;
            }

            $data['student_id'] = $this->generateStudentId();
            $data['status'] = $data['status'] ?? 'active';

            $data['name'] = strtoupper($request->input('name', ''));
            $data['fathers_name'] = strtoupper($request->input('fathers_name', ''));
            $data['mothers_name'] = strtoupper($request->input('mothers_name', ''));
            $data['address'] = strtoupper($request->input('address', ''));

            if ($request->hasFile('profile')) {
                $file = $request->file('profile');
                if ($file && $file->isValid()) {
                    $path = $file->store('upload/student/student-profile', 'public');
                    $data['profile'] = preg_replace('/^upload\//', '', $path);
                }
            } else {
                unset($data['profile']);
            }

            if ($request->has('cluster_subjects') && is_string($request->input('cluster_subjects'))) {
                $decoded = json_decode($request->input('cluster_subjects'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['cluster_subjects'] = $decoded;
                } else {
                    unset($data['cluster_subjects']);
                }
            }

            $student = Student::create($data);

            return response()->json([
                'created' => true,
                'id' => $student->id,
                'message' => 'Registration Successfully!',
                'student' => $student,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'exception' => $e->getMessage()
            ], 422);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'excel' => ['required', 'file'],
        ]);

        $file = $request->file('excel');
        if (!$file || !$file->isValid()) {
            return response()->json(['message' => 'Invalid CSV file.'], 422);
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if ($ext !== 'csv') {
            return response()->json(['message' => 'Only .csv file is supported.'], 422);
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        $base = [
            'academic_session_id' => $request->input('academic_session_id'),
            'academic_qualification_id' => $request->input('academic_qualification_id'),
            'department_id' => $request->input('department_id'),
            'academic_class_id' => $request->input('academic_class_id'),
        ];

        $path = $file->getRealPath();
        if (!$path) {
            return response()->json(['message' => 'Could not read uploaded file.'], 422);
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return response()->json(['message' => 'Could not open uploaded file.'], 422);
        }

        $rowIndex = -1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowIndex++;
            if ($rowIndex === 0) {
                continue;
            }

            $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);

            $name = (string) ($row[0] ?? '');
            $fathers = (string) ($row[1] ?? '');
            $mothers = (string) ($row[2] ?? '');
            $gender = (string) ($row[3] ?? '');
            $admissionId = (string) ($row[4] ?? '');
            $collegeRoll = (string) ($row[5] ?? '');
            $regNo = (string) ($row[6] ?? '');
            $mobile = (string) ($row[7] ?? '');
            $studentType = (string) ($row[8] ?? '');

            if ($admissionId === '') {
                $errors[] = ['row' => $rowIndex + 1, 'message' => 'Admission Roll is required.'];
                continue;
            }

            $exists = Student::query()->where('admission_id', $admissionId)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            $password = (string) random_int(111111, 999999);

            try {
                $data = array_merge($base, [
                    'name' => $name,
                    'fathers_name' => $fathers,
                    'mothers_name' => $mothers,
                    'gender' => $gender ?: null,
                    'admission_id' => $admissionId,
                    'college_roll' => $collegeRoll ?: null,
                    'reg_no' => $regNo ?: null,
                    'mobile' => $mobile,
                    'student_type' => $studentType,
                    'student_id' => $this->generateStudentId(),
                    'living_type' => 'Others',
                    'status' => 'active',
                    'password' => $password,
                ]);

                $data['name'] = strtoupper($data['name'] ?? '');
                $data['fathers_name'] = strtoupper($data['fathers_name'] ?? '');
                $data['mothers_name'] = strtoupper($data['mothers_name'] ?? '');

                Student::create($data);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowIndex + 1, 'message' => $e->getMessage()];
            }
        }

        fclose($handle);

        return response()->json([
            'message' => 'Import Successfully!',
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }

    public function details($id)
    {
        $student = Student::query()
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'students.academic_session_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'students.academic_class_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'students.academic_qualification_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'students.department_id')
            ->leftJoin('hostels as hos', 'hos.id', '=', 'students.hostel_id')
            ->select([
                'students.*',
                'ses.name as academic_session_name',
                'cls.name as academic_class_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'hos.name as hostel_name',
            ])
            ->where('students.id', $id)
            ->firstOrFail();

        return response()->json([
            'student' => $student,
        ]);
    }

    public function update(Request $request, $id)
    {
        $student = Student::query()->where('id', $id)->firstOrFail();

        $request->validate([
            'academic_class_id' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'student_type' => ['required'],
            'name' => ['required'],
            'mobile' => ['required', Rule::unique('students', 'mobile')->ignore($student->id)],
            'email' => ['nullable', 'email', Rule::unique('students', 'email')->ignore($student->id)],
            'status' => ['nullable', Rule::in(['active', 'deactive'])],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Others'])],
        ]);

        try {
            $fillable = (new Student())->getFillable();
            $fillable = array_values(array_diff($fillable, ['otp']));

            $data = $request->only($fillable);

            $data['name'] = strtoupper($request->input('name', $student->name ?? ''));
            $data['fathers_name'] = strtoupper($request->input('fathers_name', $student->fathers_name ?? ''));
            $data['mothers_name'] = strtoupper($request->input('mothers_name', $student->mothers_name ?? ''));
            $data['address'] = strtoupper($request->input('address', $student->address ?? ''));

            if (!$request->filled('password')) {
                unset($data['password']);
            }

            if ($request->hasFile('profile')) {
                $file = $request->file('profile');
                if ($file && $file->isValid()) {
                    $path = $file->store('upload/student/student-profile', 'public');
                    $data['profile'] = preg_replace('/^upload\//', '', $path);
                }
            } else {
                unset($data['profile']);
            }

            if ($request->has('cluster_subjects') && is_string($request->input('cluster_subjects'))) {
                $decoded = json_decode($request->input('cluster_subjects'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['cluster_subjects'] = $decoded;
                } else {
                    unset($data['cluster_subjects']);
                }
            }

            $student->fill($data);
            $student->save();

            return response()->json([
                'updated' => true,
                'message' => 'Update Successfully!',
                'student' => $student->fresh(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'exception' => $e->getMessage()
            ], 422);
        }
    }

    public function bulkDeactivate(Request $request)
    {
        $ids = $request->input('ids');
        if (is_array($ids) && count($ids) > 0) {
            $count = Student::whereIn('id', $ids)->update(['status' => 'deactive']);
            return response()->json(['updated' => $count]);
        }

        $filteredIds = $this->baseFilteredQuery($request)->select('students.id')->pluck('students.id');
        $count = 0;
        if ($filteredIds->count() > 0) {
            $count = Student::whereIn('id', $filteredIds->all())->update(['status' => 'deactive']);
        }

        return response()->json(['updated' => $count]);
    }

    public function downloadStudentZipImage(Request $request)
    {
        Storage::disk('public')->makeDirectory('student-zips');

        $zipName = 'students_' . date('Y_m_d_His') . '_' . Str::random(6) . '.zip';

        $admin = Auth::guard('admin')->user();
        $payload = $request->all();
        $payload['department_id'] = $request->input('department_id') ?: ($admin->department_id ?? null);
        $payload['hostel_id'] = $request->input('hostel_id') ?: ($admin->hostel_id ?? null);

        ProcessStudentZip::dispatch($payload, $zipName);

        return response()->json([
            'zip_name' => $zipName,
            'download_url' => url('/admin/download-zip/' . $zipName),
        ]);
    }

    public function checkZipStatus(string $filename)
    {
        $name = basename($filename);
        $path = storage_path('app/public/student-zips/' . $name);

        $exists = File::exists($path);
        $fileSize = $exists ? File::size($path) : 0;
        $mime = null;
        if ($exists && function_exists('mime_content_type')) {
            $mime = @mime_content_type($path);
        }

        $isReady = $exists && $fileSize > 512 && ($mime === null || stripos($mime, 'zip') !== false || str_ends_with($name, '.zip'));

        return response()->json([
            'exists' => $exists,
            'ready' => $isReady,
            'filename' => $name,
            'fileSize' => $fileSize,
            'mime' => $mime,
            'downloadUrl' => $isReady ? url('/admin/download-zip/' . $name) : null,
            'message' => $isReady ? 'File is ready for download' : ($exists ? 'File exists but still processing' : 'File is still processing'),
        ]);
    }

    public function downloadZip(string $filename)
    {
        $name = basename($filename);
        $path = storage_path('app/public/student-zips/' . $name);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->download($path, $name, [
            'Content-Type' => 'application/zip',
        ]);
    }
}
