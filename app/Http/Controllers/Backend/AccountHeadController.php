<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountHeadController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('account_heads')) {
            return response()->json([]);
        }

        $query = DB::table('account_heads')
            ->select('id', 'name', 'type', 'status')
            ->orderBy('id');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->boolean('allData')) {
            return response()->json($query->get());
        }

        return response()->json($query->paginate((int) ($request->input('pagination') ?? 10)));
    }
}
