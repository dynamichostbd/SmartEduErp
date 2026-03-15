<?php

namespace App\Models\System;

use Auth;
use Illuminate\Database\Eloquent\Model;
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

        if (!Schema::hasTable('role_permissions') || !Schema::hasTable('permissions')) {
            return [];
        }

        $q = DB::table('role_permissions as rp')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('rp.role_id', $roleId);

        if (Schema::hasColumn('permissions', 'parent_id')) {
            $q->whereNotNull('p.parent_id');
        }

        if (!Schema::hasColumn('permissions', 'route')) {
            return [];
        }

        return $q->pluck('p.route')->filter()->unique()->values()->all();
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
