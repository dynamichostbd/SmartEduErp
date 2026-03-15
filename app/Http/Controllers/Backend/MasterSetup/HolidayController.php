<?php

namespace App\Http\Controllers\Backend\MasterSetup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HolidayController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('holidays') ? 'holidays' : null;
    }

    private function flushCaches(): void
    {
        Cache::forget('dynamic_data_cache');
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->table();
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        if (!$table) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate($perPage));
        }

        $cols = Schema::getColumnListing($table);
        $q = DB::table($table)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['holiday_type', 'name'], true) && in_array($field, $cols, true)) {
            $q->where($field, 'like', "%{$value}%");
        }

        if ($request->has('status') && in_array('status', $cols, true)) {
            $status = trim((string) $request->input('status'));
            if ($status !== '') {
                $q->where('status', $status);
            }
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Holiday module not ready'], 422);
        }

        $request->validate([
            'holiday_type' => ['required'],
            'name' => ['required'],
        ]);

        $columns = Schema::getColumnListing($table);
        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (!isset($payload['status']) && in_array('status', $columns, true)) {
            $payload['status'] = 'active';
        }

        if (in_array('created_by', $columns, true)) {
            $payload['created_by'] = $admin->name ?? null;
        }
        if (in_array('created_ip', $columns, true)) {
            $payload['created_ip'] = $ip;
        }
        if (in_array('updated_by', $columns, true)) {
            $payload['updated_by'] = $admin->name ?? null;
        }
        if (in_array('updated_ip', $columns, true)) {
            $payload['updated_ip'] = $ip;
        }
        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        try {
            $id = DB::table($table)->insertGetId($payload);
            $this->flushCaches();
            return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to create',
                'exception' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->table();
        if (!$table) {
            return response()->json([], 404);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json(['holiday' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Holiday module not ready'], 422);
        }

        $request->validate([
            'holiday_type' => ['required'],
            'name' => ['required'],
        ]);

        $columns = Schema::getColumnListing($table);
        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (in_array('updated_by', $columns, true)) {
            $payload['updated_by'] = $admin->name ?? null;
        }
        if (in_array('updated_ip', $columns, true)) {
            $payload['updated_ip'] = $ip;
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        try {
            DB::table($table)->where('id', (int) $id)->update($payload);
            $this->flushCaches();
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update',
                'exception' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Holiday module not ready'], 422);
        }

        $columns = Schema::getColumnListing($table);

        if (in_array('status', $columns, true)) {
            $ok = DB::table($table)->where('id', (int) $id)->update([
                'status' => 'deactive',
                'updated_at' => in_array('updated_at', $columns, true) ? now() : DB::raw('updated_at'),
            ]);

            if ($ok) {
                $this->flushCaches();
            }

            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        }

        $ok = DB::table($table)->where('id', (int) $id)->delete();
        if ($ok) {
            $this->flushCaches();
        }

        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
