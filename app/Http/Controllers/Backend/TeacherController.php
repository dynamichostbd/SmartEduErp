<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TeacherController extends Controller
{
    private function teacherTable(): ?string
    {
        return Schema::hasTable('teachers') ? 'teachers' : null;
    }

    private function subjectAssignTable(): ?string
    {
        return Schema::hasTable('teacher_subject_assigns') ? 'teacher_subject_assigns' : null;
    }

    private function emptyPaginator(int $perPage)
    {
        return response()->json([
            'current_page' => 1,
            'data' => [],
            'from' => null,
            'last_page' => 1,
            'per_page' => $perPage,
            'to' => null,
            'total' => 0,
        ]);
    }

    private function teacherRoleId(): ?int
    {
        if (!Schema::hasTable('roles')) {
            return null;
        }

        $role = DB::table('roles')->where('type', 'Teacher')->orderBy('id')->first();
        return $role?->id ? (int) $role->id : null;
    }

    private function roleIdForType(?string $type): ?int
    {
        $type = trim((string) $type);
        if ($type === '') {
            $type = 'Teacher';
        }

        if (!Schema::hasTable('roles')) {
            return null;
        }

        $role = DB::table('roles')->where('type', $type)->orderBy('id')->first();
        return $role?->id ? (int) $role->id : null;
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        if (!Schema::hasTable('admins')) {
            return $this->emptyPaginator($perPage);
        }

        $q = DB::table('admins as a')
            ->leftJoin('roles as r', function ($j) {
                $j->on('r.id', '=', 'a.role_id');
            })
            ->leftJoin('departments as d', function ($j) {
                $j->on('d.id', '=', 'a.department_id');
            })
            ->orderByDesc('a.id');

        if (Schema::hasColumn('admins', 'type')) {
            $q->whereIn('a.type', ['Teacher', 'Staff']);
        }

        $status = $request->input('status');
        if (!empty($status) && Schema::hasColumn('admins', 'status')) {
            $q->where('a.status', $status);
        }

        $departmentId = $request->input('department_id');
        if ($departmentId !== null && $departmentId !== '' && Schema::hasColumn('admins', 'department_id')) {
            $q->where('a.department_id', $departmentId);
        }

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['name', 'email', 'mobile'];
        if ($value !== '' && in_array($field, $allowed, true) && Schema::hasColumn('admins', $field)) {
            $q->where("a.{$field}", 'like', "%{$value}%");
        }

        $select = [
            'a.id',
            'a.name',
            'a.email',
            'a.mobile',
            'a.profile',
            'a.department_id',
            'a.role_id',
            'a.status',
            'a.created_at',
            'r.id as role_row_id',
            'r.name as role_name',
            'd.id as dept_row_id',
            'd.name as dept_name',
        ];

        $q->select($select);
        $datas = $q->paginate($perPage);

        $datas->getCollection()->transform(function ($row) {
            return [
                'id' => (int) ($row->id ?? 0),
                'name' => $row->name ?? null,
                'email' => $row->email ?? null,
                'mobile' => $row->mobile ?? null,
                'profile' => $row->profile ?? null,
                'status' => $row->status ?? null,
                'created_at' => $row->created_at ?? null,
                'role_id' => $row->role_id ?? null,
                'department_id' => $row->department_id ?? null,
                'role' => $row->role_row_id ? ['id' => (int) $row->role_row_id, 'name' => $row->role_name] : null,
                'department' => $row->dept_row_id ? ['id' => (int) $row->dept_row_id, 'name' => $row->dept_name] : null,
            ];
        });

        return response()->json($datas);
    }

    public function import(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admins') || !Schema::hasTable('teachers')) {
            return response()->json(['message' => 'Teacher import module not ready'], 422);
        }

        $request->validate([
            'excel_file' => ['required', 'file'],
        ]);

        if (!$request->hasFile('excel_file')) {
            return response()->json(['message' => 'Excel file required'], 422);
        }

        $file = $request->file('excel_file');
        if (!$file || !$file->isValid()) {
            return response()->json(['message' => 'Invalid file'], 422);
        }

        try {
            $tmpPath = $file->store('upload/tmp', 'public');
            $full = storage_path('app/public/' . $tmpPath);

            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\TeachersImport(), $full);

            return response()->json(['message' => 'Import Successfully!'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Import failed', 'exception' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('admins')) {
            return response()->json(['message' => 'Teacher module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        $type = $request->input('type');
        $type = in_array($type, ['Teacher', 'Staff'], true) ? $type : 'Teacher';
        $roleId = $this->roleIdForType($type);

        $payload = [];
        foreach (['name', 'email', 'mobile', 'department_id', 'status', 'emergency_contacts', 'is_two_factor_auth', 'block'] as $col) {
            if ($request->has($col) && Schema::hasColumn('admins', $col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (Schema::hasColumn('admins', 'type')) {
            $payload['type'] = $type;
        }
        if ($roleId && Schema::hasColumn('admins', 'role_id')) {
            $payload['role_id'] = $roleId;
        }
        if (Schema::hasColumn('admins', 'password')) {
            $payload['password'] = Hash::make((string) $request->input('password'));
        }

        if ($request->hasFile('profile') && Schema::hasColumn('admins', 'profile')) {
            $file = $request->file('profile');
            if ($file && $file->isValid()) {
                $path = $file->store('upload/teacher/teacher-profile', 'public');
                $payload['profile'] = preg_replace('/^upload\//', '', $path);
            }
        }

        if (Schema::hasColumn('admins', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::hasColumn('admins', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $teacherTable = $this->teacherTable();
        $assignTable = $this->subjectAssignTable();

        DB::beginTransaction();
        try {
            $id = DB::table('admins')->insertGetId($payload);

            if ($type === 'Teacher' && $teacherTable && Schema::hasColumn($teacherTable, 'admin_id')) {
                $teacherCols = Schema::getColumnListing($teacherTable);
                $tPayload = ['admin_id' => (int) $id];

                $teacherJson = $request->input('teacher');
                $teacherArr = [];
                if (is_string($teacherJson) && $teacherJson !== '') {
                    $decoded = json_decode($teacherJson, true);
                    $teacherArr = is_array($decoded) ? $decoded : [];
                } elseif (is_array($teacherJson)) {
                    $teacherArr = $teacherJson;
                }

                foreach ($teacherArr as $k => $v) {
                    if (in_array($k, $teacherCols, true) && $k !== 'admin_id') {
                        $tPayload[$k] = $v;
                    }
                }

                if ($request->hasFile('signature') && in_array('signature', $teacherCols, true)) {
                    $file = $request->file('signature');
                    if ($file && $file->isValid()) {
                        $path = $file->store('upload/teacher/teacher-signature', 'public');
                        $tPayload['signature'] = preg_replace('/^upload\//', '', $path);
                    }
                }

                if (in_array('created_at', $teacherCols, true)) {
                    $tPayload['created_at'] = now();
                }
                if (in_array('updated_at', $teacherCols, true)) {
                    $tPayload['updated_at'] = now();
                }

                DB::table($teacherTable)->insert($tPayload);
            }

            if ($type === 'Teacher' && $assignTable && Schema::hasColumn($assignTable, 'admin_id')) {
                $assignCols = Schema::getColumnListing($assignTable);
                $assignJson = $request->input('subject_assigns');
                $assignArr = [];
                if (is_string($assignJson) && $assignJson !== '') {
                    $decoded = json_decode($assignJson, true);
                    $assignArr = is_array($decoded) ? $decoded : [];
                } elseif (is_array($assignJson)) {
                    $assignArr = $assignJson;
                }

                $rows = [];
                foreach ($assignArr as $a) {
                    if (!is_array($a)) {
                        continue;
                    }

                    if (empty($a['subject_id']) || empty($a['department_id']) || empty($a['academic_qualification_id']) || empty($a['academic_class_id'])) {
                        continue;
                    }

                    $row = ['admin_id' => (int) $id];
                    foreach (['department_id', 'academic_qualification_id', 'academic_class_id', 'subject_id', 'status'] as $k) {
                        if (array_key_exists($k, $a) && in_array($k, $assignCols, true)) {
                            $row[$k] = $a[$k];
                        }
                    }
                    if (in_array('created_at', $assignCols, true)) {
                        $row['created_at'] = now();
                    }
                    if (in_array('updated_at', $assignCols, true)) {
                        $row['updated_at'] = now();
                    }
                    $rows[] = $row;
                }

                if (!empty($rows)) {
                    DB::table($assignTable)->insert($rows);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create', 'exception' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admins')) {
            return response()->json([], 404);
        }

        $q = DB::table('admins as a')
            ->leftJoin('roles as r', function ($j) {
                $j->on('r.id', '=', 'a.role_id');
            })
            ->leftJoin('departments as d', function ($j) {
                $j->on('d.id', '=', 'a.department_id');
            })
            ->select([
                'a.*',
                'r.name as role_name',
                'd.name as department_name',
            ])
            ->where('a.id', (int) $id);

        if (Schema::hasColumn('admins', 'type')) {
            $q->whereIn('a.type', ['Teacher', 'Staff']);
        }

        $row = $q->first();
        if (!$row) {
            return response()->json([], 404);
        }

        $teacherTable = $this->teacherTable();
        $assignTable = $this->subjectAssignTable();

        $teacher = null;
        if ($teacherTable && Schema::hasColumn($teacherTable, 'admin_id')) {
            $teacher = DB::table($teacherTable)->where('admin_id', (int) $id)->first();
        }

        $assigns = [];
        if ($assignTable && Schema::hasColumn($assignTable, 'admin_id')) {
            $assigns = DB::table($assignTable)->where('admin_id', (int) $id)->orderBy('id')->get()->toArray();
        }

        return response()->json([
            'teacher' => $row,
            'teacher_profile' => $teacher,
            'subject_assigns' => $assigns,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('admins')) {
            return response()->json(['message' => 'Teacher module not ready'], 422);
        }

        $row = DB::table('admins')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
        ]);

        $teacherTable = $this->teacherTable();
        $assignTable = $this->subjectAssignTable();

        $type = $request->input('type');
        $type = in_array($type, ['Teacher', 'Staff'], true) ? $type : ($row->type ?? 'Teacher');
        $roleId = $this->roleIdForType($type);

        $payload = [];
        foreach (['name', 'email', 'mobile', 'department_id', 'status', 'emergency_contacts', 'is_two_factor_auth', 'block'] as $col) {
            if ($request->has($col) && Schema::hasColumn('admins', $col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (Schema::hasColumn('admins', 'type') && $request->has('type')) {
            $payload['type'] = $type;
        }
        if (Schema::hasColumn('admins', 'role_id') && $roleId) {
            $payload['role_id'] = $roleId;
        }

        if ($request->filled('password') && Schema::hasColumn('admins', 'password')) {
            $payload['password'] = Hash::make((string) $request->input('password'));
        }

        if ($request->hasFile('profile') && Schema::hasColumn('admins', 'profile')) {
            $file = $request->file('profile');
            if ($file && $file->isValid()) {
                $path = $file->store('upload/teacher/teacher-profile', 'public');
                $payload['profile'] = preg_replace('/^upload\//', '', $path);
            }
        }

        if (Schema::hasColumn('admins', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::beginTransaction();
        try {
            DB::table('admins')->where('id', (int) $id)->update($payload);

            if ($type === 'Teacher' && $teacherTable && Schema::hasColumn($teacherTable, 'admin_id')) {
                $teacherCols = Schema::getColumnListing($teacherTable);
                $teacherJson = $request->input('teacher');
                $teacherArr = [];
                if (is_string($teacherJson) && $teacherJson !== '') {
                    $decoded = json_decode($teacherJson, true);
                    $teacherArr = is_array($decoded) ? $decoded : [];
                } elseif (is_array($teacherJson)) {
                    $teacherArr = $teacherJson;
                }

                $tPayload = [];
                foreach ($teacherArr as $k => $v) {
                    if (in_array($k, $teacherCols, true) && $k !== 'admin_id') {
                        $tPayload[$k] = $v;
                    }
                }

                if ($request->hasFile('signature') && in_array('signature', $teacherCols, true)) {
                    $file = $request->file('signature');
                    if ($file && $file->isValid()) {
                        $path = $file->store('upload/teacher/teacher-signature', 'public');
                        $tPayload['signature'] = preg_replace('/^upload\//', '', $path);
                    }
                }

                if (in_array('updated_at', $teacherCols, true)) {
                    $tPayload['updated_at'] = now();
                }

                $exists = DB::table($teacherTable)->where('admin_id', (int) $id)->exists();
                if ($exists) {
                    DB::table($teacherTable)->where('admin_id', (int) $id)->update($tPayload);
                } else {
                    $tPayload['admin_id'] = (int) $id;
                    if (in_array('created_at', $teacherCols, true)) {
                        $tPayload['created_at'] = now();
                    }
                    DB::table($teacherTable)->insert($tPayload);
                }
            }

            if ($type !== 'Teacher' && $teacherTable && Schema::hasColumn($teacherTable, 'admin_id')) {
                DB::table($teacherTable)->where('admin_id', (int) $id)->delete();
            }

            if ($type === 'Teacher' && $assignTable && Schema::hasColumn($assignTable, 'admin_id')) {
                $assignCols = Schema::getColumnListing($assignTable);
                DB::table($assignTable)->where('admin_id', (int) $id)->delete();

                $assignJson = $request->input('subject_assigns');
                $assignArr = [];
                if (is_string($assignJson) && $assignJson !== '') {
                    $decoded = json_decode($assignJson, true);
                    $assignArr = is_array($decoded) ? $decoded : [];
                } elseif (is_array($assignJson)) {
                    $assignArr = $assignJson;
                }

                $rows = [];
                foreach ($assignArr as $a) {
                    if (!is_array($a)) {
                        continue;
                    }

                    if (empty($a['subject_id']) || empty($a['department_id']) || empty($a['academic_qualification_id']) || empty($a['academic_class_id'])) {
                        continue;
                    }

                    $rowData = ['admin_id' => (int) $id];
                    foreach (['department_id', 'academic_qualification_id', 'academic_class_id', 'subject_id', 'status'] as $k) {
                        if (array_key_exists($k, $a) && in_array($k, $assignCols, true)) {
                            $rowData[$k] = $a[$k];
                        }
                    }
                    if (in_array('created_at', $assignCols, true)) {
                        $rowData['created_at'] = now();
                    }
                    if (in_array('updated_at', $assignCols, true)) {
                        $rowData['updated_at'] = now();
                    }
                    $rows[] = $rowData;
                }

                if (!empty($rows)) {
                    DB::table($assignTable)->insert($rows);
                }
            }

            if ($type !== 'Teacher' && $assignTable && Schema::hasColumn($assignTable, 'admin_id')) {
                DB::table($assignTable)->where('admin_id', (int) $id)->delete();
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
        if (!Schema::hasTable('admins')) {
            return response()->json(['message' => 'Teacher module not ready'], 422);
        }

        $q = DB::table('admins')->where('id', (int) $id);

        $ok = false;
        if (Schema::hasColumn('admins', 'status')) {
            $ok = (bool) $q->update(['status' => 'deactive', 'updated_at' => now()]);
        } else {
            $ok = (bool) $q->delete();
        }

        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
