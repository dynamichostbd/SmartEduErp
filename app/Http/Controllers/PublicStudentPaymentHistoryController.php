<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicStudentPaymentHistoryController extends Controller
{
    public function index(Request $request)
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

        if ($request->filled('academic_class_id') && Schema::hasColumn('invoices', 'academic_class_id')) {
            $q->where('inv.academic_class_id', (int) $request->input('academic_class_id'));
        }

        if ($request->filled('academic_qualification_id') && Schema::hasColumn('invoices', 'academic_qualification_id')) {
            $q->where('inv.academic_qualification_id', (int) $request->input('academic_qualification_id'));
        }

        if ($request->filled('account_head_id') && Schema::hasColumn('invoices', 'account_head_id')) {
            $q->where('inv.account_head_id', (int) $request->input('account_head_id'));
        }

        $perPage = (int) ($request->input('per_page') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $p = $q->select([
            'inv.id',
            'inv.invoice_date',
            'inv.invoice_number',
            'inv.amount',
            Schema::hasColumn('invoices', 'payment_date') ? 'inv.payment_date' : DB::raw('NULL as payment_date'),
            Schema::hasColumn('invoices', 'status') ? 'inv.status' : DB::raw("'pending' as status"),
            'inv.account_head_id',
            'ah.name as head_name',
        ])->paginate($perPage);

        $data = collect($p->items())
            ->map(function ($r) {
                return [
                    'id' => (int) ($r->id ?? 0),
                    'invoice_date' => $r->invoice_date ?? null,
                    'invoice_number' => $r->invoice_number ?? '',
                    'amount' => (float) ($r->amount ?? 0),
                    'payment_date' => $r->payment_date ?? null,
                    'status' => $r->status ?? 'pending',
                    'head' => [
                        'id' => (int) ($r->account_head_id ?? 0),
                        'name' => $r->head_name ?? '',
                    ],
                ];
            })
            ->values();

        return response()->json([
            'data' => $data,
            'links' => [
                'first' => $p->url(1),
                'last' => $p->url($p->lastPage()),
                'prev' => $p->previousPageUrl(),
                'next' => $p->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $p->currentPage(),
                'from' => $p->firstItem(),
                'last_page' => $p->lastPage(),
                'path' => $request->url(),
                'per_page' => $p->perPage(),
                'to' => $p->lastItem(),
                'total' => $p->total(),
            ],
        ], 200);
    }
}
