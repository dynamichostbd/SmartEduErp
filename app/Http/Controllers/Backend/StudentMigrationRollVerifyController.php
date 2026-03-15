<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Imports\StudentMigrationRollVerifiesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StudentMigrationRollVerifyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = DB::table('student_migration_roll_verifies as v')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'v.academic_session_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'v.migrate_department_id')
            ->select([
                DB::raw('MIN(v.id) as id'),
                'v.academic_session_id',
                'v.migrate_department_id',
                DB::raw('ses.name as academic_session_name'),
                DB::raw('dept.name as migrate_department_name'),
            ])
            ->groupBy('v.academic_session_id', 'v.migrate_department_id', 'ses.name', 'dept.name')
            ->orderByDesc(DB::raw('MIN(v.id)'));

        if ($request->filled('academic_session_id')) {
            $query->where('v.academic_session_id', $request->input('academic_session_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('v.migrate_department_id', $request->input('department_id'));
        }

        $datas = $query->paginate($perPage);

        return response()->json([
            'data' => $datas->items(),
            'meta' => [
                'current_page' => $datas->currentPage(),
                'from' => $datas->firstItem(),
                'to' => $datas->lastItem(),
                'per_page' => $datas->perPage(),
                'total' => $datas->total(),
                'last_page' => $datas->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required'],
            'migrate_department_id' => ['required'],
            'excel_file' => ['required', 'file'],
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except('excel_file');
            Excel::import(new StudentMigrationRollVerifiesImport($data), $request->file('excel_file'));
            DB::commit();

            return response()->json(['message' => 'Create Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to import', 'exception' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $item = DB::table('student_migration_roll_verifies')->where('id', $id)->first();
        if (!$item) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $rollLists = DB::table('student_migration_roll_verifies')
            ->where([
                'migrate_department_id' => $item->migrate_department_id,
                'academic_session_id' => $item->academic_session_id,
            ])
            ->orderBy('admission_roll')
            ->get();

        $session = DB::table('academic_sessions')->select('id', 'name')->where('id', $item->academic_session_id)->first();
        $department = DB::table('departments')->select('id', 'name')->where('id', $item->migrate_department_id)->first();

        return response()->json([
            'item' => [
                'id' => $item->id,
                'academic_session_id' => $item->academic_session_id,
                'migrate_department_id' => $item->migrate_department_id,
                'academic_session' => $session,
                'department' => $department,
            ],
            'roll_lists' => $rollLists,
        ]);
    }

    public function destroy($id)
    {
        $item = DB::table('student_migration_roll_verifies')->where('id', $id)->first();
        if (!$item) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $deleted = DB::table('student_migration_roll_verifies')
            ->where([
                'migrate_department_id' => $item->migrate_department_id,
                'academic_session_id' => $item->academic_session_id,
            ])
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Delete Successfully!'], 200);
        }

        return response()->json(['error' => 'Delete Unsuccessfully!'], 200);
    }
}
