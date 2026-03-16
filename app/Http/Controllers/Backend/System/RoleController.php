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
        if (!in_array('name', $cols, true) || !in_array('route', $cols, true) || !in_array('parent_id', $cols, true)) {
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
                    $ins['route'] = '';
                }

                try {
                    $parentId = (int) DB::table('permissions')->insertGetId($ins);
                } catch (\Throwable $e) {
                    if (array_key_exists('parent_id', $ins)) {
                        $ins['parent_id'] = 0;
                    }
                    $parentId = (int) DB::table('permissions')->insertGetId($ins);
                }
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

        $cols = Schema::getColumnListing('permissions');
        $routeCol = in_array('route', $cols, true) ? 'route' : (in_array('route_name', $cols, true) ? 'route_name' : null);
        $hasRoute = $routeCol !== null;
        $hasParentId = in_array('parent_id', $cols, true);

        $select = ['id', 'name'];
        if ($hasRoute) {
            $select[] = DB::raw("{$routeCol} as route");
        }
        if ($hasParentId) {
            $select[] = 'parent_id';
        }

        $all = DB::table('permissions')->select($select)->orderBy('name')->get();

        if ($all->count() === 0 && $hasRoute && Schema::hasTable('menus') && Schema::hasColumn('menus', 'route_name')) {
            $menus = DB::table('menus')
                ->select('id', 'parent_id', 'menu_name', 'route_name')
                ->orderBy('id')
                ->get();

            $byId = [];
            foreach ($menus as $m) {
                $byId[(int) ($m->id ?? 0)] = $m;
            }

            $findRoot = function ($menuId) use ($byId) {
                $seen = [];
                $cur = $byId[(int) $menuId] ?? null;
                while ($cur && !empty($cur->parent_id)) {
                    $cid = (int) ($cur->id ?? 0);
                    if ($cid > 0 && isset($seen[$cid])) {
                        break;
                    }
                    if ($cid > 0) {
                        $seen[$cid] = true;
                    }
                    $cur = $byId[(int) ($cur->parent_id ?? 0)] ?? null;
                }
                return $cur;
            };

            $toTitle = function (?string $text) {
                $t = trim((string) $text);
                if ($t === '') {
                    return 'Permissions';
                }
                $t = preg_replace('/[_\-]+/', ' ', $t);
                $t = preg_replace('/([a-z])([A-Z])/', '$1 $2', $t);
                $t = preg_replace('/\s+/', ' ', $t);
                return ucwords($t);
            };

            $parentsByName = [];
            if ($hasParentId) {
                $existingParents = DB::table('permissions')
                    ->whereNull('parent_id')
                    ->select('id', 'name')
                    ->get();
                foreach ($existingParents as $p) {
                    $name = (string) ($p->name ?? '');
                    if ($name !== '') {
                        $parentsByName[$name] = (int) ($p->id ?? 0);
                    }
                }
            }

            foreach ($menus as $m) {
                $route = trim((string) ($m->route_name ?? ''));
                if ($route === '') {
                    continue;
                }

                $groupName = null;
                $root = $findRoot((int) ($m->id ?? 0));
                if ($root) {
                    $groupName = $toTitle($root->menu_name ?? null);
                }
                if (!$groupName) {
                    $prefix = str_contains($route, '.') ? explode('.', $route, 2)[0] : $route;
                    $groupName = $toTitle($prefix);
                }

                $parentId = null;
                if ($hasParentId) {
                    if (!isset($parentsByName[$groupName]) || $parentsByName[$groupName] <= 0) {
                        $ins = ['name' => $groupName, 'parent_id' => null];
                        $ins[$routeCol] = '';

                        try {
                            $parentsByName[$groupName] = (int) DB::table('permissions')->insertGetId($ins);
                        } catch (\Throwable $e) {
                            $ins['parent_id'] = 0;
                            $parentsByName[$groupName] = (int) DB::table('permissions')->insertGetId($ins);
                        }
                    }
                    $parentId = $parentsByName[$groupName] ?? null;
                }

                $action = str_contains($route, '.') ? (explode('.', $route, 2)[1] ?? $route) : $route;
                $action = strtolower(trim((string) $action));
                $action = $action !== '' ? $action : $route;

                $exists = DB::table('permissions')->where($routeCol, $route)->exists();
                if ($exists) {
                    continue;
                }

                $child = ['name' => $action];
                $child[$routeCol] = $route;
                if ($hasParentId) {
                    $child['parent_id'] = $parentId;
                }
                DB::table('permissions')->insert($child);
            }

            $all = DB::table('permissions')->select($select)->orderBy('name')->get();
        }

        $buildFromFlat = function ($rows) use ($hasRoute) {
            $groups = [];
            $groupIndex = 0;

            foreach ($rows as $r) {
                $route = $hasRoute ? ($r->route ?? null) : null;
                $prefix = null;
                if (is_string($route) && $route !== '' && str_contains($route, '.')) {
                    $prefix = explode('.', $route, 2)[0] ?: null;
                }

                $groupKey = $prefix ?: 'Permissions';
                if (!isset($groups[$groupKey])) {
                    $groupIndex++;
                    $groups[$groupKey] = [
                        'id' => 0 - $groupIndex,
                        'name' => $groupKey,
                        'route' => null,
                        'parent_id' => null,
                        'children' => [],
                    ];
                }

                $groups[$groupKey]['children'][] = [
                    'id' => (int) ($r->id ?? 0),
                    'name' => $r->name ?? null,
                    'route' => $hasRoute ? ($r->route ?? null) : null,
                    'parent_id' => null,
                ];
            }

            foreach ($groups as &$g) {
                usort($g['children'], function ($a, $b) {
                    return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
                });
            }
            unset($g);

            return array_values($groups);
        };

        if (!$hasParentId) {
            return response()->json($buildFromFlat($all));
        }

        $parents = $all->whereNull('parent_id')->values();
        if ($parents->count() === 0) {
            return response()->json($buildFromFlat($all));
        }

        $children = $all->whereNotNull('parent_id')->groupBy('parent_id');
        $tree = [];
        foreach ($parents as $p) {
            $tree[] = [
                'id' => (int) ($p->id ?? 0),
                'name' => $p->name ?? null,
                'route' => $hasRoute ? ($p->route ?? null) : null,
                'parent_id' => $p->parent_id ?? null,
                'children' => collect($children->get($p->id) ?? [])->map(function ($c) use ($hasRoute) {
                    return [
                        'id' => (int) ($c->id ?? 0),
                        'name' => $c->name ?? null,
                        'route' => $hasRoute ? ($c->route ?? null) : null,
                        'parent_id' => $c->parent_id ?? null,
                    ];
                })->values()->all(),
            ];
        }

        return response()->json($tree);
    }
}
