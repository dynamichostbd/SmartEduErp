<?php

namespace App\Http\Controllers\Backend\SMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Traits\SmsGatewayTrait;

class SmsHistoryController extends Controller
{
    use SmsGatewayTrait;

    private function table(): ?string
    {
        return Schema::hasTable('sms_histories') ? 'sms_histories' : null;
    }

    private function templateTable(): ?string
    {
        return Schema::hasTable('sms_templates') ? 'sms_templates' : null;
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
        $tpl = $this->templateTable();
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        if (!$table || !$tpl) {
            return $this->emptyPaginator($perPage);
        }

        $q = DB::table("{$table} as h")
            ->leftJoin("{$tpl} as t", 't.id', '=', 'h.sms_template_id')
            ->select([
                'h.*',
                't.name as sms_template_name',
            ])
            ->orderByDesc('h.id');

        if ($request->filled('sms_template_id')) {
            $q->where('h.sms_template_id', $request->input('sms_template_id'));
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if ($from) {
            $q->whereDate('h.date', '>=', $from);
        }
        if ($to) {
            $q->whereDate('h.date', '<=', $to);
        }

        return response()->json($q->paginate($perPage));
    }

    public function students(Request $request)
    {
        $type = (string) ($request->input('sending_type') ?? 'students');

        if ($type === 'applicants') {
            if (!Schema::hasTable('online_admissions')) {
                return response()->json([]);
            }

            $q = DB::table('online_admissions')->select('id', 'name', 'admission_roll', 'mobile');
            if (Schema::hasColumn('online_admissions', 'status') && $request->filled('status')) {
                $q->where('status', $request->input('status'));
            }

            foreach (['academic_session_id', 'academic_qualification_id', 'department_id', 'academic_class_id'] as $f) {
                if ($request->filled($f) && Schema::hasColumn('online_admissions', $f)) {
                    $q->where($f, $request->input($f));
                }
            }

            return response()->json($q->orderBy('id')->limit(2000)->get());
        }

        if (!Schema::hasTable('students')) {
            return response()->json([]);
        }

        $q = DB::table('students')->select('id', 'name', 'college_roll', 'mobile');
        if (Schema::hasColumn('students', 'status') && $request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        foreach (['academic_session_id', 'academic_qualification_id', 'department_id', 'academic_class_id'] as $f) {
            if ($request->filled($f) && Schema::hasColumn('students', $f)) {
                $q->where($f, $request->input($f));
            }
        }

        return response()->json($q->orderBy('id')->limit(2000)->get());
    }

    public function store(Request $request)
    {
        $tplTable = $this->templateTable();
        if (!$tplTable) {
            return response()->json(['message' => 'SMS module not ready'], 422);
        }

        $request->validate([
            'sending_type' => ['required'],
            'sms_template_id' => ['required'],
        ]);

        $sendingType = (string) $request->input('sending_type');
        if ($sendingType === 'any') {
            $request->validate([
                'any_numbers' => ['required'],
            ]);
        } else {
            $request->validate([
                'academic_session_id' => ['required'],
                'academic_qualification_id' => ['required'],
                'department_id' => ['required'],
                'academic_class_id' => ['required'],
            ]);
        }

        $template = DB::table($tplTable)->where('id', (int) $request->input('sms_template_id'))->first();
        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $cfgError = $this->smsGatewayConfigError();
        if ($cfgError) {
            return response()->json(['message' => $cfgError], 422);
        }

        $commonMessage = (int) ($template->common_message ?? 0) === 1;
        $smsType = (string) ($template->sms_type ?? '');
        $smsBody = (string) ($template->sms_body ?? '');

        $contacts = [];

        if ($sendingType === 'any') {
            $raw = (string) $request->input('any_numbers');
            $parts = array_map('trim', preg_split('/[\n,]+/', $raw) ?: []);
            $contacts = array_values(array_filter($parts));

            if (count($contacts) === 0) {
                return response()->json(['message' => 'No numbers provided'], 422);
            }

            $message = $this->renderTemplate($smsBody, [], null);
            $this->sendSmsViaGateway(implode(',', $contacts), $message);
        } else {
            $status = $sendingType === 'applicants' ? 'approved' : 'active';
            $list = $this->students(new Request(array_merge($request->all(), ['sending_type' => $sendingType, 'status' => $status])))->getData(true);
            $students = is_array($list) ? $list : [];

            $selected = $request->input('student_ids');
            if (is_array($selected) && count($selected) > 0) {
                $selectedSet = array_flip(array_map('intval', $selected));
                $students = array_values(array_filter($students, fn ($s) => isset($selectedSet[(int) ($s['id'] ?? $s->id ?? 0)])));
            }

            if (count($students) === 0) {
                return response()->json(['message' => 'No students found'], 422);
            }

            if ($commonMessage) {
                $contacts = array_values(array_filter(array_map(fn ($s) => (string) ($s['mobile'] ?? $s->mobile ?? ''), $students)));
                $message = $this->renderTemplate($smsBody, [], null);
                $this->sendSmsViaGateway(implode(',', $contacts), $message);
            } else {
                foreach ($students as $s) {
                    $mobile = (string) ($s['mobile'] ?? $s->mobile ?? '');
                    if ($mobile === '') {
                        continue;
                    }

                    $params = [];
                    if (str_contains($smsBody, '[_Password_]')) {
                        $params['password'] = (string) rand(111111, 999999);
                        if ($sendingType !== 'applicants' && Schema::hasTable('students') && Schema::hasColumn('students', 'password')) {
                            DB::table('students')->where('id', (int) ($s['id'] ?? $s->id ?? 0))->update(['password' => $params['password']]);
                        }
                    }

                    $message = $this->renderTemplate($smsBody, $params, $s);
                    $this->sendSmsViaGateway($mobile, $message);
                    $contacts[] = $mobile;
                }
            }
        }

        $count = count($contacts);

        $historyTable = $this->table();
        if ($historyTable) {
            $cols = Schema::getColumnListing($historyTable);
            $site = Schema::hasTable('site_settings') ? DB::table('site_settings')->first() : null;
            $smsCost = $site && isset($site->sms_cost) ? (float) $site->sms_cost : 0;

            $row = [];
            if (in_array('date', $cols, true)) {
                $row['date'] = date('Y-m-d');
            }
            if (in_array('sms_template_id', $cols, true)) {
                $row['sms_template_id'] = (int) $request->input('sms_template_id');
            }
            if (in_array('sms_cost', $cols, true)) {
                $row['sms_cost'] = $smsCost;
            }
            if (in_array('total_sending_sms', $cols, true)) {
                $row['total_sending_sms'] = $count;
            }
            if (in_array('created_at', $cols, true)) {
                $row['created_at'] = now();
            }
            if (in_array('updated_at', $cols, true)) {
                $row['updated_at'] = now();
            }

            if (!empty($row)) {
                DB::table($historyTable)->insert($row);
            }
        }

        return response()->json(['message' => 'Send Successfully!'], 200);
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->table();
        $tpl = $this->templateTable();
        if (!$table || !$tpl) {
            return response()->json([], 404);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        $template = DB::table($tpl)->select('id', 'name', 'sms_type')->where('id', (int) ($row->sms_template_id ?? 0))->first();

        return response()->json([
            'sms_history' => $row,
            'sms_template' => $template,
        ], 200);
    }

    public function destroy($id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'SMS History module not ready'], 422);
        }

        $ok = DB::table($table)->where('id', (int) $id)->delete();
        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }

    private function renderTemplate(string $smsBody, array $params, $student): string
    {
        $name = (string) ($student['name'] ?? $student->name ?? '');
        $mobile = (string) ($student['mobile'] ?? $student->mobile ?? '');
        $email = (string) ($student['email'] ?? $student->email ?? '');
        $collegeRoll = (string) ($student['college_roll'] ?? $student->college_roll ?? '');

        $smsBody = str_replace('[_Student_Name_]', $name, $smsBody);
        $smsBody = str_replace('[_Mobile_]', $mobile, $smsBody);
        $smsBody = str_replace('[_Email_]', $email, $smsBody);
        $smsBody = str_replace('[_College_Roll_]', $collegeRoll, $smsBody);

        $smsBody = str_replace('[_Password_]', (string) ($params['password'] ?? ''), $smsBody);
        $smsBody = str_replace('[_OTP_]', (string) ($params['otp'] ?? ''), $smsBody);
        $smsBody = str_replace('[_Invoice_ID_]', (string) ($params['invoice_id'] ?? ''), $smsBody);

        $date = !empty($params['date']) ? date('D, d F Y', strtotime((string) $params['date'])) : date('D, d F Y');
        $smsBody = str_replace('[_Date_]', $date, $smsBody);

        return $smsBody;
    }
}
