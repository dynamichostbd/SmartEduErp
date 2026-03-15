<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountHeadController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('account_heads')) {
            return response()->json([]);
        }

        $cols = Schema::getColumnListing('account_heads');
        $select = ['id'];
        foreach (['name', 'name_en', 'type', 'status', 'academic_qualification_ids', 'department_ids', 'academic_class_ids', 'created_at'] as $c) {
            if (in_array($c, $cols, true)) {
                $select[] = $c;
            }
        }

        $query = DB::table('account_heads')->select($select)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['name', 'name_en'], true) && in_array($field, $cols, true)) {
            $query->where($field, 'like', "%{$value}%");
        }

        if ($request->filled('status') && in_array('status', $cols, true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type') && in_array('type', $cols, true)) {
            $query->where('type', $request->input('type'));
        }

        if ($request->boolean('allData')) {
            $sel = ['id'];
            foreach (['type', 'name'] as $c) {
                if (in_array($c, $cols, true)) {
                    $sel[] = $c;
                }
            }
            return response()->json($query->select($sel)->get());
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('account_heads')) {
            return response()->json(['message' => 'Account Head module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
            'type' => ['required'],
        ]);

        $cols = Schema::getColumnListing('account_heads');
        $payload = [];

        foreach (['name', 'name_en', 'type', 'status'] as $c) {
            if ($request->has($c) && in_array($c, $cols, true)) {
                $payload[$c] = $request->input($c);
            }
        }

        foreach (['academic_qualification_ids', 'department_ids', 'academic_class_ids'] as $jsonCol) {
            if ($request->has($jsonCol) && in_array($jsonCol, $cols, true)) {
                $v = $request->input($jsonCol);
                $payload[$jsonCol] = is_array($v) ? json_encode(array_values($v)) : $v;
            }
        }

        if (!isset($payload['status']) && in_array('status', $cols, true)) {
            $payload['status'] = 'active';
        }

        if (in_array('created_at', $cols, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $cols, true)) {
            $payload['updated_at'] = now();
        }

        try {
            $id = DB::table('account_heads')->insertGetId($payload);
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

        if (!Schema::hasTable('account_heads')) {
            return response()->json([], 404);
        }

        $row = DB::table('account_heads')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json(['account_head' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('account_heads')) {
            return response()->json(['message' => 'Account Head module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
            'type' => ['required'],
        ]);

        $cols = Schema::getColumnListing('account_heads');
        $payload = [];

        foreach (['name', 'name_en', 'type', 'status'] as $c) {
            if ($request->has($c) && in_array($c, $cols, true)) {
                $payload[$c] = $request->input($c);
            }
        }

        foreach (['academic_qualification_ids', 'department_ids', 'academic_class_ids'] as $jsonCol) {
            if ($request->has($jsonCol) && in_array($jsonCol, $cols, true)) {
                $v = $request->input($jsonCol);
                $payload[$jsonCol] = is_array($v) ? json_encode(array_values($v)) : $v;
            }
        }

        if (in_array('updated_at', $cols, true)) {
            $payload['updated_at'] = now();
        }

        try {
            DB::table('account_heads')->where('id', (int) $id)->update($payload);
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to update', 'exception' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('account_heads')) {
            return response()->json(['message' => 'Account Head module not ready'], 422);
        }

        $cols = Schema::getColumnListing('account_heads');
        if (in_array('status', $cols, true)) {
            $ok = DB::table('account_heads')->where('id', (int) $id)->update(['status' => 'deactive']);
            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        }

        $ok = DB::table('account_heads')->where('id', (int) $id)->delete();
        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
