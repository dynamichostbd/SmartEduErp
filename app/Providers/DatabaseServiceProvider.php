<?php

namespace App\Providers;

use App\Models\System\Menu;
use App\Models\System\RolePermission;
use App\Models\System\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('siteSettingObj', function ($app) {
            return Cache::rememberForever('site_setting_cache', function () {
                $site = SiteSetting::first();

                return !empty($site) ? $site->toArray() : [];
            });
        });

        $this->app->singleton('sideMenus', function ($app) {
            return Menu::menus();
        });

        $this->app->singleton('premitedMenuArr', function ($app) {
            if (!Auth::guard('admin')->check()) {
                return [];
            }

            $roleId = Auth::guard('admin')->user()->role_id ?? null;
            $roleId = (int) ($roleId ?? 0);
            if ($roleId <= 0) {
                return [];
            }

            $key = "premited_menu_arr_role_{$roleId}";

            return Cache::remember($key, 600, function () use ($roleId) {
                return RolePermission::routesForRole($roleId);
            });
        });

        $this->app->singleton('globalData', function ($app) {
            return Cache::rememberForever('global_data_cache', function () {
                $data = [];

                if (Schema::hasTable('grade_management')) {
                    $data['grade_management'] = DB::table('grade_management')
                        ->select('id', 'from_mark', 'to_mark', 'grade', 'gpa', 'from_gpa', 'to_gpa')
                        ->orderBy('id')
                        ->get();
                } else {
                    $data['grade_management'] = [];
                }

                return $data;
            });
        });
    }

    public function boot(): void
    {
    }
}
