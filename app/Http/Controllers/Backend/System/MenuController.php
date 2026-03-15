<?php

namespace App\Http\Controllers\Backend\System;

use App\Http\Controllers\Controller;
use App\Models\System\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('menus')) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $q = DB::table('menus as m')
            ->leftJoin('menus as p', 'p.id', '=', 'm.parent_id');

        $field = (string) ($request->input('field_name') ?? 'menu_name');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['menu_name', 'route_name'];
        if ($value !== '' && in_array($field, $allowed, true) && Schema::hasColumn('menus', $field)) {
            $q->where("m.{$field}", 'like', "%{$value}%");
        }

        $cols = [
            'm.id',
            'm.parent_id',
            'm.menu_name',
            'm.route_name',
            'p.menu_name as parent_menu_name',
        ];

        if (Schema::hasColumn('menus', 'params')) $cols[] = 'm.params';
        if (Schema::hasColumn('menus', 'sorting')) $cols[] = 'm.sorting';
        if (Schema::hasColumn('menus', 'icon')) $cols[] = 'm.icon';
        if (Schema::hasColumn('menus', 'menu_look_type')) $cols[] = 'm.menu_look_type';
        if (Schema::hasColumn('menus', 'show_dasboard')) $cols[] = 'm.show_dasboard';

        $q->select($cols);
        if (Schema::hasColumn('menus', 'sorting')) {
            $q->orderBy('m.sorting', 'asc');
        }
        $q->orderByDesc('m.id');

        return response()->json($q->paginate($perPage));
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('menus')) {
            return response()->json([], 404);
        }

        $row = DB::table('menus')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json($row);
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('menus')) {
            return response()->json(['message' => 'Menu module not ready'], 422);
        }

        $request->validate([
            'menu_name' => ['required', 'string', 'max:191'],
            'sorting' => ['required'],
        ]);

        $columns = Schema::getColumnListing('menus');
        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        $id = DB::table('menus')->insertGetId($payload);

        Cache::forget('side_menu_cache');
        $ver = (int) Cache::get('side_menu_version_global', 1);
        Cache::forever('side_menu_version_global', $ver + 1);

        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('menus')) {
            return response()->json(['message' => 'Menu module not ready'], 422);
        }

        $request->validate([
            'menu_name' => ['required', 'string', 'max:191'],
            'sorting' => ['required'],
        ]);

        $columns = Schema::getColumnListing('menus');
        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table('menus')->where('id', (int) $id)->update($payload);

        Cache::forget('side_menu_cache');
        $ver = (int) Cache::get('side_menu_version_global', 1);
        Cache::forever('side_menu_version_global', $ver + 1);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!Schema::hasTable('menus')) {
            return response()->json(['message' => 'Menu module not ready'], 422);
        }

        DB::table('menus')->where('id', (int) $id)->delete();

        Cache::forget('side_menu_cache');
        $ver = (int) Cache::get('side_menu_version_global', 1);
        Cache::forever('side_menu_version_global', $ver + 1);

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function menus(Request $request, $any = null)
    {
        if (!Schema::hasTable('menus')) {
            return response()->json([]);
        }

        return response()->json(Menu::getMenuList());
    }
}
