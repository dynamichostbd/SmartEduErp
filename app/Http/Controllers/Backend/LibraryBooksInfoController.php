<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LibraryBooksInfoController extends Controller
{
    private function table(): ?string
    {
        if (Schema::hasTable('library_books')) return 'library_books';
        if (Schema::hasTable('book_infos')) return 'book_infos';
        if (Schema::hasTable('books')) return 'books';
        return null;
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

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $table = $this->table();
        if (!$table) {
            return $this->emptyPaginator($perPage);
        }

        $q = DB::table($table)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'id');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && Schema::hasColumn($table, $field)) {
            $q->where($field, 'like', "%{$value}%");
        }

        return response()->json($q->paginate($perPage));
    }
}
