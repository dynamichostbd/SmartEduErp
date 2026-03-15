<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CertificateTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('certificate_templates as t')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 't.academic_qualification_id')
            ->leftJoin('account_heads as h', 'h.id', '=', 't.account_head_id')
            ->leftJoin('payment_gateways as g', 'g.id', '=', 't.payment_gateway_id')
            ->select([
                't.*',
                'q.name as academic_qualification_name',
                'h.name as account_head_name',
                'g.account_no as gateway_account_no',
            ])
            ->orderByDesc('t.id');

        if ($request->filled('status')) {
            $query->where('t.status', $request->input('status'));
        }

        $field = (string) ($request->input('field_name') ?? '');
        $value = (string) ($request->input('value') ?? '');
        if ($field !== '' && $value !== '' && in_array($field, ['title'], true)) {
            $query->where('t.' . $field, 'like', '%' . $value . '%');
        }

        if ($request->boolean('allData')) {
            $items = $query->get();
            return response()->json($items);
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
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

    public function show($id)
    {
        $row = DB::table('certificate_templates')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $qualification = DB::table('academic_qualifications')->select('id', 'name')->where('id', $row->academic_qualification_id)->first();
        $head = Schema::hasTable('account_heads')
            ? DB::table('account_heads')->select('id', 'name')->where('id', $row->account_head_id)->first()
            : null;

        $gateway = Schema::hasTable('payment_gateways')
            ? DB::table('payment_gateways')->select('id', 'account_no')->where('id', $row->payment_gateway_id)->first()
            : null;

        return response()->json([
            'id' => $row->id,
            'academic_qualification_id' => $row->academic_qualification_id,
            'account_head_id' => $row->account_head_id,
            'payment_gateway_id' => $row->payment_gateway_id,
            'title' => $row->title,
            'bg_en_image' => $this->assetUrl($row->bg_en_image),
            'bg_bn_image' => $this->assetUrl($row->bg_bn_image),
            'en_template_json' => $this->decodeJson($row->en_template_json),
            'bn_template_json' => $this->decodeJson($row->bn_template_json),
            'print_layout' => $row->print_layout,
            'certificate_fees' => $row->certificate_fees,
            'amount' => $row->amount,
            'status' => $row->status,
            'qualification' => $qualification,
            'account_head' => $head,
            'gateway' => $gateway,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_qualification_id' => ['required'],
            'account_head_id' => ['required'],
            'payment_gateway_id' => ['required'],
            'title' => ['required'],
            'certificate_fees' => ['required'],
            'print_layout' => ['required'],
            'amount' => ['required'],
            'bg_en_image' => ['required', 'file'],
            'bg_bn_image' => ['required', 'file'],
            'status' => ['required'],
        ]);

        $collegeName = env('COLLEGE_NAME', 'default_college');
        $path = 'upload/' . $collegeName . '/certificate_template';

        $data = $request->only([
            'academic_qualification_id',
            'account_head_id',
            'payment_gateway_id',
            'title',
            'bn_template_json',
            'en_template_json',
            'print_layout',
            'certificate_fees',
            'amount',
            'status',
        ]);

        $bgEn = $request->file('bg_en_image');
        $bgBn = $request->file('bg_bn_image');

        $data['bg_en_image'] = $bgEn ? $bgEn->store($path, 'public') : null;
        $data['bg_bn_image'] = $bgBn ? $bgBn->store($path, 'public') : null;

        if (is_array($data['en_template_json'] ?? null)) {
            $data['en_template_json'] = json_encode($data['en_template_json']);
        }
        if (is_array($data['bn_template_json'] ?? null)) {
            $data['bn_template_json'] = json_encode($data['bn_template_json']);
        }

        $id = DB::table('certificate_templates')->insertGetId($data + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function update(Request $request, $id)
    {
        $row = DB::table('certificate_templates')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $payload = $request->input('data');
        $data = is_string($payload) ? json_decode($payload, true) : (is_array($payload) ? $payload : []);
        if (!is_array($data)) {
            $data = [];
        }

        $collegeName = env('COLLEGE_NAME', 'default_college');
        $path = 'upload/' . $collegeName . '/certificate_template';

        $bgEn = $request->file('bg_en_image');
        $bgBn = $request->file('bg_bn_image');

        if ($bgEn) {
            $data['bg_en_image'] = $bgEn->store($path, 'public');
        }
        if ($bgBn) {
            $data['bg_bn_image'] = $bgBn->store($path, 'public');
        }

        if (isset($data['en_template_json']) && is_array($data['en_template_json'])) {
            $data['en_template_json'] = json_encode($data['en_template_json']);
        }
        if (isset($data['bn_template_json']) && is_array($data['bn_template_json'])) {
            $data['bn_template_json'] = json_encode($data['bn_template_json']);
        }

        $allowed = [
            'academic_qualification_id',
            'account_head_id',
            'payment_gateway_id',
            'title',
            'bg_en_image',
            'bg_bn_image',
            'bn_template_json',
            'en_template_json',
            'print_layout',
            'certificate_fees',
            'amount',
            'status',
        ];

        $update = array_intersect_key($data, array_flip($allowed));

        DB::table('certificate_templates')
            ->where('id', $id)
            ->update($update + ['updated_at' => now()]);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy($id)
    {
        DB::table('certificate_templates')->where('id', $id)->update(['status' => 'deactive', 'updated_at' => now()]);
        return response()->json(['message' => 'Delete Successfully!'], 200);
    }

    protected function decodeJson($val): array
    {
        if (empty($val)) {
            return [[
                'text_align' => '',
                'font_weight' => '',
                'label' => '',
                'color' => '',
                'fornt_size' => 16,
                'section_width' => '250px',
                'section_height' => '25px',
            ]];
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
}
