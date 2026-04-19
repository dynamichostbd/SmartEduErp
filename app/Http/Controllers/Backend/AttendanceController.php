<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Traits\SmsGatewayTrait;

class AttendanceController extends Controller
{
    use SmsGatewayTrait;

    public function students(Request $request)
    {
        if (!Schema::hasTable('students')) {
            return response()->json(['students' => []]);
        }

        $request->validate([
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
        ]);

        $students = Student::query()
            ->select([
                'id',
                'student_id',
                'name',
                'mobile',
                'admission_id',
                'college_roll',
                'profile',
            ])
            ->where([
                'academic_session_id' => $request->input('academic_session_id'),
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'department_id' => $request->input('department_id'),
                'academic_class_id' => $request->input('academic_class_id'),
                'status' => 'active',
            ])
            ->orderByRaw('CAST(college_roll as UNSIGNED) asc')
            ->get();

        return response()->json(['students' => $students]);
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('attendances')) {
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

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = DB::table('attendances as a')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'a.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'a.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'a.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'a.department_id')
            ->select([
                'a.id',
                'a.date',
                'a.academic_session_id',
                'a.academic_qualification_id',
                'a.department_id',
                'a.academic_class_id',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
                DB::raw("(SELECT COUNT(*) FROM attendance_details ad WHERE ad.attendance_id = a.id AND ad.status = 'P') as present_student_count"),
                DB::raw("(SELECT COUNT(*) FROM students s WHERE s.status = 'active' AND s.academic_session_id = a.academic_session_id AND s.academic_qualification_id = a.academic_qualification_id AND s.academic_class_id = a.academic_class_id AND ((a.department_id IS NULL AND s.department_id IS NULL) OR s.department_id = a.department_id)) as total_student"),
            ])
            ->orderByDesc('a.date')
            ->orderByDesc('a.id');

        if ($request->filled('academic_session_id')) {
            $query->where('a.academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('academic_qualification_id')) {
            $query->where('a.academic_qualification_id', $request->input('academic_qualification_id'));
        }
        if ($request->filled('department_id')) {
            $query->where('a.department_id', $request->input('department_id'));
        }
        if ($request->filled('academic_class_id')) {
            $query->where('a.academic_class_id', $request->input('academic_class_id'));
        }

        $from = (string) ($request->input('from_date') ?? '');
        $to = (string) ($request->input('to_date') ?? '');
        if ($from !== '' && $to !== '') {
            $query->whereBetween('a.date', [date('Y-m-d', strtotime($from)), date('Y-m-d', strtotime($to))]);
        } elseif ($from !== '') {
            $query->whereDate('a.date', '>=', date('Y-m-d', strtotime($from)));
        } elseif ($to !== '') {
            $query->whereDate('a.date', '<=', date('Y-m-d', strtotime($to)));
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

    public function attendanceReport(Request $request)
    {
        if (!Schema::hasTable('attendances') || !Schema::hasTable('attendance_details') || !Schema::hasTable('students')) {
            return response()->json([]);
        }

        $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
        ]);

        $from = date('Y-m-d', strtotime((string) $request->input('from_date')));
        $to = date('Y-m-d', strtotime((string) $request->input('to_date')));

        $field = (string) ($request->input('field_name') ?? '');
        $value = (string) ($request->input('value') ?? '');

        $allowedFields = [
            'student_id' => 'ad.student_id',
            'name' => 'std.name',
            'mobile' => 'std.mobile',
            'admission_id' => 'std.admission_id',
            'college_roll' => 'std.college_roll',
        ];

        $query = DB::table('attendance_details as ad')
            ->leftJoin('attendances as a', 'a.id', '=', 'ad.attendance_id')
            ->leftJoin('students as std', 'std.student_id', '=', 'ad.student_id')
            ->selectRaw(
                'ad.student_id,
                std.name,
                std.mobile,
                std.college_roll,
                std.admission_id,
                IFNULL(sum(CASE WHEN ad.status = "P" THEN 1 END),0) as total_present,
                0 as total_absent,
                0 as present_percentage'
            )
            ->whereDate('a.date', '>=', $from)
            ->whereDate('a.date', '<=', $to)
            ->where([
                'a.academic_session_id' => $request->input('academic_session_id'),
                'a.academic_qualification_id' => $request->input('academic_qualification_id'),
                'a.department_id' => $request->input('department_id'),
                'a.academic_class_id' => $request->input('academic_class_id'),
            ])
            ->groupBy('ad.student_id', 'std.name', 'std.mobile', 'std.college_roll', 'std.admission_id')
            ->orderByRaw('CAST(std.college_roll as UNSIGNED) asc');

        $searchCol = $allowedFields[$field] ?? null;
        if ($searchCol && $value !== '') {
            $query->where($searchCol, 'LIKE', '%' . $value . '%');
        }

        $data = $query->get();

        return response()->json($data);
    }

    public function attendanceSheet(Request $request)
    {
        if (!Schema::hasTable('attendances') || !Schema::hasTable('attendance_details') || !Schema::hasTable('students')) {
            return response()->json(['students' => [], 'dates' => []]);
        }

        $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
        ]);

