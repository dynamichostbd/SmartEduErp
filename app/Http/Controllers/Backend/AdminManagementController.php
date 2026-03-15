<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminManagementController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admins')) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $query = DB::table('admins as a')
            ->leftJoin('roles as r', function ($j) {
                $j->on('r.id', '=', 'a.role_id');
            })
            ->leftJoin('departments as d', function ($j) {
                $j->on('d.id', '=', 'a.department_id');
            });

        if (Schema::hasColumn('admins', 'type')) {
            $query->where('a.type', 'Admin');
        }

        $status = $request->input('status');
        if (!empty($status) && Schema::hasColumn('admins', 'status')) {
            $query->where('a.status', $status);
        }

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['name', 'email', 'mobile'];
        if ($value !== '' && in_array($field, $allowed, true) && Schema::hasColumn('admins', $field)) {
            $query->where("a.{$field}", 'like', "%{$value}%");
        }

        $select = [
            'a.id',
            'a.name',
            'a.email',
            'a.mobile',
            'a.status',
            'a.profile',
            'a.role_id',
            'a.department_id',
            'a.is_two_factor_auth',
            'a.block',
            'a.created_at',
            'r.id as role_row_id',
            'r.name as role_name',
            'd.id as dept_row_id',
            'd.name as dept_name',
        ];

        $cols = [];
        foreach ($select as $c) {
            if (str_starts_with($c, 'a.') || str_starts_with($c, 'r.') || str_starts_with($c, 'd.')) {
                $cols[] = $c;
            } else {
                $cols[] = $c;
            }
        }

        $query->select($cols)->orderByDesc('a.id');

        $datas = $query->paginate($perPage);
        $datas->getCollection()->transform(function ($row) {
            return [
                'id' => (int) ($row->id ?? 0),
                'name' => $row->name ?? null,
                'email' => $row->email ?? null,
                'mobile' => $row->mobile ?? null,
                'status' => $row->status ?? null,
                'profile' => $row->profile ?? null,
                'is_two_factor_auth' => $row->is_two_factor_auth ?? null,
                'block' => $row->block ?? null,
                'created_at' => $row->created_at ?? null,
                'role_id' => $row->role_id ?? null,
                'department_id' => $row->department_id ?? null,
                'role' => $row->role_row_id ? ['id' => (int) $row->role_row_id, 'name' => $row->role_name] : null,
                'department' => $row->dept_row_id ? ['id' => (int) $row->dept_row_id, 'name' => $row->dept_name] : null,
            ];
        });

        return response()->json($datas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'role_id' => ['required'],
            'password' => ['required', 'min:6'],
        ]);

        if (!Schema::hasTable('admins')) {
            return response()->json(['message' => 'Admin module not ready'], 422);
        }

        $auth = Auth::guard('admin')->user();
        if ((int) ($auth->role_id ?? 0) !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payload = [];

        foreach (['name', 'email', 'mobile', 'emergency_contacts', 'role_id', 'department_id', 'status', 'is_two_factor_auth', 'block'] as $col) {
            if ($request->has($col) && Schema::hasColumn('admins', $col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (Schema::hasColumn('admins', 'type')) {
            $payload['type'] = 'Admin';
        }

        if (Schema::hasColumn('admins', 'password')) {
            $payload['password'] = Hash::make((string) $request->input('password'));
        }

        if ($request->hasFile('profile') && Schema::hasColumn('admins', 'profile')) {
            $file = $request->file('profile');
            if ($file && $file->isValid()) {
                $path = $file->store('upload/admin/admin-profile', 'public');
                $payload['profile'] = preg_replace('/^upload\//', '', $path);
            }
        }

        if (Schema::hasColumn('admins', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::hasColumn('admins', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $id = DB::table('admins')->insertGetId($payload);

        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admins')) {
            return response()->json([], 404);
        }

        $auth = Auth::guard('admin')->user();
        $authId = $auth->id ?? null;
        $authRoleId = $auth->role_id ?? null;
        $targetId = ((int) $authRoleId === 1) ? (int) $id : (int) $authId;

        $row = DB::table('admins as a')
            ->leftJoin('roles as r', function ($j) {
                $j->on('r.id', '=', 'a.role_id');
            })
            ->leftJoin('departments as d', function ($j) {
                $j->on('d.id', '=', 'a.department_id');
            })
            ->select([
                'a.id',
                'a.name',
                'a.email',
                'a.username',
                'a.type',
                'a.mobile',
                'a.address',
                'a.emergency_contacts',
                'a.department_id',
                'a.role_id',
                'a.profile',
                'r.id as role_row_id',
                'r.name as role_name',
                'd.id as dept_row_id',
                'd.name as dept_name',
            ])
            ->where('a.id', $targetId)
            ->first();

        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json([
            'id' => (int) ($row->id ?? 0),
            'name' => $row->name ?? null,
            'email' => $row->email ?? null,
            'username' => $row->username ?? null,
            'type' => $row->type ?? null,
            'mobile' => $row->mobile ?? null,
            'address' => $row->address ?? null,
            'emergency_contacts' => $row->emergency_contacts ?? null,
            'department_id' => $row->department_id ?? null,
            'role_id' => $row->role_id ?? null,
            'profile' => $row->profile ?? null,
            'role' => $row->role_row_id ? ['id' => (int) $row->role_row_id, 'name' => $row->role_name] : null,
            'department' => $row->dept_row_id ? ['id' => (int) $row->dept_row_id, 'name' => $row->dept_name] : null,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('admins')) {
            return response()->json(['message' => 'Admin module not ready'], 422);
        }

        $auth = Auth::guard('admin')->user();
        $authId = $auth->id ?? null;
        $authRoleId = $auth->role_id ?? null;

        $isSuper = (int) $authRoleId === 1;
        if ((int) $id !== (int) $authId && !$isSuper) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $row = DB::table('admins')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $payload = [];
        $basicCols = ['name', 'mobile', 'email', 'emergency_contacts', 'address', 'department_id'];
        $adminCols = ['role_id', 'status', 'is_two_factor_auth', 'block'];

        foreach ($basicCols as $col) {
            if ($request->has($col) && Schema::hasColumn('admins', $col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if ($isSuper) {
            foreach ($adminCols as $col) {
                if ($request->has($col) && Schema::hasColumn('admins', $col)) {
                    $payload[$col] = $request->input($col);
                }
            }

            if ($request->filled('password') && Schema::hasColumn('admins', 'password')) {
                $payload['password'] = Hash::make((string) $request->input('password'));
            }
        }

        if ($request->hasFile('profile') && Schema::hasColumn('admins', 'profile')) {
            $file = $request->file('profile');
            if ($file && $file->isValid()) {
                $path = $file->store('upload/admin/admin-profile', 'public');
                $payload['profile'] = preg_replace('/^upload\//', '', $path);
            }
        }

        if (Schema::hasColumn('admins', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('admins')->where('id', (int) $id)->update($payload);

        return response()->json(['message' => 'Information Update Successfully!'], 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!Schema::hasTable('admins')) {
            return response()->json(['message' => 'Admin module not ready'], 422);
        }

        $auth = Auth::guard('admin')->user();
        if ((int) ($auth->role_id ?? 0) !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $row = DB::table('admins')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        if (Schema::hasColumn('admins', 'status')) {
            DB::table('admins')->where('id', (int) $id)->update([
                'status' => 'deactive',
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function checkOldPassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required'],
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(false, 200);
        }

        $targetId = (int) ($request->input('id') ?? ($admin->id ?? 0));
        if ((int) ($admin->role_id ?? 0) === 1 && (int) ($admin->id ?? 0) !== $targetId) {
            return response()->json(true, 200);
        }

        return response()->json(Hash::check((string) $request->old_password, (string) $admin->password), 200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'new_password' => ['required', 'min:6'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $targetId = (int) ($request->input('id') ?? ($admin->id ?? 0));
        $isSuper = (int) ($admin->role_id ?? 0) === 1;

        if (!$isSuper || (int) ($admin->id ?? 0) === $targetId) {
            $request->validate([
                'old_password' => ['required'],
            ]);

            if (!Hash::check((string) $request->old_password, (string) $admin->password)) {
                return response()->json(['message' => 'Old password do not match our records!!'], 422);
            }
        }

        if (Schema::hasTable('admins')) {
            DB::table('admins')->where('id', $targetId)->update([
                'password' => Hash::make((string) $request->new_password),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Password changed successfully!'], 200);
    }
}
