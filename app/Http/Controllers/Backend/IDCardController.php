<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IDCardController extends Controller
{
    public function index(Request $request)
    {
        $take = (int) ($request->input('take') ?? 100);
        $take = $take > 0 ? min($take, 2000) : 100;

        $skip = (int) ($request->input('skip') ?? 0);
        $skip = $skip >= 0 ? $skip : 0;

        $orderBy = strtolower((string) ($request->input('order_by') ?? 'asc'));
        $orderBy = in_array($orderBy, ['asc', 'desc'], true) ? $orderBy : 'asc';

        $admin = Auth::guard('admin')->user();
        $departmentId = $request->input('department_id') ?: ($admin->department_id ?? null);

        $query = Student::query()
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'students.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'students.academic_qualification_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'students.department_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'students.academic_class_id')
            ->select([
                'students.id',
                'students.academic_session_id',
                'students.department_id',
                'students.academic_qualification_id',
                'students.academic_class_id',
                'students.student_id',
                'students.mothers_name',
                'students.fathers_name',
                'students.dob',
                'students.address',
                'students.blood_group',
                'students.name',
                'students.gender',
                'students.mobile',
                'students.admission_id',
                'students.college_roll',
                'students.guardian_mobile',
                'students.profile',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'cls.name as academic_class_name',
            ])
            ->where('students.status', 'active')
            ->whereNotNull('students.profile')
            ->where('students.profile', '!=', '');

        $field = (string) ($request->input('field_name') ?? '');
        $value = $request->input('value');

        if ($value !== null && $value !== '' && $field !== '' && $field !== '0') {
            if ($field === 'admission_id') {
                $query->where('students.admission_id', $value);
            } else {
                $query->where('students.' . $field, 'like', '%' . $value . '%');
            }
        }

        if ($request->filled('academic_class_id')) {
            $query->where('students.academic_class_id', $request->input('academic_class_id'));
        }
        if ($request->filled('academic_session_id')) {
            $query->where('students.academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('academic_qualification_id')) {
            $query->where('students.academic_qualification_id', $request->input('academic_qualification_id'));
        }
        if ($departmentId !== null && $departmentId !== '') {
            $query->where('students.department_id', $departmentId);
        }

        if ($request->boolean('custom_rolls')) {
            $rolls = (string) ($request->input('roll_lists') ?? '');
            $rolls = str_replace(' ', '', $rolls);
            $rolls = array_values(array_filter(explode(',', $rolls)));
            $rolls = array_slice($rolls, 0, 100);
            if (count($rolls) > 0) {
                $query->whereIn('students.college_roll', $rolls);
            }
        }

        if ($request->boolean('custom_mobiles')) {
            $mobiles = (string) ($request->input('mobile_lists') ?? '');
            $mobiles = str_replace(' ', '', $mobiles);
            $mobiles = array_values(array_filter(explode(',', $mobiles)));
            $mobiles = array_map(function ($mobile) {
                return preg_replace('/[^0-9]/', '', (string) $mobile);
            }, $mobiles);
            $mobiles = array_values(array_filter($mobiles));
            $mobiles = array_slice($mobiles, 0, 100);
            if (count($mobiles) > 0) {
                $query->whereIn('students.mobile', $mobiles);
            }
        }

        $students = $query
            ->orderBy('students.id', $orderBy)
            ->skip($skip)
            ->take($take)
            ->get();

        $students->transform(function ($s) {
            $s->qr_text = $this->qrText($s);
            $s->qr_code = $this->qrSvgBase64($s->qr_text);
            return $s;
        });

        return response()->json($students);
    }

    protected function qrText($student): string
    {
        $address = (string) ($student->address ?? '');
        $address = preg_replace('/[[:^print:]]/', '', $address);

        $info = [
            'ID: ' . ($student->student_id ?? ''),
            'Guardian Mobile: ' . ($student->guardian_mobile ?? ''),
            'Address: ' . $address,
        ];

        return implode("\n", $info);
    }

    protected function qrSvgBase64(string $text): ?string
    {
        if ($text === '') {
            return null;
        }

        if (!class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            return null;
        }

        try {
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(120)
                ->margin(0)
                ->generate($text);

            return base64_encode($svg);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
