<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CertificateApplicationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = DB::table('certificate_applications as a')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'a.academic_qualification_id')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'a.academic_session_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'a.department_id')
            ->leftJoin('certificate_templates as t', 't.id', '=', 'a.certificate_template_id')
            ->leftJoin('account_heads as h', 'h.id', '=', 'a.account_head_id')
            ->select([
                'a.*',
                'q.name as academic_qualification_name',
                'ses.name as academic_session_name',
                'dept.name as department_name',
                't.title as template_title',
                'h.name as head_name',
            ])
            ->orderByDesc('a.id');

        $field = (string) ($request->input('field_name') ?? '');
        $value = (string) ($request->input('value') ?? '');
        $fieldAllowed = ['mobile', 'student_name_en', 'registration_no_en', 'college_roll_en', 'invoice_number'];
        if ($field !== '' && $value !== '' && in_array($field, $fieldAllowed, true)) {
            $query->where('a.' . $field, 'like', '%' . $value . '%');
        }

        foreach (['payment_status', 'application_status', 'academic_qualification_id', 'academic_session_id', 'department_id', 'certificate_template_id'] as $f) {
            if ($request->filled($f)) {
                $query->where('a.' . $f, $request->input($f));
            }
        }

        $datas = $query->paginate($perPage);

        $items = collect($datas->items())->map(function ($row) {
            $row->qualification = ['id' => $row->academic_qualification_id, 'name' => $row->academic_qualification_name];
            $row->academic_session = ['id' => $row->academic_session_id, 'name' => $row->academic_session_name];
            $row->department = ['id' => $row->department_id, 'name' => $row->department_name];
            $row->template = ['id' => $row->certificate_template_id, 'title' => $row->template_title];
            $row->head = ['id' => $row->account_head_id, 'name' => $row->head_name];
            return $row;
        });

        return response()->json([
            'data' => $items->values(),
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

    public function show($id)
    {
        $row = DB::table('certificate_applications')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $qualification = DB::table('academic_qualifications')->select('id', 'name')->where('id', $row->academic_qualification_id)->first();
        $session = DB::table('academic_sessions')->select('id', 'name')->where('id', $row->academic_session_id)->first();
        $department = $row->department_id ? DB::table('departments')->select('id', 'name')->where('id', $row->department_id)->first() : null;
        $head = Schema::hasTable('account_heads') ? DB::table('account_heads')->select('id', 'name')->where('id', $row->account_head_id)->first() : null;

        $templateRow = DB::table('certificate_templates')->where('id', $row->certificate_template_id)->first();
        $template = $templateRow ? [
            'id' => $templateRow->id,
            'title' => $templateRow->title,
            'print_layout' => $templateRow->print_layout,
            'bg_en_image' => $this->assetUrl($templateRow->bg_en_image),
            'bg_bn_image' => $this->assetUrl($templateRow->bg_bn_image),
            'en_template_json' => $this->decodeTemplateJson($templateRow->en_template_json),
            'bn_template_json' => $this->decodeTemplateJson($templateRow->bn_template_json),
        ] : ['en_template_json' => [], 'bn_template_json' => []];

        [$principalName, $principalIndex] = $this->principalInfo();

        return response()->json(array_merge((array) $row, [
            'qualification' => $qualification,
            'academic_session' => $session,
            'department' => $department,
            'template' => $template,
            'head' => $head,
            'principal_name' => $principalName,
            'principal_index' => $principalIndex,
            'serial_number' => $row->id,
        ]));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'certificate_type' => ['required'],
            'mobile' => ['required'],
            'certificate_template_id' => ['required'],
            'academic_session_id' => ['required'],
            'academic_qualification_id' => ['required'],
            'student_name_en' => ['required_if:certificate_type,en,both'],
            'student_name_bn' => ['required_if:certificate_type,bn,both'],
            'fathers_name_en' => ['required_if:certificate_type,en,both'],
            'fathers_name_bn' => ['required_if:certificate_type,bn,both'],
            'mothers_name_en' => ['required_if:certificate_type,en,both'],
            'mothers_name_bn' => ['required_if:certificate_type,bn,both'],
            'academic_year_en' => ['required_if:certificate_type,en,both'],
            'academic_year_bn' => ['required_if:certificate_type,bn,both'],
            'registration_no_en' => ['required_if:certificate_type,en,both'],
            'registration_no_bn' => ['required_if:certificate_type,bn,both'],
            'exam_roll_en' => ['required_if:certificate_type,en,both'],
            'exam_roll_bn' => ['required_if:certificate_type,bn,both'],
            'exam_year_en' => ['required_if:certificate_type,en,both'],
            'exam_year_bn' => ['required_if:certificate_type,bn,both'],
            'gpa_en' => ['required_if:certificate_type,en,both'],
            'gpa_bn' => ['required_if:certificate_type,bn,both'],
            'division_en' => ['required_if:certificate_type,en,both'],
            'division_bn' => ['required_if:certificate_type,bn,both'],
        ]);

        DB::table('certificate_applications')
            ->where('id', $id)
            ->update($request->except(['id']) + ['updated_at' => now()]);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy($id)
    {
        $row = DB::table('certificate_applications')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (($row->application_status ?? '') === 'rejected' && ($row->payment_status ?? '') !== 'success') {
            DB::table('certificate_applications')->where('id', $id)->delete();
            return response()->json(['message' => 'Application delete Successfully!'], 200);
        }

        DB::table('certificate_applications')->where('id', $id)->update([
            'application_status' => 'rejected',
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Application reject Successfully!'], 200);
    }

    public function approved(Request $request)
    {
        $request->validate(['id' => ['required', 'integer']]);

        $admin = Auth::guard('admin')->user();

        DB::table('certificate_applications')
            ->where('id', $request->input('id'))
            ->update([
                'approved_by' => $admin?->id,
                'approved_by_name' => $admin?->name,
                'approved_date' => date('Y-m-d'),
                'application_status' => 'approved',
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Approved Successfully!'], 200);
    }

    public function printStatus(Request $request)
    {
        $request->validate(['id' => ['required', 'integer']]);

        $row = DB::table('certificate_applications')->where('id', $request->input('id'))->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $count = ((int) ($row->number_of_print ?? 0)) + 1;

        DB::table('certificate_applications')
            ->where('id', $row->id)
            ->update([
                'print_date' => date('Y-m-d'),
                'number_of_print' => $count,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Print Status Update Successfully!'], 200);
    }

    protected function decodeTemplateJson($val): array
    {
        if (empty($val)) {
            return [];
        }
        $decoded = is_string($val) ? json_decode($val, true) : $val;
        return is_array($decoded) ? $decoded : [];
    }

    protected function assetUrl($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = ltrim((string) $value, '/');

        $filePath2 = public_path('storage/' . $value);
        if (file_exists($filePath2)) {
            return url('storage/' . $value);
        }

        $filePath1 = public_path($value);
        if (file_exists($filePath1)) {
            return url($value);
        }

        $bucketUrl = env('DO_ASSET_URL', 'https://smartedubd.blr1.cdn.digitaloceanspaces.com/blr1_storage');
        $bucketUrl = rtrim($bucketUrl, '/');

        return "$bucketUrl/$value";
    }

    protected function principalInfo(): array
    {
        if (!Schema::hasTable('designations') || !Schema::hasTable('teachers')) {
            return ['', ''];
        }

        $designationId = DB::table('designations')->where('name', 'Principal')->value('id');
        if (!$designationId) {
            return ['', ''];
        }

        $teacher = DB::table('teachers')->where('designation_id', $designationId)->orderByDesc('id')->first();
        if (!$teacher) {
            return ['', ''];
        }

        $principalIndex = $teacher->index_number ?? '';
        $principalName = '';

        if (!empty($teacher->admin_id) && Schema::hasTable('admins')) {
            $principalName = (string) (DB::table('admins')->where('id', $teacher->admin_id)->value('name') ?? '');
        }

        return [$principalName, $principalIndex];
    }
}
