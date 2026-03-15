<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSummary;
use App\Models\AttendanceSummaryDetails;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceSummaryController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('attendance_summaries')) {
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

        $query = DB::table('attendance_summaries as s')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 's.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 's.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 's.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 's.department_id')
            ->select([
                's.id',
                's.from_date',
                's.to_date',
                's.academic_session_id',
                's.academic_qualification_id',
                's.department_id',
                's.academic_class_id',
                's.admit_card_id',
                's.present_percent',
                's.total_class',
                's.created_at',
                's.updated_at',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
            ])
            ->orderByDesc('s.id');

        if ($request->filled('academic_session_id')) {
            $query->where('s.academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('academic_qualification_id')) {
            $query->where('s.academic_qualification_id', $request->input('academic_qualification_id'));
        }
        if ($request->filled('department_id')) {
            $query->where('s.department_id', $request->input('department_id'));
        }
        if ($request->filled('academic_class_id')) {
            $query->where('s.academic_class_id', $request->input('academic_class_id'));
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

    public function students(Request $request)
    {
        if (!Schema::hasTable('attendances') || !Schema::hasTable('attendance_details') || !Schema::hasTable('students')) {
            return response()->json(['students' => []]);
        }

        $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'admit_card_id' => ['required'],
            'present_percent' => ['required'],
            'total_class' => ['required'],
        ]);

        $from = date('Y-m-d', strtotime((string) $request->input('from_date')));
        $to = date('Y-m-d', strtotime((string) $request->input('to_date')));
        $totalClass = max(0, (int) $request->input('total_class'));
        $presentPercent = (float) $request->input('present_percent');

        $rows = DB::table('attendance_details as ad')
            ->leftJoin('attendances as a', 'a.id', '=', 'ad.attendance_id')
            ->leftJoin('students as std', 'std.student_id', '=', 'ad.student_id')
            ->selectRaw(
                'ad.student_id,
                std.name,
                std.mobile,
                std.college_roll,
                std.admission_id,
                IFNULL(sum(CASE WHEN ad.status = "P" THEN 1 END),0) as total_present'
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
            ->orderByRaw('CAST(std.college_roll as UNSIGNED) asc')
            ->get();

        $data = $rows->map(function ($r) use ($totalClass, $presentPercent) {
            $totalPresent = (int) ($r->total_present ?? 0);
            $totalAbsent = max(0, $totalClass - $totalPresent);
            $percentage = $totalClass > 0 ? round(($totalPresent * 100) / $totalClass, 2) : 0;
            $status = $percentage >= $presentPercent ? 'P' : 'A';

            return [
                'student_id' => $r->student_id,
                'name' => $r->name,
                'mobile' => $r->mobile,
                'admission_id' => $r->admission_id,
                'college_roll' => $r->college_roll,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'present_percentage' => number_format($percentage, 2, '.', ''),
                'status' => $status,
            ];
        })->values();

        return response()->json(['students' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'admit_card_id' => ['required'],
            'present_percent' => ['required'],
            'total_class' => ['required'],
            'details' => ['required', 'array'],
        ]);

        if (!Schema::hasTable('attendance_summaries') || !Schema::hasTable('attendance_summary_details')) {
            return response()->json(['message' => 'Attendance summary module not ready'], 422);
        }

        $data = $request->except('details');
        $details = $request->input('details') ?? [];

        $exists = DB::table('attendance_summaries')->where([
            'admit_card_id' => $data['admit_card_id'] ?? null,
            'academic_session_id' => $data['academic_session_id'],
            'department_id' => $data['department_id'],
            'academic_class_id' => $data['academic_class_id'],
            'academic_qualification_id' => $data['academic_qualification_id'],
        ])->first();

        if (!empty($exists)) {
            return response()->json(['error' => 'Sorry!! Already Submitted'], 200);
        }

        DB::beginTransaction();
        try {
            $admin = Auth::guard('admin')->user();
            $ip = $request->ip();

            $summaryId = DB::table('attendance_summaries')->insertGetId([
                'from_date' => date('Y-m-d', strtotime((string) $data['from_date'])),
                'to_date' => date('Y-m-d', strtotime((string) $data['to_date'])),
                'academic_session_id' => $data['academic_session_id'],
                'academic_qualification_id' => $data['academic_qualification_id'],
                'department_id' => $data['department_id'],
                'academic_class_id' => $data['academic_class_id'],
                'admit_card_id' => $data['admit_card_id'] ?? null,
                'present_percent' => $data['present_percent'],
                'total_class' => $data['total_class'],
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
                    'attendance_summarie_id' => $summaryId,
                    'student_id' => (string) $sid,
                    'total_present' => (int) ($d['total_present'] ?? 0),
                    'total_absent' => (int) ($d['total_absent'] ?? 0),
                    'present_percentage' => (float) ($d['present_percentage'] ?? 0),
                    'status' => ($d['status'] ?? 'A') === 'P' ? 'P' : 'A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('attendance_summary_details')->insert($rows);
            }

            DB::commit();
            return response()->json(['message' => 'Create Successfully!', 'id' => $summaryId], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create', 'exception' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        if (!Schema::hasTable('attendance_summaries') || !Schema::hasTable('attendance_summary_details')) {
            return response()->json(['message' => 'Attendance summary module not ready'], 422);
        }

        $summary = DB::table('attendance_summaries as s')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 's.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 's.academic_qualification_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 's.academic_class_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 's.department_id')
            ->select([
                's.*',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'cls.name as academic_class_name',
                'dept.name as department_name',
            ])
            ->where('s.id', $id)
            ->first();

        if (!$summary) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $details = DB::table('attendance_summary_details as d')
            ->select([
                'd.id',
                'd.attendance_summarie_id',
                'd.student_id',
                'd.total_present',
                'd.total_absent',
                'd.present_percentage',
                'd.status',
            ])
            ->where('d.attendance_summarie_id', $summary->id)
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
            'summary' => $summary,
            'details' => $details,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'from_date' => ['required'],
            'to_date' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'admit_card_id' => ['required'],
            'present_percent' => ['required'],
            'total_class' => ['required'],
            'details' => ['required', 'array'],
        ]);

        if (!Schema::hasTable('attendance_summaries') || !Schema::hasTable('attendance_summary_details')) {
            return response()->json(['message' => 'Attendance summary module not ready'], 422);
        }

        $summary = DB::table('attendance_summaries')->where('id', $id)->first();
        if (!$summary) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->except('details');
        $details = $request->input('details') ?? [];

        $count = DB::table('attendance_summaries')
            ->where([
                'admit_card_id' => $data['admit_card_id'] ?? null,
                'academic_session_id' => $data['academic_session_id'],
                'department_id' => $data['department_id'],
                'academic_class_id' => $data['academic_class_id'],
                'academic_qualification_id' => $data['academic_qualification_id'],
            ])
            ->where('id', '<>', $id)
            ->count();

        if ($count > 0) {
            return response()->json(['error' => 'Sorry!! Already Submitted'], 200);
        }

        DB::beginTransaction();
        try {
            $admin = Auth::guard('admin')->user();
            $ip = $request->ip();

            DB::table('attendance_summaries')->where('id', $id)->update([
                'from_date' => date('Y-m-d', strtotime((string) $data['from_date'])),
                'to_date' => date('Y-m-d', strtotime((string) $data['to_date'])),
                'academic_session_id' => $data['academic_session_id'],
                'academic_qualification_id' => $data['academic_qualification_id'],
                'department_id' => $data['department_id'],
                'academic_class_id' => $data['academic_class_id'],
                'admit_card_id' => $data['admit_card_id'] ?? null,
                'present_percent' => $data['present_percent'],
                'total_class' => $data['total_class'],
                'updated_by' => $admin->name ?? null,
                'updated_ip' => $ip,
                'updated_at' => now(),
            ]);

            DB::table('attendance_summary_details')->where('attendance_summarie_id', $id)->delete();

            $rows = [];
            foreach ($details as $d) {
                $sid = $d['student_id'] ?? null;
                if (empty($sid)) {
                    continue;
                }

                $rows[] = [
                    'attendance_summarie_id' => $id,
                    'student_id' => (string) $sid,
                    'total_present' => (int) ($d['total_present'] ?? 0),
                    'total_absent' => (int) ($d['total_absent'] ?? 0),
                    'present_percentage' => (float) ($d['present_percentage'] ?? 0),
                    'status' => ($d['status'] ?? 'A') === 'P' ? 'P' : 'A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('attendance_summary_details')->insert($rows);
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
        if (!Schema::hasTable('attendance_summaries')) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $summary = DB::table('attendance_summaries')->where('id', $id)->first();
        if (!$summary) {
            return response()->json(['message' => 'Not found'], 404);
        }

        DB::beginTransaction();
        try {
            if (Schema::hasTable('attendance_summary_details')) {
                DB::table('attendance_summary_details')->where('attendance_summarie_id', $id)->delete();
            }

            DB::table('attendance_summaries')->where('id', $id)->delete();

            DB::commit();
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Delete Unsuccessfully!'], 200);
        }
    }
}
