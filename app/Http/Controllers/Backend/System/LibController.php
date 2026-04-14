<?php

namespace App\Http\Controllers\Backend\System;

use App\Http\Controllers\Controller;
use App\Traits\Lib\CustomDataTrait;
use App\Traits\Lib\DynamicDataTrait;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class LibController extends Controller
{
    use DynamicDataTrait, CustomDataTrait;

    private function index(bool $withDynamic = true, bool $withAuthUser = true): array
    {
        $data = [];
        $data += App::make('globalData');
        $data += $this->customData();
        if ($withDynamic) {
            $data += $this->dynamicData();
        }

        if ($withAuthUser) {
            $admin = Auth::guard('admin')->user();
            $data['auth_user'] = is_object($admin) ? [
                'id' => $admin->id ?? null,
                'name' => $admin->name ?? null,
                'type' => $admin->type ?? null,
                'role_id' => $admin->role_id ?? null,
                'department_id' => $admin->department_id ?? null,
                'profile' => $admin->profile ?? null,
            ] : null;
        }

        return $data;
    }

    public function systems(Request $request): array
    {
        $lite = $request->boolean('lite');

        $conn = (string) (config('database.default') ?? 'default');
        $db = (string) (config("database.connections.{$conn}.database") ?? '');
        $dbSuffix = preg_replace('/[^A-Za-z0-9_]+/', '_', $conn . '_' . $db) ?: 'default';

        $admin = Auth::guard('admin')->user();
        $roleId = (int) ($admin->role_id ?? 0);
        $menuGlobalVer = (int) Cache::get('side_menu_version_global', 1);
        $menuRoleVer = (int) Cache::get("side_menu_version_role_{$roleId}", 1);
        $cacheKey = "initialize_systems_l{$lite}_r{$roleId}_mg{$menuGlobalVer}_mr{$menuRoleVer}_db{$dbSuffix}";

        $authUser = is_object($admin) ? [
            'id' => $admin->id ?? null,
            'name' => $admin->name ?? null,
            'type' => $admin->type ?? null,
            'role_id' => $admin->role_id ?? null,
            'department_id' => $admin->department_id ?? null,
            'profile' => $admin->profile ?? null,
        ] : null;

        $base = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($lite) {
            return [
                'global' => $this->index(!$lite, false),
                'permissions' => App::make('premitedMenuArr'),
                'site' => App::make('siteSettingObj'),
                'menus' => App::make('sideMenus'),
            ];
        });

        $base['global'] = is_array($base['global'] ?? null) ? $base['global'] : [];
        $base['global']['auth_user'] = $authUser;
        $base['profile'] = $admin->profile ?? '';

        return $base;
    }
}
