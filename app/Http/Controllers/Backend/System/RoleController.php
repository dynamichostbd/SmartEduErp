<?php

namespace App\Http\Controllers\Backend\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;


class RoleController extends Controller
{
    private function ensurePermissionRows(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $cols = Schema::getColumnListing('permissions');
        if (!in_array('name', $cols, true) || !in_array('route', $cols, true)) {
            return;
        }

        $groups = [
            'Website' => [
                'slider' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
                'videoSlider' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
                'bus' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
                'calender' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
                'class' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
                'examR' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
                'popup' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
                'notice' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'officeOrder'],
                'content' => ['create', 'show', 'store', 'storeFile', 'destroy', 'file'],
            ],
        ];

        foreach ($groups as $parentName => $modules) {
            $parent = DB::table('permissions')->whereNull('parent_id')->where('name', $parentName)->first();
            $parentId = $parent ? (int) ($parent->id ?? 0) : 0;

            if ($parentId <= 0) {
                $ins = ['name' => $parentName];
                if (in_array('parent_id', $cols, true)) {
                    $ins['parent_id'] = null;
                }
                if (in_array('route', $cols, true)) {
                    $ins['route'] = null;
                }
                $parentId = (int) DB::table('permissions')->insertGetId($ins);
            }

            foreach ($modules as $routePrefix => $actions) {
                foreach ($actions as $action) {
                    $route = $routePrefix . '.' . $action;
                    $exists = DB::table('permissions')->where('route', $route)->exists();
                    if ($exists) {
                        continue;
                    }

                    $child = [
                        'name' => $action,
                        'route' => $route,
                    ];
                    if (in_array('parent_id', $cols, true)) {
                        $child['parent_id'] = $parentId;
                    }

                    DB::table('permissions')->insert($child);
                }
            }
        }
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('roles')) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $q = DB::table('roles')->orderByDesc('id');
        $q->where('id', '!=', 1);

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['name', 'type', 'status'];
        if ($value !== '' && in_array($field, $allowed, true) && Schema::hasColumn('roles', $field)) {
            $q->where($field, 'like', "%{$value}%");
        }

        $select = ['id', 'name'];
        foreach (['type', 'status', 'created_at'] as $col) {
            if (Schema::hasColumn('roles', $col)) {
                $select[] = $col;
            }
        }

        $datas = $q->select($select)->paginate($perPage);
        return response()->json($datas);
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('roles')) {
            return response()->json([], 404);
        }

        $row = DB::table('roles')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        $permissions = [];
        if (Schema::hasTable('role_permissions')) {
            $permissions = DB::table('role_permissions')->where('role_id', (int) $id)->pluck('permission_id')->toArray();
        }

        return response()->json([
            'id' => (int) ($row->id ?? 0),
            'name' => $row->name ?? null,
            'type' => property_exists($row, 'type') ? $row->type : null,
            'status' => property_exists($row, 'status') ? $row->status : null,
            'created_at' => property_exists($row, 'created_at') ? $row->created_at : null,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')],
        ];
        if (Schema::hasColumn('roles', 'status')) {
            $rules['status'] = ['required', Rule::in(['active', 'deactive'])];
        }
        if (Schema::hasColumn('roles', 'type')) {
            $rules['type'] = ['required', Rule::in(['Admin', 'Teacher', 'Staff'])];
        }
        $request->validate($rules);

        if (!Schema::hasTable('roles')) {
            return response()->json(['message' => 'Role module not ready'], 422);
        }

        $payload = [];
        foreach (['name', 'status', 'type'] as $col) {
            if ($request->has($col) && Schema::hasColumn('roles', $col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (Schema::hasColumn('roles', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::hasColumn('roles', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $roleId = DB::table('roles')->insertGetId($payload);

        $perms = $request->input('permissions');
        if (Schema::hasTable('role_permissions') && is_array($perms)) {
            $rows = [];
            foreach ($perms as $pid) {
                $pid = (int) $pid;
                if ($pid > 0) {
                    $rows[] = ['role_id' => $roleId, 'permission_id' => $pid];
                }
            }
            if (count($rows)) {
                DB::table('role_permissions')->insert($rows);
            }
        }

        Cache::forget('site_setting_cache');

        return response()->json(['message' => 'You have successfully created', 'id' => $roleId], 200);
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('roles')) {
            return response()->json(['message' => 'Role module not ready'], 422);
        }

        $rules = [
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore((int) $id)],
        ];
        if (Schema::hasColumn('roles', 'status')) {
            $rules['status'] = ['required', Rule::in(['active', 'deactive'])];
        }
        if (Schema::hasColumn('roles', 'type')) {
            $rules['type'] = ['required', Rule::in(['Admin', 'Teacher', 'Staff'])];
        }
        $request->validate($rules);

        $payload = [];
        foreach (['name', 'status', 'type'] as $col) {
            if ($request->has($col) && Schema::hasColumn('roles', $col)) {
                $payload[$col] = $request->input($col);
            }
        }
        if (Schema::hasColumn('roles', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('roles')->where('id', (int) $id)->update($payload);

        $perms = $request->input('permissions');
        if (Schema::hasTable('role_permissions')) {
            DB::table('role_permissions')->where('role_id', (int) $id)->delete();

            if (is_array($perms)) {
                $rows = [];
                foreach ($perms as $pid) {
                    $pid = (int) $pid;
                    if ($pid > 0) {
                        $rows[] = ['role_id' => (int) $id, 'permission_id' => $pid];
                    }
                }
                if (count($rows)) {
                    DB::table('role_permissions')->insert($rows);
                }
            }
        }

        Cache::forget("premited_menu_arr_role_" . ((int) $id));
        $verKey = "side_menu_version_role_" . ((int) $id);
        $ver = (int) Cache::get($verKey, 1);
        Cache::forever($verKey, $ver + 1);

        Cache::forget('site_setting_cache');

        return response()->json(['message' => 'You have successfully updated'], 200);
    }

    public function destroy(Request $request, $id)
    {
        if ((int) $id === 3) {
            return response()->json(['message' => 'You cannot delete this role!'], 422);
        }

        if (!Schema::hasTable('roles')) {
            return response()->json(['message' => 'Role module not ready'], 422);
        }

        if (Schema::hasTable('role_permissions')) {
            DB::table('role_permissions')->where('role_id', (int) $id)->delete();
        }

        DB::table('roles')->where('id', (int) $id)->delete();

        Cache::forget("premited_menu_arr_role_" . ((int) $id));
        $verKey = "side_menu_version_role_" . ((int) $id);
        $ver = (int) Cache::get($verKey, 1);
        Cache::forever($verKey, $ver + 1);

        Cache::forget('site_setting_cache');

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function getPermissions()
    {
        if (!Schema::hasTable('permissions')) {
            return response()->json([]);
        }

        $this->ensurePermissionRows();

        $parents = DB::table('permissions')->whereNull('parent_id')->orderBy('name')->get();
        $children = DB::table('permissions')->whereNotNull('parent_id')->orderBy('name')->get()->groupBy('parent_id');

        $tree = [];
        foreach ($parents as $p) {
            $tree[] = [
                'id' => (int) ($p->id ?? 0),
                'name' => $p->name ?? null,
                'route' => $p->route ?? null,
                'parent_id' => $p->parent_id ?? null,
                'children' => collect($children->get($p->id) ?? [])->map(function ($c) {
                    return [
                        'id' => (int) ($c->id ?? 0),
                        'name' => $c->name ?? null,
                        'route' => $c->route ?? null,
                        'parent_id' => $c->parent_id ?? null,
                    ];
                })->values()->all(),
            ];
        }

        return response()->json($tree);
    }
}
