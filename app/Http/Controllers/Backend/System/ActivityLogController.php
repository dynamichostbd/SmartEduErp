<?php

namespace App\Http\Controllers\Backend\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivityLogController extends Controller
{
    private function conn(): string
    {
        return (string) (env('ACTIVITY_LOGGER_DB_CONNECTION') ?: 'mysql');
    }

    private function tableName(string $conn): ?string
    {
        $candidates = ['activity_log', 'activity_logs', 'activities'];
        foreach ($candidates as $t) {
            try {
                if (Schema::connection($conn)->hasTable($t)) {
                    return $t;
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $conn = $this->conn();
        $table = $this->tableName($conn);
        if (!$table) {
            return response()->json(DB::connection($conn)->table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $q = DB::connection($conn)->table($table)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'log_name');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['log_name', 'description'];
        if ($value !== '' && in_array($field, $allowed, true) && Schema::connection($conn)->hasColumn($table, $field)) {
            $q->where($field, 'like', "%{$value}%");
        }

        $description = trim((string) ($request->input('description') ?? ''));
        if ($description !== '' && Schema::connection($conn)->hasColumn($table, 'description')) {
            $q->where('description', 'like', "%{$description}%");
        }

        $action = trim((string) ($request->input('action') ?? ''));
        if ($action !== '' && Schema::connection($conn)->hasColumn($table, 'event')) {
            $q->where('event', $action);
        }

        $status = trim((string) ($request->input('status') ?? ''));
        if ($status !== '' && Schema::connection($conn)->hasColumn($table, 'status')) {
            $q->where('status', $status);
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if (!empty($from) && Schema::connection($conn)->hasColumn($table, 'created_at')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if (!empty($to) && Schema::connection($conn)->hasColumn($table, 'created_at')) {
            $q->whereDate('created_at', '<=', $to);
        }

        return response()->json($q->paginate($perPage));
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $conn = $this->conn();
        $table = $this->tableName($conn);
        if (!$table) {
            return response()->json([], 404);
        }

        $row = DB::connection($conn)->table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        if (Schema::connection($conn)->hasColumn($table, 'status')) {
            DB::connection($conn)->table($table)->where('id', (int) $id)->update(['status' => 'r']);
            $row = DB::connection($conn)->table($table)->where('id', (int) $id)->first();
        }

        return response()->json($row);
    }

    public function destroy(Request $request, $id)
    {
        $conn = $this->conn();
        $table = $this->tableName($conn);
        if (!$table) {
            return response()->json(['message' => 'Activity log not ready'], 422);
        }

        DB::connection($conn)->table($table)->where('id', (int) $id)->delete();

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    public function allRead(Request $request)
    {
        $conn = $this->conn();
        $table = $this->tableName($conn);
        if (!$table) {
            return response()->json(['message' => 'Activity log not ready'], 422);
        }

        if (!Schema::connection($conn)->hasColumn($table, 'status')) {
            return response()->json(['message' => 'Read Successfully!'], 200);
        }

        DB::connection($conn)->table($table)->where('status', 'ur')->update(['status' => 'r']);

        return response()->json(['message' => 'Read Successfully!'], 200);
    }

    public function admins(Request $request)
    {
        if (!Schema::hasTable('admins')) {
            return response()->json([]);
        }

        return response()->json(DB::table('admins')->select('id', 'name')->orderBy('name')->get());
    }
}
