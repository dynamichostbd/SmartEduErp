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

                if (empty($site)) {
                    return [];
                }

                $data = $site->toArray();
                foreach ([
                    'logo',
                    'logo_small',
                    'favicon',
                    'idcard_front_part',
                    'idcard_back_part',
                    'admit_card_image',
                    'marksheet_image',
                    'online_admission_form_image',
                    'teacher_id_card_front',
                    'teacher_id_card_back',
                    'principle_signature',
                    'admin_admit_card',
                    'seat_card',
                    'admin_admit_card_front',
                    'admin_admit_card_back',
                ] as $k) {
                    $data[$k] = $site->{$k};
                }

                return $data;
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
