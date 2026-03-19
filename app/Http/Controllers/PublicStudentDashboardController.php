<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class PublicStudentDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!Schema::hasTable('invoices')) {
            return response()->json(['invoices' => []], 200);
        }

        $q = DB::table('invoices as inv')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'inv.account_head_id')
            ->where('inv.student_id', (int) ($student->id ?? 0))
            ->orderByDesc('inv.id');

        if (Schema::hasColumn('invoices', 'status')) {
            $q->where('inv.status', 'success');
        }

        $rows = $q
            ->limit(20)
            ->select([
                'inv.id',
                'inv.invoice_date',
                'inv.invoice_number',
                'inv.amount',
                Schema::hasColumn('invoices', 'payment_date') ? 'inv.payment_date' : DB::raw('NULL as payment_date'),
                Schema::hasColumn('invoices', 'status') ? 'inv.status' : DB::raw("'success' as status"),
                'inv.account_head_id',
                'ah.name as head_name',
            ])
            ->get();

        $out = $rows->map(function ($r) {
            return [
                'id' => (int) ($r->id ?? 0),
                'invoice_date' => $r->invoice_date ?? null,
                'invoice_number' => $r->invoice_number ?? '',
                'amount' => (float) ($r->amount ?? 0),
                'payment_date' => $r->payment_date ?? null,
                'status' => $r->status ?? 'success',
                'head' => [
                    'name' => $r->head_name ?? '',
                ],
            ];
        })->values()->all();

        return response()->json(['invoices' => $out], 200);
    }

    public function viewInvoice(Request $request, int $id)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!Schema::hasTable('invoices')) {
            abort(404);
        }

        $invoice = DB::table('invoices')->where('id', $id)->first();
        if (!$invoice) {
            abort(404);
        }

        if ((int) ($invoice->student_id ?? 0) !== (int) ($student->id ?? 0)) {
            abort(403);
        }

        $head = null;
        if (!empty($invoice->account_head_id) && Schema::hasTable('account_heads')) {
            $head = DB::table('account_heads')->where('id', (int) $invoice->account_head_id)->first();
        }

        $config = [];
        try {
            $cfg = app()->make('siteSettingObj');
            $config = is_array($cfg) ? $cfg : [];
        } catch (\Throwable $e) {
            $config = [];
        }

        $logo = $config['logo'] ?? null;
        if (is_string($logo) && $logo !== '' && !preg_match('#^https?://#i', $logo) && !str_starts_with($logo, '/')) {
            $config['logo'] = url($logo);
        }

        $dto = (object) array_merge((array) $invoice, [
            'head' => $head,
            'student' => $student,
        ]);

        return response()->view('pdf.student_invoice', [
            'invoice' => $dto,
            'config' => $config,
        ]);
    }

    public function downloadInvoice(Request $request, int $id)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!Schema::hasTable('invoices')) {
            abort(404);
        }

        $invoice = DB::table('invoices')->where('id', $id)->first();
        if (!$invoice) {
            abort(404);
        }

        if ((int) ($invoice->student_id ?? 0) !== (int) ($student->id ?? 0)) {
            abort(403);
        }

        $head = null;
        if (!empty($invoice->account_head_id) && Schema::hasTable('account_heads')) {
            $head = DB::table('account_heads')->where('id', (int) $invoice->account_head_id)->first();
        }

        $config = [];
        try {
            $cfg = app()->make('siteSettingObj');
            $config = is_array($cfg) ? $cfg : [];
        } catch (\Throwable $e) {
            $config = [];
        }

        $logo = $config['logo'] ?? null;
        if (is_string($logo) && $logo !== '' && !preg_match('#^https?://#i', $logo) && !str_starts_with($logo, '/')) {
            $config['logo'] = url($logo);
        }

        $dto = (object) array_merge((array) $invoice, [
            'head' => $head,
            'student' => $student,
        ]);

        $pdf = Pdf::loadView('pdf.student_invoice', [
            'invoice' => $dto,
            'config' => $config,
        ])->setPaper('a4', 'portrait');

        $fileName = 'invoice(' . ($invoice->invoice_date ?? date('Y-m-d')) . '__' . ($invoice->invoice_number ?? $id) . ').pdf';

        return $pdf->download($fileName);
    }
}
