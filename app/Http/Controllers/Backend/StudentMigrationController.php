<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentMigrationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $adminDeptId = Auth::guard('admin')->user()->department_id ?? null;
        $migrateDepartmentId = $request->input('migrate_department_id') ?: $adminDeptId;

        $query = DB::table('student_migrations as m')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'm.academic_session_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'm.academic_class_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'm.academic_qualification_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'm.department_id')
            ->leftJoin('departments as mdept', 'mdept.id', '=', 'm.migrate_department_id')
            ->leftJoin('students as s', 's.id', '=', 'm.student_id')
            ->select([
                'm.id',
                'm.academic_session_id',
                'm.academic_qualification_id',
                'm.department_id',
                'm.academic_class_id',
                'm.migrate_department_id',
                'm.student_id',
                'm.college_roll',
                'm.status',
                'm.approved_date',
                'm.reject_date',
                'ses.name as academic_session_name',
                'cls.name as academic_class_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'mdept.name as migrate_department_name',
                's.name as student_name',
                's.mobile as student_mobile',
                's.admission_id as admission_id',
            ])
            ->orderByDesc('m.id');

        if ($request->filled('academic_session_id')) {
            $query->where('m.academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('academic_qualification_id')) {
            $query->where('m.academic_qualification_id', $request->input('academic_qualification_id'));
        }
        if ($request->filled('department_id')) {
            $query->where('m.department_id', $request->input('department_id'));
        }
        if ($request->filled('academic_class_id')) {
            $query->where('m.academic_class_id', $request->input('academic_class_id'));
        }
        if (!empty($migrateDepartmentId)) {
            $query->where('m.migrate_department_id', $migrateDepartmentId);
        }
        if ($request->filled('status')) {
            $query->where('m.status', $request->input('status'));
        }

        $field = (string) ($request->input('field_name') ?? '');
        $value = (string) ($request->input('value') ?? '');
        if ($field !== '' && $field !== '0' && $value !== '') {
            if (in_array($field, ['college_roll'], true)) {
                $query->where('m.' . $field, 'like', '%' . $value . '%');
            }
        }

        $datas = $query->paginate($perPage);

        $items = collect($datas->items());
        if (Schema::hasTable('invoices')) {
            $migrationIds = $items->pluck('id')->filter()->values();
            if ($migrationIds->count() > 0) {
                $invoices = DB::table('invoices')
                    ->select('student_migration_id', 'status')
                    ->whereIn('student_migration_id', $migrationIds)
                    ->get()
                    ->groupBy('student_migration_id');

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

    public function show($id)
    {
        $migration = DB::table('student_migrations as m')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'm.academic_session_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'm.academic_class_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'm.academic_qualification_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'm.department_id')
            ->leftJoin('departments as mdept', 'mdept.id', '=', 'm.migrate_department_id')
            ->leftJoin('students as s', 's.id', '=', 'm.student_id')
            ->select([
                'm.*',
                'ses.name as academic_session_name',
                'cls.name as academic_class_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'mdept.name as migrate_department_name',
                's.name as student_name',
                's.mobile as student_mobile',
                's.admission_id as admission_id',
            ])
            ->where('m.id', $id)
            ->first();

        if (!$migration) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $invoices = [];
        if (Schema::hasTable('invoices')) {
            $invQuery = DB::table('invoices as i')
                ->where('i.student_migration_id', $migration->id)
                ->select('i.account_head_id', 'i.invoice_date', 'i.invoice_number', 'i.amount', 'i.payment_date', 'i.status');

            if (Schema::hasTable('account_heads')) {
                $invQuery->leftJoin('account_heads as h', 'h.id', '=', 'i.account_head_id')
                    ->addSelect('h.name as head_name');
            }

            $invoices = $invQuery->orderByDesc('i.id')->get();
        }

        return response()->json([
            'id' => $migration->id,
            'student_id' => $migration->student_id,
            'college_roll' => $migration->college_roll,
            'status' => $migration->status,
            'academic_session' => ['id' => $migration->academic_session_id, 'name' => $migration->academic_session_name],
            'department' => ['id' => $migration->department_id, 'name' => $migration->department_name],
            'qualification' => ['id' => $migration->academic_qualification_id, 'name' => $migration->academic_qualification_name],
            'academic_class' => ['id' => $migration->academic_class_id, 'name' => $migration->academic_class_name],
            'migrate_department' => ['id' => $migration->migrate_department_id, 'name' => $migration->migrate_department_name],
            'student' => [
                'id' => $migration->student_id,
                'name' => $migration->student_name,
                'mobile' => $migration->student_mobile,
                'admission_id' => $migration->admission_id,
            ],
            'invoices' => $invoices,
            'inoivce_payable' => 1,
            'inoivce_paid' => 1,
        ]);
    }

    public function approved(Request $request)
    {
        $request->validate([
            'migrate_id' => ['required', 'integer'],
            'student_id' => ['required', 'integer'],
        ]);

        DB::beginTransaction();
        try {
            $password = (string) random_int(111111, 999999);

            $migrate = DB::table('student_migrations')->where('id', $request->input('migrate_id'))->first();
            if (!$migrate) {
                return response()->json(['message' => 'Migration not found'], 404);
            }

            $student = Student::query()->where('id', $request->input('student_id'))->first();
            if (!$student) {
                return response()->json(['message' => 'Student not found'], 404);
            }

            $sessionName = DB::table('academic_sessions')->where('id', $student->academic_session_id)->value('name');

            $newCollegeRoll = $this->generateCollegeRoll([
                'academic_session_id' => $student->academic_session_id,
                'department_id' => $migrate->migrate_department_id,
                'academic_qualification_id' => $student->academic_qualification_id,
                'academic_class_id' => $student->academic_class_id,
                'academic_session_name' => $sessionName,
            ]);

            $student->update([
                'password' => $password,
                'department_id' => $migrate->migrate_department_id,
                'college_roll' => $newCollegeRoll,
                'college_roll_auto_generate' => 1,
            ]);

            DB::table('student_migrations')
                ->where('id', $migrate->id)
                ->update([
                    'status' => 'approved',
                    'approved_date' => date('Y-m-d'),
                    'updated_at' => now(),
                ]);

            DB::commit();
            return response()->json(['message' => 'Approved Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to approve', 'exception' => $e->getMessage()], 422);
        }
    }

    public function reject($id)
    {
        DB::table('student_migrations')
            ->where('id', $id)
            ->update([
                'status' => 'reject',
                'reject_date' => date('Y-m-d'),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Reject Successfully!'], 200);
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
}
