<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeaveApplicationController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('leave_applications') ? 'leave_applications' : null;
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

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $table = $this->table();
        if (!$table) {
            return $this->emptyPaginator($perPage);
        }

        $q = DB::table($table)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'id');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['id'];
        if ($value !== '' && in_array($field, $allowed, true) && Schema::hasColumn($table, $field)) {
            $q->where($field, 'like', "%{$value}%");
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Leave Application module not ready'], 422);
        }

        $request->validate([
            'admin_id' => ['required'],
        ]);

        $data = [];
        foreach (Schema::getColumnListing($table) as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if ($request->has($col)) {
                $data[$col] = $request->input($col);
            }
        }

        if (Schema::hasColumn($table, 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        $id = DB::table($table)->insertGetId($data);
        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
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

        return response()->json(['leave_application' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Leave Application module not ready'], 422);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Leave application not found'], 404);
        }

        $data = [];
        foreach (Schema::getColumnListing($table) as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }
            if ($request->has($col)) {
                $data[$col] = $request->input($col);
            }
        }

        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table($table)->where('id', (int) $id)->update($data);
        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy($id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Leave Application module not ready'], 422);
        }

        $ok = (bool) DB::table($table)->where('id', (int) $id)->delete();
        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
