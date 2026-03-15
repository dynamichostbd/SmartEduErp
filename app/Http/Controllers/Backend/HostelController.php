<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HostelController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('hostels') ? 'hostels' : null;
    }

    private function emptyPaginator(int $perPage)
    {
        return response()->json([
            'current_page' => 1,
            'data' => [],
            'from' => null,
            'last_page' => 1,
            'per_page' => $perPage,
            'to' => null,
            'total' => 0,
        ]);
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
            return $this->emptyPaginator($perPage);
        }

        $cols = Schema::getColumnListing($table);
        $q = DB::table($table)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['name'], true) && in_array($field, $cols, true)) {
            $q->where($field, 'like', "%{$value}%");
        }

        $status = $request->input('status');
        if ($status !== null && $status !== '' && in_array('status', $cols, true)) {
            $q->where('status', $status);
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Hostel module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
        ]);

        $cols = Schema::getColumnListing($table);
        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        $payload = [];
        foreach ($cols as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (!isset($payload['status']) && in_array('status', $cols, true)) {
            $payload['status'] = 'active';
        }

        if (in_array('created_by', $cols, true)) {
            $payload['created_by'] = $admin->name ?? null;
        }
        if (in_array('created_ip', $cols, true)) {
            $payload['created_ip'] = $ip;
        }
        if (in_array('updated_by', $cols, true)) {
            $payload['updated_by'] = $admin->name ?? null;
        }
        if (in_array('updated_ip', $cols, true)) {
            $payload['updated_ip'] = $ip;
        }
        if (in_array('created_at', $cols, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $cols, true)) {
            $payload['updated_at'] = now();
        }

        try {
            $id = DB::table($table)->insertGetId($payload);
            return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to create', 'exception' => $e->getMessage()], 422);
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

        return response()->json(['hostel' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Hostel module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
        ]);

        $cols = Schema::getColumnListing($table);
        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        $payload = [];
        foreach ($cols as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (in_array('updated_by', $cols, true)) {
            $payload['updated_by'] = $admin->name ?? null;
        }
        if (in_array('updated_ip', $cols, true)) {
            $payload['updated_ip'] = $ip;
        }
        if (in_array('updated_at', $cols, true)) {
            $payload['updated_at'] = now();
        }

        try {
            DB::table($table)->where('id', (int) $id)->update($payload);
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to update', 'exception' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Hostel module not ready'], 422);
        }

        $cols = Schema::getColumnListing($table);
        if (in_array('status', $cols, true)) {
            $ok = DB::table($table)->where('id', (int) $id)->update(['status' => 'deactive']);
            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        }

        $ok = DB::table($table)->where('id', (int) $id)->delete();
        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
