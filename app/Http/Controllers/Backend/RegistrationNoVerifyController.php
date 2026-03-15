<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Imports\RegistrationNoVerifiesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RegistrationNoVerifyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = DB::table('registration_no_verifies as v')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'v.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'v.academic_qualification_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'v.department_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'v.academic_class_id')
            ->select([
                'v.id',
                'v.academic_session_id',
                'v.academic_qualification_id',
                'v.department_id',
                'v.academic_class_id',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'cls.name as academic_class_name',
                'v.created_at',
            ])
            ->orderByDesc('v.id');

        if ($request->filled('academic_session_id')) {
            $query->where('v.academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('academic_qualification_id')) {
            $query->where('v.academic_qualification_id', $request->input('academic_qualification_id'));
        }
        if ($request->filled('department_id')) {
            $query->where('v.department_id', $request->input('department_id'));
        }
        if ($request->filled('academic_class_id')) {
            $query->where('v.academic_class_id', $request->input('academic_class_id'));
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
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
            'excel_file' => ['required', 'file'],
        ]);

        try {
            $data = $request->except('excel_file');
            Excel::import(new RegistrationNoVerifiesImport($data), $request->file('excel_file'));

            return response()->json(['message' => 'Create Successfully!'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to import', 'exception' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $row = DB::table('registration_no_verifies')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $session = DB::table('academic_sessions')->select('id', 'name')->where('id', $row->academic_session_id)->first();
        $qualification = DB::table('academic_qualifications')->select('id', 'name')->where('id', $row->academic_qualification_id)->first();
        $department = DB::table('departments')->select('id', 'name')->where('id', $row->department_id)->first();
        $class = DB::table('academic_classes')->select('id', 'name')->where('id', $row->academic_class_id)->first();

        $lists = $this->decodeLists($row->registration_no_lists);

        return response()->json([
            'id' => $row->id,
            'academic_session_id' => $row->academic_session_id,
            'academic_qualification_id' => $row->academic_qualification_id,
            'department_id' => $row->department_id,
            'academic_class_id' => $row->academic_class_id,
            'academic_session' => $session,
            'qualification' => $qualification,
            'department' => $department,
            'academic_class' => $class,
            'registration_no_lists' => $lists,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'registration_no_lists' => ['required', 'array'],
        ]);

        $row = DB::table('registration_no_verifies')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $existing = $this->decodeLists($row->registration_no_lists);
        $incoming = array_values($request->input('registration_no_lists') ?? []);
        $incoming = array_map(fn($v) => is_numeric($v) ? (int) $v : $v, $incoming);

        $merged = array_merge($existing, $incoming);

        DB::table('registration_no_verifies')
            ->where('id', $id)
            ->update([
                'registration_no_lists' => json_encode($merged),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Added Successfully!'], 200);
    }

    public function destroy($id)
    {
        $deleted = DB::table('registration_no_verifies')->where('id', $id)->delete();
        if ($deleted) {
            return response()->json(['message' => 'Delete Successfully!'], 200);
        }

        return response()->json(['error' => 'Delete Unsuccessfully!'], 200);
    }

    protected function decodeLists($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $str = (string) ($value ?? '');
        if ($str === '') {
            return [];
        }

        $decoded = json_decode($str, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [];
    }
}
