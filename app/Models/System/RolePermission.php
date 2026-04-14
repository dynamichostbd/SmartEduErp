<?php

namespace App\Models\System;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolePermission extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public static function permissions()
    {
        return RolePermission::select('permission_id', 'role_id')
            ->with(['permission' => function ($q) {
                $q->select('id', 'name', 'route', 'parent_id');
            }])->get()->groupBy('role_id');
    }

    public static function permissionProcess($obj)
    {
        $routes = [];

        $user = Auth::guard('admin')->user();
        $roleId = $user->role_id ?? null;

        $rolePermissions = $roleId ? $obj->get($roleId) : null;

        if ($rolePermissions) {
            foreach ($rolePermissions->toArray() as $value) {
                if (!empty($value['permission']['parent_id'])) {
                    $routes[] = $value['permission']['route'];
                }
            }
        }

        return $routes;
    }

    public static function routesForRole($roleId)
    {
        $roleId = (int) ($roleId ?? 0);
        if ($roleId <= 0) {
            return [];
        }

        $hasTable = function (string $table): bool {
            $key = 'schema_has_table_' . $table;
            return Cache::rememberForever($key, function () use ($table) {
                return Schema::hasTable($table);
            });
        };

        $cols = function (string $table): array {
            $key = 'schema_cols_' . $table;
            return Cache::rememberForever($key, function () use ($table) {
                if (!Schema::hasTable($table)) {
                    return [];
                }

                try {
                    return Schema::getColumnListing($table);
                } catch (\Throwable $e) {
                    return [];
                }
            });
        };

        if (!$hasTable('role_permissions') || !$hasTable('permissions')) {
            return [];
        }

        $permissionCols = $cols('permissions');
        $permissionColSet = !empty($permissionCols) ? array_fill_keys($permissionCols, true) : [];
        $hasPermCol = function (string $col) use ($permissionColSet): bool {
            return !empty($permissionColSet) && isset($permissionColSet[$col]);
        };

        $q = DB::table('role_permissions as rp')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('rp.role_id', $roleId);

        if ($hasPermCol('parent_id')) {
            $q->whereNotNull('p.parent_id');
        }

        if (!$hasPermCol('route')) {
            return [];
        }

        return $q->pluck('p.route')->filter()->unique()->values()->all();
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