        $from = date('Y-m-d', strtotime((string) $request->input('from_date')));
        $to = date('Y-m-d', strtotime((string) $request->input('to_date')));

        $startDate = Carbon::parse((string) $request->input('from_date'));
        $endDate = Carbon::parse((string) $request->input('to_date'));
        $period = CarbonPeriod::create($startDate, $endDate);

        $dates = [];
        foreach ($period as $singleDate) {
            $dates[$singleDate->format('Y-m-d')] = 'A';
        }

        $students = Student::query()
            ->select([
                'id',
                'student_id',
                'name',
                'college_roll',
            ])
            ->where([
                'academic_session_id' => $request->input('academic_session_id'),
                'department_id' => $request->input('department_id'),
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'academic_class_id' => $request->input('academic_class_id'),
                'status' => 'active',
            ])
            ->orderByRaw('CAST(college_roll as UNSIGNED) asc')
            ->get()
            ->toArray();

        foreach ($students as $k => $student) {
            $studentId = (string) ($student['student_id'] ?? '');
            if ($studentId === '') {
                $students[$k]['attendance_data'] = [];
                continue;
            }

            $presents = DB::table('attendance_details as ad')
                ->join('attendances as a', 'a.id', '=', 'ad.attendance_id')
                ->where([
                    'a.academic_session_id' => $request->input('academic_session_id'),
                    'a.academic_qualification_id' => $request->input('academic_qualification_id'),
                    'a.department_id' => $request->input('department_id'),
                    'a.academic_class_id' => $request->input('academic_class_id'),
                    'ad.student_id' => $studentId,
                    'ad.status' => 'P',
                ])
                ->whereDate('a.date', '>=', $from)
                ->whereDate('a.date', '<=', $to)
                ->distinct()
                ->orderBy('a.date')
                ->pluck('a.date')
                ->toArray();

            $students[$k]['attendance_data'] = array_values($presents);
        }

