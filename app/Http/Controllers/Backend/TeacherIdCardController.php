<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\System\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherIdCardController extends Controller
{
    private function columnsCached(string $table): array
    {
        $key = 'schema_cols_' . $table;
        return Cache::rememberForever($key, function () use ($table) {
            if (!Schema::hasTable($table)) {
                return [];
            }

            try {
                return Schema::getColumnListing($table);
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    private function adminProfileUrl(?string $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $filePath1 = public_path($value);
        if (file_exists($filePath1)) {
            return url($value);
        }

        $filePath2 = public_path('storage/upload/' . $value);
        if (file_exists($filePath2)) {
            return url('storage/upload/' . $value);
        }

        $bucketUrl = env('DO_ASSET_URL', 'https://smartedubd.blr1.cdn.digitaloceanspaces.com/blr1_storage');
        $bucketUrl = rtrim((string) $bucketUrl, '/');
        $value = ltrim($value, '/');

        return $bucketUrl !== '' ? "{$bucketUrl}/{$value}" : null;
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $take = (int) ($request->input('take') ?? 100);
        $take = $take > 0 ? min($take, 2000) : 100;

        $skip = (int) ($request->input('skip') ?? 0);
        $skip = $skip >= 0 ? $skip : 0;

        $orderBy = strtolower((string) ($request->input('order_by') ?? 'asc'));
        $orderBy = in_array($orderBy, ['asc', 'desc'], true) ? $orderBy : 'asc';

        if (!Schema::hasTable('admins')) {
            return response()->json([]);
        }

        $adminCols = $this->columnsCached('admins');
        $adminColSet = !empty($adminCols) ? array_fill_keys($adminCols, true) : [];
        $hasAdminCol = function (string $c) use ($adminColSet): bool {
            return !empty($adminColSet) && isset($adminColSet[$c]);
        };

        $auth = Auth::guard('admin')->user();
        $deptID = $auth->department_id ?? null;
        $departmentId = $request->input('department_id');
        $departmentId = ($departmentId !== null && $departmentId !== '') ? $departmentId : $deptID;

        $q = DB::table('admins as a')
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->leftJoin('teachers as t', function ($j) {
                $j->on('t.admin_id', '=', 'a.id');
            })
            ->leftJoin('designations as des', function ($j) {
                $j->on('des.id', '=', 't.designation_id');
            });

        if ($hasAdminCol('type')) {
            $q->where('a.type', 'Teacher');
        }

        if ($hasAdminCol('status')) {
            $q->where('a.status', 'active');
        }

        if ($hasAdminCol('profile')) {
            $q->whereNotNull('a.profile')->where('a.profile', '!=', '');
        }

        if ($departmentId !== null && $departmentId !== '' && $hasAdminCol('department_id')) {
            $q->where('a.department_id', $departmentId);
        }

        $field = (string) ($request->input('field_name') ?? '');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['name', 'mobile', 'email'];
        if ($value !== '' && $field !== '' && $field !== '0' && in_array($field, $allowed, true) && $hasAdminCol($field)) {
            $q->where('a.' . $field, 'like', '%' . $value . '%');
        }

        $academicQualificationId = $request->input('academic_qualification_id');
        if ($academicQualificationId !== null && $academicQualificationId !== '' && Schema::hasTable('teacher_subject_assigns') && Schema::hasColumn('teacher_subject_assigns', 'admin_id') && Schema::hasColumn('teacher_subject_assigns', 'academic_qualification_id')) {
            $q->whereExists(function ($sq) use ($academicQualificationId) {
                $sq->select(DB::raw(1))
                    ->from('teacher_subject_assigns as tsa')
                    ->whereColumn('tsa.admin_id', 'a.id')
                    ->where('tsa.academic_qualification_id', $academicQualificationId);
            });
        }

        $select = [
            'a.id',
            'a.name',
            'a.mobile',
            'a.email',
            'a.profile',
            'a.emergency_contacts',
            'a.department_id',
            'd.id as dept_row_id',
            'd.name as dept_name',
            't.index_number',
            't.bcs_batch',
            't.blood_group',
            't.designation_id',
            'des.id as des_row_id',
            'des.name as des_name',
        ];

        $rows = $q
            ->select($select)
            ->orderBy('a.id', $orderBy)
            ->skip($skip)
            ->take($take)
            ->get();

        $site = null;
        if (Schema::hasTable('site_settings')) {
            $site = SiteSetting::query()->first();
        }

        $rows->transform(function ($row) use ($site) {
            $item = [
                'id' => (int) ($row->id ?? 0),
                'name' => $row->name ?? null,
                'mobile' => $row->mobile ?? null,
                'email' => $row->email ?? null,
                'profile' => $this->adminProfileUrl($row->profile ?? null),
                'emergency_contacts' => $row->emergency_contacts ?? null,
                'department_id' => $row->department_id ?? null,
                'department' => ($row->dept_row_id ?? null) ? [
                    'id' => (int) ($row->dept_row_id ?? 0),
                    'name' => $row->dept_name ?? null,
                ] : null,
                'teacher' => [
                    'index_number' => $row->index_number ?? null,
                    'bcs_batch' => $row->bcs_batch ?? null,
                    'blood_group' => $row->blood_group ?? null,
                    'designation' => ($row->des_row_id ?? null) ? [
                        'id' => (int) ($row->des_row_id ?? 0),
                        'name' => $row->des_name ?? null,
                    ] : null,
                ],
            ];

            $item['qr_code'] = $this->qrSvgBase64($this->qrText($item, $site));

            return $item;
        });

        return response()->json($rows);
    }

    private function qrText(array $item, $site = null): string
    {
        $teacherId = data_get($item, 'teacher.index_number') ?: (string) (data_get($item, 'id') ?? '');
        $name = (string) (data_get($item, 'name') ?? '');
        $designation = (string) (data_get($item, 'teacher.designation.name') ?? '');
        $department = (string) (data_get($item, 'department.name') ?? '');
        $college = $site ? (string) ($site->college_name ?? '') : '';
        $mobile = (string) (data_get($item, 'mobile') ?? '');
        $email = (string) (data_get($item, 'email') ?? '');

        $info = [
            'ID: ' . $teacherId,
            'Name: ' . $name,
            'Designation: ' . $designation,
            'Department: ' . $department,
            'College Name: ' . $college,
            'Mobile: ' . $mobile,
            'Email: ' . $email,
        ];

        return implode("\n", $info);
    }

    private function qrSvgBase64(string $text): ?string
    {
        if ($text === '') {
            return null;
        }

        if (!class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            return null;
        }

        try {
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(300)
                ->margin(0)
                ->generate($text);

            return base64_encode($svg);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
