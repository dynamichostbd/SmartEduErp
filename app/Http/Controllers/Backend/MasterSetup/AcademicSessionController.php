<?php

namespace App\Http\Controllers\Backend\MasterSetup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicSessionController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('academic_sessions') ? 'academic_sessions' : null;
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

        $q = DB::table($table)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['name'], true) && Schema::hasColumn($table, $field)) {
            $q->where($field, 'like', "%{$value}%");
        }

        if ($request->has('status') && Schema::hasColumn($table, 'status')) {
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
            return response()->json(['message' => 'Academic Session module not ready'], 422);
        }

        $request->validate([
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

        foreach (['current', 'online_admission', 'registration', 'application_fee'] as $flag) {
            if (in_array($flag, $columns, true)) {
                $payload[$flag] = (int) ($request->input($flag) ? 1 : 0);
            }
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

        if (!isset($payload['status']) && in_array('status', $columns, true)) {
            $payload['status'] = 'active';
        }

        DB::beginTransaction();
        try {
            if (($payload['current'] ?? 0) && in_array('current', $columns, true)) {
                DB::table($table)->where('current', 1)->update(['current' => 0]);
            }

            if (in_array('created_at', $columns, true)) {
                $payload['created_at'] = now();
            }
            if (in_array('updated_at', $columns, true)) {
                $payload['updated_at'] = now();
            }

            $id = DB::table($table)->insertGetId($payload);

            DB::commit();

            $this->flushCaches();

            return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

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
            return response()->json(['message' => 'Academic Session module not ready'], 422);
        }

        $row = DB::table($table)->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(['academic_session' => $row]);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Academic Session module not ready'], 422);
        }

        $request->validate([
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

        foreach (['current', 'online_admission', 'registration', 'application_fee'] as $flag) {
            if (in_array($flag, $columns, true)) {
                $payload[$flag] = (int) ($request->input($flag) ? 1 : 0);
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

        DB::beginTransaction();
        try {
            if (($payload['current'] ?? 0) && in_array('current', $columns, true)) {
                DB::table($table)->where('current', 1)->where('id', '!=', $id)->update(['current' => 0]);
            }

            DB::table($table)->where('id', $id)->update($payload);

            DB::commit();

            $this->flushCaches();

            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

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
            return response()->json(['message' => 'Academic Session module not ready'], 422);
        }

        $columns = Schema::getColumnListing($table);

        if (in_array('status', $columns, true)) {
            $ok = DB::table($table)->where('id', $id)->update([
                'status' => 'deactive',
                'updated_at' => in_array('updated_at', $columns, true) ? now() : DB::raw('updated_at'),
            ]);

            if ($ok) {
                $this->flushCaches();
            }

            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        }

        $ok = DB::table($table)->where('id', $id)->delete();

        if ($ok) {
            $this->flushCaches();
        }

        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }

    private function flushCaches(): void
    {
        Cache::forget('dynamic_data_cache');
    }
}