        return response()->json(['students' => $students, 'dates' => $dates]);
    }

    public function examAttendanceSheet(Request $request)
    {
        if (!Schema::hasTable('students')) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'from' => 0,
                'to' => 0,
                'total' => 0,
                'per_page' => (int) ($request->input('pagination') ?? 10),
            ]);
        }

        $admin = Auth::guard('admin')->user();
        $departmentId = $request->input('department_id') ?: ($admin->department_id ?? null);
        $hostelId = $request->input('hostel_id') ?: ($admin->hostel_id ?? null);

        $subjectIds = $request->input('subject_ids') ?: [];
        $fourthSubjectId = $request->input('fourth_subject_id') ?: null;
        $classId = $request->input('academic_class_id') ?: null;

        $query = Student::query();

        $field = (string) ($request->input('field_name') ?? 'mobile');
        $value = $request->input('value');
        if ($value !== null && $value !== '') {
            if ($field === 'admission_id') {
                $query->where('admission_id', $value);
            } else {
                $query->where($field, 'like', '%' . $value . '%');
            }
        }

        if ($request->filled('academic_class_id')) {
            $query->where('academic_class_id', $request->input('academic_class_id'));
        }
        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('academic_qualification_id')) {
            $query->where('academic_qualification_id', $request->input('academic_qualification_id'));
        }
        if ($request->filled('student_type')) {
            $query->where('student_type', $request->input('student_type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        if ($departmentId !== null && $departmentId !== '') {
            $query->where('department_id', $departmentId);
        }
        if ($hostelId !== null && $hostelId !== '') {
            $query->where('hostel_id', $hostelId);
        }

        if (!empty($subjectIds) && is_array($subjectIds) && Schema::hasTable('student_subject_assigns')) {
            $subjectIds = array_values(array_filter($subjectIds, fn ($v) => $v !== null && $v !== ''));
            if (count($subjectIds) > 0) {
                $query->whereIn('id', function ($q) use ($subjectIds, $departmentId, $classId) {
                    $q->from('student_subject_assigns')
                        ->select('student_id')
                        ->when($classId, fn ($sq) => $sq->where('academic_class_id', $classId))
                        ->when($departmentId, fn ($sq) => $sq->where('department_id', $departmentId))
                        ->where('main_subject', 1)
                        ->whereIn('subject_id', $subjectIds)
                        ->groupBy('student_id')
                        ->havingRaw('COUNT(DISTINCT subject_id) = ?', [count($subjectIds)]);
                });
            }
        }

        if (!empty($fourthSubjectId) && Schema::hasTable('student_subject_assigns')) {
            $query->whereExists(function ($q) use ($fourthSubjectId, $departmentId, $classId) {
                $q->from('student_subject_assigns')
                    ->whereColumn('student_subject_assigns.student_id', 'students.id')
                    ->when($classId, fn ($sq) => $sq->where('academic_class_id', $classId))
                    ->when($departmentId, fn ($sq) => $sq->where('department_id', $departmentId))
                    ->where('main_subject', 0)
                    ->where('subject_id', $fourthSubjectId);
            });
        }

        $query->select('id', 'name', 'college_roll')
            ->orderByRaw('CAST(college_roll as UNSIGNED) asc');

        if ($request->boolean('allData')) {
            return response()->json($query->get());
        }

        $perPage = (int) ($request->input('pagination') ?? 1000);
        $perPage = $perPage > 0 ? min($perPage, 5000) : 1000;

        $datas = $query->paginate($perPage);

        return response()->json($datas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'details' => ['required', 'array'],
        ]);

        if (!Schema::hasTable('attendances') || !Schema::hasTable('attendance_details')) {
            return response()->json(['message' => 'Attendance module not ready'], 422);
        }

        $data = $request->except('details');
        $details = $request->input('details') ?? [];

        $arr = [
            'date' => date('Y-m-d', strtotime((string) $data['date'])),
            'academic_session_id' => $data['academic_session_id'],
            'department_id' => $data['department_id'],
            'academic_class_id' => $data['academic_class_id'],
            'academic_qualification_id' => $data['academic_qualification_id'],
        ];

        $exists = DB::table('attendances')->where($arr)->first();
        if (!empty($exists)) {
            return response()->json(['error' => 'Sorry!! Already Submitted'], 200);
        }

        DB::beginTransaction();
        try {
            $admin = Auth::guard('admin')->user();
            $ip = $request->ip();

            $attendanceId = DB::table('attendances')->insertGetId([
                'date' => $arr['date'],
                'academic_session_id' => $arr['academic_session_id'],
                'academic_qualification_id' => $arr['academic_qualification_id'],
                'department_id' => $arr['department_id'],
                'academic_class_id' => $arr['academic_class_id'],
                'created_by' => $admin->name ?? null,
                'created_ip' => $ip,
                'updated_by' => $admin->name ?? null,
                'updated_ip' => $ip,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $rows = [];
            foreach ($details as $d) {
                $sid = $d['student_id'] ?? null;
                if (empty($sid)) {
                    continue;
                }
                $rows[] = [
                    'attendance_id' => $attendanceId,
                    'student_id' => (string) $sid,
                    'in_time' => $d['in_time'] ?? null,
                    'out_time' => $d['out_time'] ?? null,
                    'type' => $d['type'] ?? null,
                    'device_identifier' => $d['device_identifier'] ?? null,
                    'rfid' => $d['rfid'] ?? null,
                    'status' => ($d['status'] ?? 'A') === 'P' ? 'P' : 'A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('attendance_details')->insert($rows);
            }

            $absentIds = collect($rows)
                ->filter(fn($r) => ($r['status'] ?? 'A') === 'A')
                ->pluck('student_id')
                ->filter()
                ->values()
                ->all();

            if (!empty($absentIds) && Schema::hasTable('students')) {
                $mobiles = DB::table('students')
                    ->whereNotNull('guardian_mobile')
                    ->whereIn('student_id', $absentIds)
                    ->where([
                        'academic_session_id' => $arr['academic_session_id'],
                        'academic_qualification_id' => $arr['academic_qualification_id'],
                        'department_id' => $arr['department_id'],
                        'academic_class_id' => $arr['academic_class_id'],
                        'status' => 'active',
                    ])
                    ->pluck('guardian_mobile')
                    ->toArray();

                $this->sendAbsentSms($arr['date'], $mobiles);
            }

            DB::commit();
            return response()->json(['message' => 'Create Successfully!', 'id' => $attendanceId], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create', 'exception' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        if (!Schema::hasTable('attendances') || !Schema::hasTable('attendance_details')) {
            return response()->json(['message' => 'Attendance module not ready'], 422);
        }

        $attendance = DB::table('attendances as a')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'a.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'a.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'a.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'a.department_id')
            ->select([
                'a.*',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
            ])
            ->where('a.id', $id)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $details = DB::table('attendance_details as d')
            ->select([
                'd.id',
                'd.attendance_id',
                'd.student_id',
                'd.in_time',
                'd.out_time',
                'd.type',
                'd.device_identifier',
                'd.rfid',
                'd.status',
            ])
            ->where('d.attendance_id', $attendance->id)
            ->get();

        $studentIds = $details->pluck('student_id')->filter()->values()->all();
        $studentsByStudentId = [];
        if (!empty($studentIds) && Schema::hasTable('students')) {
            $studentsByStudentId = Student::query()
                ->select([
                    'id',
                    'student_id',
                    'name',
                    'mobile',
                    'admission_id',
                    'college_roll',
                    'profile',
                ])
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy('student_id')
                ->all();
        }

        $details = $details->map(function ($d) use ($studentsByStudentId) {
            $std = $studentsByStudentId[$d->student_id] ?? null;
            $d->student = $std;
            return $d;
        })->values();

        return response()->json([
            'attendance' => $attendance,
            'details' => $details,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'details' => ['required', 'array'],
        ]);

        if (!Schema::hasTable('attendances') || !Schema::hasTable('attendance_details')) {
            return response()->json(['message' => 'Attendance module not ready'], 422);
        }

        $attendance = DB::table('attendances')->where('id', $id)->first();
        if (!$attendance) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->except('details');
        $details = $request->input('details') ?? [];

        $arr = [
            'date' => date('Y-m-d', strtotime((string) $data['date'])),
            'academic_session_id' => $data['academic_session_id'],
            'department_id' => $data['department_id'],
            'academic_class_id' => $data['academic_class_id'],
            'academic_qualification_id' => $data['academic_qualification_id'],
        ];

        $count = DB::table('attendances')
            ->where($arr)
            ->where('id', '<>', $id)
            ->count();

        if ($count > 0) {
            return response()->json(['error' => 'Sorry!! Already Submitted'], 200);
        }

        DB::beginTransaction();
        try {
            $admin = Auth::guard('admin')->user();
            $ip = $request->ip();

            DB::table('attendances')
                ->where('id', $id)
                ->update([
                    'date' => $arr['date'],
                    'academic_session_id' => $arr['academic_session_id'],
                    'academic_qualification_id' => $arr['academic_qualification_id'],
                    'department_id' => $arr['department_id'],
                    'academic_class_id' => $arr['academic_class_id'],
                    'updated_by' => $admin->name ?? null,
                    'updated_ip' => $ip,
                    'updated_at' => now(),
                ]);

            DB::table('attendance_details')->where('attendance_id', $id)->delete();

            $rows = [];
            foreach ($details as $d) {
                $sid = $d['student_id'] ?? null;
                if (empty($sid)) {
                    continue;
                }
                $rows[] = [
                    'attendance_id' => $id,
                    'student_id' => (string) $sid,
                    'in_time' => $d['in_time'] ?? null,
                    'out_time' => $d['out_time'] ?? null,
                    'type' => $d['type'] ?? null,
                    'device_identifier' => $d['device_identifier'] ?? null,
                    'rfid' => $d['rfid'] ?? null,
                    'status' => ($d['status'] ?? 'A') === 'P' ? 'P' : 'A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('attendance_details')->insert($rows);
            }

            DB::commit();
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update', 'exception' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('attendances')) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $attendance = DB::table('attendances')->where('id', $id)->first();
        if (!$attendance) {
            return response()->json(['message' => 'Not found'], 404);
        }

        DB::beginTransaction();
        try {
            if (Schema::hasTable('attendance_details')) {
                DB::table('attendance_details')->where('attendance_id', $id)->delete();
            }

            DB::table('attendances')->where('id', $id)->delete();

            DB::commit();
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Delete Unsuccessfully!'], 200);
        }
    }

    protected function sendAbsentSms(string $date, array $contacts): void
    {
        $cfgError = $this->smsGatewayConfigError();
        if ($cfgError) {
            return;
        }

        $contacts = array_values(array_filter(array_map('trim', $contacts)));
        if (count($contacts) === 0) {
            return;
        }

        $msgDate = date('D, d F Y', strtotime($date));
        $message = $this->smsTemplate('Absent', ['date' => $date]) ?: ('Absent on ' . $msgDate);

        $this->sendSmsViaGateway(implode(',', $contacts), $message);
    }

    protected function smsTemplate(string $smsType, array $params): string
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
        $smsBody = str_replace('[_Student_Name_]', (string) ($params['student_name'] ?? ''), $smsBody);
        $smsBody = str_replace('[_Mobile_]', (string) ($params['mobile'] ?? ''), $smsBody);
        $smsBody = str_replace('[_Email_]', (string) ($params['email'] ?? ''), $smsBody);
        $smsBody = str_replace('[_College_Roll_]', (string) ($params['college_roll'] ?? ''), $smsBody);
        $smsBody = str_replace('[_Password_]', (string) ($params['password'] ?? ''), $smsBody);
        $smsBody = str_replace('[_OTP_]', (string) ($params['otp'] ?? ''), $smsBody);
        $smsBody = str_replace('[_Invoice_ID_]', (string) ($params['invoice_id'] ?? ''), $smsBody);

        $date = !empty($params['date']) ? date('D, d F Y', strtotime((string) $params['date'])) : date('D, d F Y');
        $smsBody = str_replace('[_Date_]', $date, $smsBody);

        return $smsBody;
    }
}
