<?php

namespace App\Http\Controllers\Backend\Website;

use App\Http\Controllers\Controller;
use App\Models\Website\Content\Content;
use App\Models\Website\Content\ContentFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContentController extends Controller
{
    public function create(Request $request, $slug = null)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        return response()->json(['message' => 'OK']);
    }

    public function file(Request $request, $slug = null)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        return response()->json(['message' => 'OK']);
    }

    public function show(Request $request, $slug)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('contents')) {
            return response()->json([
                'status' => 'active',
                'meta' => ['title' => '', 'description' => ''],
            ]);
        }

        $row = Content::with('contentFiles')->where('slug', (string) $slug)->first();

        if (!$row) {
            return response()->json([
                'slug' => (string) $slug,
                'title' => '',
                'description' => '',
                'image' => null,
                'status' => 'active',
                'meta' => ['title' => '', 'description' => ''],
                'content_files' => [],
            ]);
        }

        return response()->json($row);
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('contents')) {
            return response()->json(['message' => 'Content module not ready'], 422);
        }

        $slug = trim((string) $request->input('slug'));
        if ($slug === '') {
            return response()->json(['message' => 'Slug is Missing!'], 422);
        }

        $columns = Schema::getColumnListing('contents');

        $content = DB::table('contents')->where('slug', $slug)->first();

        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }

            if ($col === 'image') {
                continue;
            }

            if ($col === 'meta') {
                $meta = $request->input('meta');
                $payload['meta'] = !empty($meta) ? json_encode($meta) : null;
                continue;
            }

            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (in_array('slug', $columns, true)) {
            $payload['slug'] = $slug;
        }

        if ($request->hasFile('image') && in_array('image', $columns, true)) {
            $file = $request->file('image');
            if ($file && $file->isValid()) {
                $path = $file->store('upload/content', 'public');
                $payload['image'] = preg_replace('/^upload\//', '', $path);
            }
        } else {
            if (!empty($content) && property_exists($content, 'image')) {
                $payload['image'] = $content->image;
            }
        }

        if (!empty($content)) {
            if (in_array('updated_at', $columns, true)) {
                $payload['updated_at'] = now();
            }
            DB::table('contents')->where('id', (int) $content->id)->update($payload);
            return response()->json(['message' => 'Update Successfully!'], 200);
        }

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table('contents')->insert($payload);

        return response()->json(['message' => 'Create Successfully!'], 200);
    }

    public function storeFile(Request $request, $contentId)
    {
        if (!Schema::hasTable('content_files')) {
            return response()->json(['message' => 'Content file module not ready'], 422);
        }

        $contentId = (int) $contentId;
        if ($contentId <= 0) {
            return response()->json(['message' => 'Invalid content'], 422);
        }

        $columns = Schema::getColumnListing('content_files');

        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }

            if ($col === 'file') {
                continue;
            }

            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (in_array('content_id', $columns, true)) {
            $payload['content_id'] = $contentId;
        }

        if ($request->hasFile('file') && in_array('file', $columns, true)) {
            $file = $request->file('file');
            if ($file && $file->isValid()) {
                $path = $file->store('upload/content-file', 'public');
                $payload['file'] = preg_replace('/^upload\//', '', $path);
            }
        }

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table('content_files')->insert($payload);

        return response()->json(['message' => 'Create Successfully!'], 200);
    }

    public function destroy(Request $request, $contentFileId)
    {
        if (!Schema::hasTable('content_files')) {
            return response()->json(['message' => 'Content file module not ready'], 422);
        }

        $id = (int) $contentFileId;
        if ($id <= 0) {
            return response()->json(['message' => 'Invalid file'], 422);
        }

        DB::table('content_files')->where('id', $id)->delete();

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }
}
