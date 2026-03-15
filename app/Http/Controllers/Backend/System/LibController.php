<?php

namespace App\Http\Controllers\Backend\System;

use App\Http\Controllers\Controller;
use App\Traits\Lib\CustomDataTrait;
use App\Traits\Lib\DynamicDataTrait;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LibController extends Controller
{
    use DynamicDataTrait, CustomDataTrait;

    private function index(bool $withDynamic = true): array
    {
        $data = [];
        $data += App::make('globalData');
        $data += $this->customData();
        if ($withDynamic) {
            $data += $this->dynamicData();
        }

        $admin = Auth::guard('admin')->user();
        $data['auth_user'] = is_object($admin) ? [
            'id' => $admin->id ?? null,
            'name' => $admin->name ?? null,
            'type' => $admin->type ?? null,
            'role_id' => $admin->role_id ?? null,
            'department_id' => $admin->department_id ?? null,
            'profile' => $admin->profile ?? null,
        ] : null;

        return $data;
    }

    public function systems(Request $request): array
    {
        $lite = $request->boolean('lite');
        return [
            'global' => $this->index(!$lite),
            'profile' => Auth::guard('admin')->user()->profile ?? '',
            'permissions' => App::make('premitedMenuArr'),
            'site' => App::make('siteSettingObj'),
            'menus' => App::make('sideMenus'),
        ];
    }
}
