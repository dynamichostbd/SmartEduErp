<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Menu extends Model
{
    protected $guarded = ['id'];

    protected static function columnsCached(string $table): array
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }

        $key = 'schema_cols_' . $table;
        return $cache[$table] = Cache::rememberForever($key, function () use ($table) {
            if (!Schema::hasTable($table)) {
                return [];
            }

            try {
                return Schema::getColumnListing($table);
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class);
    }

    public function childs()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id')->oldest('sorting');
    }

    public function childMenus()
    {
        return $this->childs()->with('childMenus');
    }

    public static function getMenuList()
    {
        $parents = Menu::query()
            ->whereNull('parent_id')
            ->with('childMenus')
            ->oldest('sorting')
            ->get();

        $list = [];
        Menu::recursiveMenuList($parents, $list, 0);

        return $list;
    }

    public static function recursiveMenuList($menus, &$list = [], $level = 0)
    {
        if (empty($menus)) {
            return $list;
        }

        foreach ($menus as $menu) {
            $id = data_get($menu, 'id');
            if (!$id) {
                continue;
            }

            $name = (string) data_get($menu, 'menu_name', '');
            $list[$id] = str_repeat('|__', max(0, (int) $level)) . $name;

            $children = data_get($menu, 'childMenus');
            if (!empty($children) && count($children) > 0) {
                Menu::recursiveMenuList($children, $list, $level + 1);
            }
        }

        return $list;
    }

    public static function menus()
    {
        $premitedMenuArr = App::make('premitedMenuArr');

        $allowed = [];
        if (is_array($premitedMenuArr) && count($premitedMenuArr)) {
            $allowed = array_fill_keys($premitedMenuArr, true);
        }

        $admin = Auth::guard('admin')->user();
        $roleId = (int) ($admin->role_id ?? 0);

        $globalVer = (int) Cache::get('side_menu_version_global', 1);
        $roleVer = (int) Cache::get("side_menu_version_role_{$roleId}", 1);
        $cacheKey = "side_menu_cache_v{$globalVer}_r{$roleId}_v{$roleVer}";

        return Cache::rememberForever($cacheKey, function () use ($allowed) {
            $select = ['id', 'parent_id', 'menu_name', 'route_name'];

            $cols = self::columnsCached('menus');
            $colSet = !empty($cols) ? array_fill_keys($cols, true) : [];
            $hasCol = function (string $col) use ($colSet): bool {
                return !empty($colSet) && isset($colSet[$col]);
            };

            if ($hasCol('sorting')) $select[] = 'sorting';
            if ($hasCol('icon')) $select[] = 'icon';
            if ($hasCol('menu_look_type')) $select[] = 'menu_look_type';
            if ($hasCol('params')) $select[] = 'params';
            if ($hasCol('show_dasboard')) $select[] = 'show_dasboard';

            $q = DB::table('menus')->select($select);
            if ($hasCol('sorting')) {
                $q->orderBy('sorting', 'asc');
            }
            $rows = $q->orderBy('id', 'asc')->get();

            $childrenByParent = [];
            foreach ($rows as $r) {
                $pid = $r->parent_id ?? 0;
                $childrenByParent[$pid][] = $r;
            }

            $buildAll = function ($parentId) use (&$buildAll, $childrenByParent, $allowed) {
                $out = [];
                foreach (($childrenByParent[$parentId] ?? []) as $r) {
                    $childMenus = $buildAll($r->id);
                    $route = (string) ($r->route_name ?? '');

                    $isAllowed = $route !== '' && !empty($allowed) ? isset($allowed[$route]) : true;
                    if (!$isAllowed && !count($childMenus)) {
                        continue;
                    }

                    $out[] = [
                        'id' => $r->id,
                        'parent_id' => $r->parent_id,
                        'menu_name' => $r->menu_name,
                        'route_name' => $r->route_name,
                        'sorting' => property_exists($r, 'sorting') ? $r->sorting : null,
                        'icon' => property_exists($r, 'icon') ? $r->icon : null,
                        'menu_look_type' => property_exists($r, 'menu_look_type') ? $r->menu_look_type : null,
                        'params' => property_exists($r, 'params') ? $r->params : null,
                        'show_dasboard' => property_exists($r, 'show_dasboard') ? $r->show_dasboard : null,
                        'childMenus' => $childMenus,
                    ];
                }
                return $out;
            };

            $roots = [];
            foreach (($childrenByParent[0] ?? []) as $root) {
                $node = [
                    'id' => $root->id,
                    'parent_id' => $root->parent_id,
                    'menu_name' => $root->menu_name,
                    'route_name' => $root->route_name,
                    'sorting' => property_exists($root, 'sorting') ? $root->sorting : null,
                    'icon' => property_exists($root, 'icon') ? $root->icon : null,
                    'menu_look_type' => property_exists($root, 'menu_look_type') ? $root->menu_look_type : null,
                    'params' => property_exists($root, 'params') ? $root->params : null,
                    'show_dasboard' => property_exists($root, 'show_dasboard') ? $root->show_dasboard : null,
                    'childMenus' => [],
                ];

                $childNodes = [];
                foreach (($childrenByParent[$root->id] ?? []) as $child) {
                    $route = (string) ($child->route_name ?? '');
                    if ($route === '') {
                        continue;
                    }
                    if (!empty($allowed) && !isset($allowed[$route])) {
                        continue;
                    }
                    $childNodes[] = [
                        'id' => $child->id,
                        'parent_id' => $child->parent_id,
                        'menu_name' => $child->menu_name,
                        'route_name' => $child->route_name,
                        'sorting' => property_exists($child, 'sorting') ? $child->sorting : null,
                        'icon' => property_exists($child, 'icon') ? $child->icon : null,
                        'menu_look_type' => property_exists($child, 'menu_look_type') ? $child->menu_look_type : null,
                        'params' => property_exists($child, 'params') ? $child->params : null,
                        'show_dasboard' => property_exists($child, 'show_dasboard') ? $child->show_dasboard : null,
                        'childMenus' => $buildAll($child->id),
                    ];
                }
                $node['childMenus'] = $childNodes;
                $roots[] = $node;
            }

            return $roots;
        });
    }
}
