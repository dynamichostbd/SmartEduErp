<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OnlineAdmissionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $adminDeptId = Auth::guard('admin')->user()->department_id ?? null;
        $departmentId = $request->input('department_id') ?: $adminDeptId;

        $query = DB::table('online_admissions as a')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'a.academic_session_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'a.academic_class_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'a.academic_qualification_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'a.department_id')
            ->select([
                'a.id',
                'a.academic_session_id',
                'a.department_id',
                'a.academic_qualification_id',
                'a.academic_class_id',
                'a.name',
                'a.mobile',
                'a.admission_roll',
                'a.registration_no',
                'a.fathers_name',
                'a.mothers_name',
                'a.status',
                'a.profile',
                'a.subject_choose',
                'a.created_at',
                'ses.name as academic_session_name',
                'cls.name as academic_class_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
            ])
            ->orderByDesc('a.id');

        $field = (string) ($request->input('field_name') ?? '');
        $value = (string) ($request->input('value') ?? '');
        $fieldAllowed = ['name', 'mobile', 'admission_roll', 'registration_no'];
        if ($field !== '' && $field !== '0' && $value !== '' && in_array($field, $fieldAllowed, true)) {
            $query->where('a.' . $field, 'like', '%' . $value . '%');
        }

        foreach (['academic_class_id', 'academic_session_id', 'academic_qualification_id', 'gender', 'status'] as $f) {
            if ($request->filled($f)) {
                $query->where('a.' . $f, $request->input($f));
            }
        }

        if (!empty($departmentId)) {
            $query->where('a.department_id', $departmentId);
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween(DB::raw('DATE(a.created_at)'), [$fromDate, $toDate]);
        }

        $subjectIds = $request->input('subject_ids');
        if (is_array($subjectIds) && count($subjectIds) > 0) {
            foreach ($subjectIds as $sid) {
                if ($sid === null || $sid === '') {
                    continue;
                }
                $sid = (int) $sid;
                $query->where('a.subject_choose', 'like', '%"subject_id":' . $sid . '%');
            }
        }

        $datas = $query->paginate($perPage);
        $items = collect($datas->items());

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'online_admission_id')) {
            $ids = $items->pluck('id')->filter()->values();
            if ($ids->count() > 0) {
                $invoices = DB::table('invoices')
                    ->select('online_admission_id', 'status')
                    ->whereIn('online_admission_id', $ids)
                    ->get()
                    ->groupBy('online_admission_id');

                $items = $items->map(function ($row) use ($invoices) {
                    $row->invoices = ($invoices[$row->id] ?? collect())->values();
                    return $row;
                });
            }
        }

        return response()->json([
            'data' => $items->values(),
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

    public function rejectedList(Request $request)
    {
        $request->merge(['status' => 'rejected']);

        return $this->index($request);
    }

    public function show($id)
    {
        $row = DB::table('online_admissions')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $session = DB::table('academic_sessions')->select('id', 'name')->where('id', $row->academic_session_id)->first();
        $qualification = DB::table('academic_qualifications')->select('id', 'name', 'commitment')->where('id', $row->academic_qualification_id)->first();
        $department = $row->department_id ? DB::table('departments')->select('id', 'name')->where('id', $row->department_id)->first() : null;
        $academicClass = DB::table('academic_classes')->select('id', 'name')->where('id', $row->academic_class_id)->first();

        $subjectChoose = $this->decodeJson($row->subject_choose);
        $documents = $this->decodeJson($row->documents);
        $clusterSubjects = $this->decodeJson($row->cluster_subjects);

        $student = Student::query()->select('id', 'name', 'mobile', 'college_roll', 'admission_id')
            ->where('online_admission_id', $row->id)
            ->first();

        $invoices = [];
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'online_admission_id')) {
            $invoices = DB::table('invoices')
                ->where('online_admission_id', $row->id)
                ->select('account_head_id', 'invoice_date', 'invoice_number', 'amount', 'payment_date', 'status')
                ->orderByDesc('id')
                ->get();
        }

        return response()->json(array_merge((array) $row, [
            'profile' => $this->assetUrl($row->profile),
            'academic_session' => $session,
            'qualification' => $qualification,
            'department' => $department,
            'academic_class' => $academicClass,
            'subject_choose' => $subjectChoose,
            'documents' => $documents,
            'cluster_subjects' => $clusterSubjects,
            'student' => $student,
            'invoices' => $invoices,
        ]));
    }

    public function update(Request $request, $id)
    {
        $row = DB::table('online_admissions')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $payload = $request->all();

        if (array_key_exists('name', $payload)) {
            $payload['name'] = strtoupper((string) $payload['name']);
        }
        if (array_key_exists('fathers_name', $payload)) {
            $payload['fathers_name'] = strtoupper((string) $payload['fathers_name']);
        }
        if (array_key_exists('mothers_name', $payload)) {
            $payload['mothers_name'] = strtoupper((string) $payload['mothers_name']);
        }
        if (array_key_exists('address', $payload)) {
            $payload['address'] = strtoupper((string) $payload['address']);
        }

        if (!empty($payload['dob'])) {
            $payload['dob'] = date('Y-m-d', strtotime((string) $payload['dob']));
        }

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            if ($file && $file->isValid()) {
                $path = $file->store('upload/onlineAdmission/student-profile', 'public');
                $payload['profile'] = preg_replace('/^upload\//', '', $path);
            }
        } else {
            unset($payload['profile']);
        }

        unset($payload['id']);
        unset($payload['_method']);

        DB::table('online_admissions')->where('id', $id)->update($payload + ['updated_at' => now()]);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy($id)
    {
        $row = DB::table('online_admissions')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        DB::table('online_admissions')->where('id', $id)->update(['status' => 'rejected', 'updated_at' => now()]);
        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function approved(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer'],
        ]);

        DB::beginTransaction();
        try {
            $password = (string) random_int(111111, 999999);

            $admission = DB::table('online_admissions')->where('id', $request->input('id'))->first();
            if (!$admission) {
                return response()->json(['message' => 'Not found'], 404);
            }

            $sessionName = DB::table('academic_sessions')->where('id', $admission->academic_session_id)->value('name');

            $cond = [
                'academic_session_id' => $admission->academic_session_id,
                'department_id' => $admission->department_id,
                'academic_qualification_id' => $admission->academic_qualification_id,
                'academic_class_id' => $admission->academic_class_id,
                'academic_session_name' => $sessionName,
            ];

            $existStudent = Student::query()->where('mobile', $admission->mobile)->first();
            $sameStudent = Student::query()
                ->where('mobile', $admission->mobile)
                ->where('name', strtoupper((string) $admission->name))
                ->first();

            if ($existStudent && !$sameStudent) {
                DB::commit();

                return response()->json([
                    'data' => ['student' => $existStudent, 'admission' => $admission],
                    'message' => 'উক্ত শিক্ষার্থীর মোবাইল নাম্বারে অন্য একজন শিক্ষার্থী ডাটাবেইজে সংযুক্ত আছে, অনুগ্রহ করে শিক্ষার্থীর সঠিক মোবাইল নাম্বার আপডেট করুন অতঃপর পুনরায় Approved করুন',
                ], 201);
            }

            $studentData = (array) $admission;
            unset($studentData['id']);

            if (!$existStudent) {
                $studentData['student_id'] = $this->generateStudentId();
            }

            $studentData['online_admission_id'] = $admission->id;
            $studentData['admission_id'] = $admission->admission_roll;
            $studentData['reg_no'] = $admission->registration_no;
            $studentData['living_type'] = 'Others';
            $studentData['status'] = 'active';
            $studentData['college_roll_auto_generate'] = 1;
            $studentData['password'] = $password;
            $studentData['name'] = strtoupper((string) ($studentData['name'] ?? ''));
            $studentData['fathers_name'] = strtoupper((string) ($studentData['fathers_name'] ?? ''));
            $studentData['mothers_name'] = strtoupper((string) ($studentData['mothers_name'] ?? ''));
            $studentData['address'] = strtoupper((string) ($studentData['address'] ?? ''));

            $newCollegeRoll = $this->generateCollegeRoll($cond);
            $studentData['college_roll'] = $newCollegeRoll;

            if (
                $existStudent &&
                (int) $existStudent->department_id === (int) $admission->department_id &&
                (int) $existStudent->academic_qualification_id === (int) $admission->academic_qualification_id
            ) {
                unset($studentData['college_roll']);
                unset($studentData['college_roll_auto_generate']);
            }

            $fillable = (new Student())->getFillable();
            $fillable = array_values(array_diff($fillable, ['otp']));
            $studentData = array_intersect_key($studentData, array_flip($fillable));

            $student = Student::query()->updateOrCreate(['mobile' => $admission->mobile], $studentData);

            if ($student) {
                $this->studentSubjectAssign($student, $this->decodeJson($admission->subject_choose));

                DB::table('online_admissions')->where('id', $admission->id)->update(['status' => 'approved', 'updated_at' => now()]);

                if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'online_admission_id')) {
                    $update = [
                        'student_id' => $student->id,
                        'admission_id' => $student->admission_id,
                        'college_roll' => $student->college_roll,
                        'reg_no' => $student->reg_no,
                        'updated_at' => now(),
                    ];

                    foreach (array_keys($update) as $col) {
                        if (!Schema::hasColumn('invoices', $col)) {
                            unset($update[$col]);
                        }
                    }

                    if (!empty($update)) {
                        DB::table('invoices')->where('online_admission_id', $admission->id)->update($update);
                    }
                }
            }

            DB::commit();
            return response()->json(['data' => $student, 'password' => $password, 'message' => 'Approved Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to approve', 'exception' => $e->getMessage()], 422);
        }
    }

    public function approvedSmsSend(Request $request)
    {
        $request->validate([
            'mobile' => ['required'],
            'password' => ['required'],
        ]);

        $student = Student::query()->where('mobile', $request->input('mobile'))->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $token = (string) env('SMS_API_TOKEN', '');
        $sid = (string) env('SMS_SID', 'DYNAMICNONMASK');
        $baseUrl = (string) env('SMS_BASE_URL', 'https://smsplus.sslwireless.com/api/v3/send-sms');

        if ($token === '') {
            return response()->json(['message' => 'SMS API not configured'], 422);
        }

        $message = $this->smsTemplate('OnlineAdmission', ['password' => $request->input('password')], $student)
            ?: ('Your password is ' . $request->input('password'));

        $res = Http::withHeaders(['Content-Type' => 'application/json'])->post($baseUrl, [
            'api_token' => $token,
            'sid' => $sid,
            'msisdn' => $student->mobile,
            'sms' => $message,
            'csms_id' => Str::random(8) . time(),
        ]);

        if ($res->successful()) {
            return response()->json(['message' => 'SMS sent successfully'], 200);
        }

        return response()->json(['message' => 'Failed to send SMS'], 422);
    }

    public function getSubjects($id)
    {
        $data = ['subjectAssign' => ['main_subject' => 0, 'details' => [], 'note' => ''], 'assign_subjects' => []];

        $student = DB::table('online_admissions')->select('id', 'academic_qualification_id', 'department_id', 'academic_class_id', 'subject_choose')
            ->where('id', $id)
            ->first();

        if (!$student) {
            return response()->json($data);
        }

        $subjectAssign = DB::table('subject_assigns')
            ->where([
                'academic_qualification_id' => $student->academic_qualification_id,
                'department_id' => $student->department_id,
                'academic_class_id' => $student->academic_class_id,
            ])
            ->first();

        if (!$subjectAssign) {
            $data['assign_subjects'] = $this->decorateSubjectChoose($this->decodeJson($student->subject_choose));
            return response()->json($data);
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
                'd.sorting',
                's.name_en as subject_name',
                's.is_child',
                's.parent_id'
            )
            ->orderBy('d.sorting')
            ->get();

        $data['subjectAssign'] = [
            'id' => $subjectAssign->id,
            'academic_qualification_id' => $subjectAssign->academic_qualification_id,
            'department_id' => $subjectAssign->department_id,
            'academic_class_id' => $subjectAssign->academic_class_id,
            'main_subject' => $subjectAssign->main_subject,
            'note' => $subjectAssign->note,
            'details' => $details,
        ];

        $data['assign_subjects'] = $this->decorateSubjectChoose($this->decodeJson($student->subject_choose));

        return response()->json($data);
    }

    public function subjectAssign(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'integer'],
            'data' => ['required', 'array'],
        ]);

        $student = DB::table('online_admissions')->where('id', $request->input('student_id'))->first();
        if (!$student) {
            return response()->json(['message' => 'Applicant not found'], 404);
        }

        $payload = $this->decorateSubjectChoose($request->input('data') ?? []);

        DB::table('online_admissions')
            ->where('id', $student->id)
            ->update([
                'subject_choose' => json_encode($payload),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Subject Assign Successfully'], 200);
    }

    public function downloadOnlineAdmissionForm(Request $request)
    {
        $applicationsIds = json_decode((string) $request->input('applications_id'), true);
        if (!is_array($applicationsIds) || count($applicationsIds) === 0) {
            return response()->json(['message' => 'No applications selected'], 422);
        }

        $config = [];
        if (Schema::hasTable('site_settings')) {
            $cfg = DB::table('site_settings')->first();
            $config = $cfg ? (array) $cfg : [];
        }

        $apps = DB::table('online_admissions')->whereIn('id', $applicationsIds)->orderBy('admission_roll')->get();

        $applications = $apps->map(function ($row) {
            $row->profile = $this->assetUrl($row->profile);
            $row->academic_session = DB::table('academic_sessions')->select('id', 'name')->where('id', $row->academic_session_id)->first();
            $row->department = $row->department_id ? DB::table('departments')->select('id', 'name')->where('id', $row->department_id)->first() : null;
            $row->qualification = DB::table('academic_qualifications')->select('id', 'name', 'commitment')->where('id', $row->academic_qualification_id)->first();
            $row->academic_class = DB::table('academic_classes')->select('id', 'name')->where('id', $row->academic_class_id)->first();
            $row->subject_choose = $this->decorateSubjectChoose($this->decodeJson($row->subject_choose));

            $subjectAssign = DB::table('subject_assigns')
                ->where([
                    'academic_qualification_id' => $row->academic_qualification_id,
                    'department_id' => $row->department_id,
                    'academic_class_id' => $row->academic_class_id,
                ])
                ->first();

            $row->subjectAssign = ['details' => []];
            if ($subjectAssign) {
                $details = DB::table('subject_assign_details as d')
                    ->join('subjects as s', 's.id', '=', 'd.subject_id')
                    ->where('d.subject_assign_id', $subjectAssign->id)
                    ->where('d.fourth_subject', 0)
                    ->where('d.main_subject', 0)
                    ->where('s.is_child', 0)
                    ->select('d.subject_id', 's.name_en')
                    ->orderBy('d.sorting')
                    ->get();

                $row->subjectAssign = [
                    'details' => $details->map(fn($d) => ['subject' => ['name_en' => $d->name_en]])->values(),
                ];
            }

            $row->student = Student::query()->select('id', 'college_roll')->where('online_admission_id', $row->id)->first();

            return $row;
        });

        $pdf = Pdf::loadView('pdf.online_admission_form_bulk', [
            'applications' => $applications,
            'config' => $config,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('online-admission-form.pdf');
    }

    protected function studentSubjectAssign(Student $student, $subjects): void
    {
        if (!Schema::hasTable('student_subject_assigns')) {
            return;
        }

        $subjects = is_array($subjects) ? $subjects : [];

        DB::table('student_subject_assigns')
            ->where([
                'department_id' => $student->department_id,
                'academic_class_id' => $student->academic_class_id,
                'student_id' => $student->id,
            ])
            ->delete();

        $rows = [];
        foreach ($subjects as $subject) {
            $sid = $subject['subject_id'] ?? null;
            if (empty($sid)) {
                continue;
            }

            $rows[] = [
                'department_id' => $student->department_id,
                'academic_class_id' => $student->academic_class_id,
                'student_id' => $student->id,
                'subject_id' => (int) $sid,
                'main_subject' => !empty($subject['main_subject']) ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rows)) {
            DB::table('student_subject_assigns')->insert($rows);
        }
    }

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

    protected function generateCollegeRoll(array $cond): ?int
    {
        $sessionId = $cond['academic_session_id'] ?? null;
        $departmentId = $cond['department_id'] ?? null;
        $qualificationId = $cond['academic_qualification_id'] ?? null;
        $classId = $cond['academic_class_id'] ?? null;

        if (empty($sessionId) || empty($departmentId) || empty($qualificationId) || empty($classId)) {
            return null;
        }

        $last = DB::table('students')
            ->where([
                'academic_session_id' => $sessionId,
                'department_id' => $departmentId,
                'academic_qualification_id' => $qualificationId,
                'academic_class_id' => $classId,
                'college_roll_auto_generate' => 1,
            ])
            ->whereNotNull('college_roll')
            ->orderByRaw('CAST(college_roll as UNSIGNED) desc')
            ->value('college_roll');

        $deptCode = DB::table('department_qualidactions')
            ->where([
                'department_id' => $departmentId,
                'academic_qualification_id' => $qualificationId,
            ])
            ->value('department_code');

        $sessionName = (string) ($cond['academic_session_name'] ?? '');
        $yearPart = '';
        if ($sessionName !== '') {
            $ex = explode('-', $sessionName);
            $yearOfSession = $ex[0] ?? '';
            $yearOfSession = substr((string) $yearOfSession, -2);
            $yearPart = $yearOfSession;
        }

        $prefix = $yearPart . (string) ($deptCode ?? '');

        if (empty($last)) {
            return (int) $prefix;
        }

        return (int) $last + 1;
    }

    protected function decodeJson($val): array
    {
        if ($val === null || $val === '') {
            return [];
        }

        if (is_array($val)) {
            return $val;
        }

        $decoded = json_decode((string) $val, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    protected function decorateSubjectChoose($items): array
    {
        $items = is_array($items) ? $items : [];

        $ids = collect($items)
            ->pluck('subject_id')
            ->filter()
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values();

        $subjects = collect();
        if ($ids->count() > 0 && Schema::hasTable('subjects')) {
            $subjects = DB::table('subjects')->select('id', 'name_en')->whereIn('id', $ids)->get()->keyBy('id');
        }

        return collect($items)
            ->map(function ($item) use ($subjects) {
                $sid = (int) ($item['subject_id'] ?? 0);
                if ($sid <= 0) {
                    return null;
                }

                return [
                    'subject_id' => $sid,
                    'main_subject' => !empty($item['main_subject']) ? 1 : 0,
                    'subject' => [
                        'name_en' => $subjects[$sid]->name_en ?? '',
                    ],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function smsTemplate(string $smsType, array $params, Student $user): string
    {
        if (!Schema::hasTable('sms_templates')) {
            return '';
        }

        $template = DB::table('sms_templates')
            ->where('sms_type', $smsType)
            ->where('sending_status', 1)
            ->first();

        if (!$template || empty($template->sms_body)) {
            return '';
        }

        $smsBody = (string) $template->sms_body;
        $smsBody = str_replace('[_Student_Name_]', (string) ($user->name ?? ''), $smsBody);
        $smsBody = str_replace('[_Mobile_]', (string) ($user->mobile ?? ''), $smsBody);
        $smsBody = str_replace('[_Email_]', (string) ($user->email ?? ''), $smsBody);
        $smsBody = str_replace('[_College_Roll_]', (string) ($user->college_roll ?? ''), $smsBody);

        $smsBody = str_replace('[_Password_]', (string) ($params['password'] ?? ''), $smsBody);
        $smsBody = str_replace('[_OTP_]', (string) ($params['otp'] ?? ''), $smsBody);
        $smsBody = str_replace('[_Invoice_ID_]', (string) ($params['invoice_id'] ?? ''), $smsBody);

        $date = !empty($params['date']) ? date('D, d F Y', strtotime((string) $params['date'])) : date('D, d F Y');
        $smsBody = str_replace('[_Date_]', $date, $smsBody);

        return $smsBody;
    }

    protected function assetUrl($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $filePath1 = public_path($value);
        if (file_exists($filePath1)) {
            return url($value);
        }

        $filePath2 = public_path('storage/upload/' . $value);
        if (file_exists($filePath2)) {
            return url('storage/upload/' . $value);
        }

        $bucketUrl = env('DO_ASSET_URL', 'https://smartedubd.blr1.cdn.digitaloceanspaces.com/blr1_storage');
        $bucketUrl = rtrim((string) $bucketUrl, '/');
        $value = ltrim((string) $value, '/');

        return "$bucketUrl/$value";
    }
}
