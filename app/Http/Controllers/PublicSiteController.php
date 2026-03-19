<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PublicSiteController extends Controller
{
    private function resolveStoredUrl(?string $value, string $prefix = 'upload/'): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        $value = preg_replace('#^storage/#i', '', $value);
        $value = ltrim((string) $value, '/');

        $disk = 'public';
        if (config('filesystems.default')) {
            $disk = (string) config('filesystems.default');
        }

        if ($disk === 'public') {
            $localPath = $prefix . $value;
            try {
                if (Storage::disk('public')->exists($localPath)) {
                    return url('storage/' . ltrim($localPath, '/'));
                }
            } catch (\Throwable $e) {
            }
        }

        $bucketUrl = trim((string) env('DO_ASSET_URL', ''));
        if ($bucketUrl !== '') {
            $bucketUrl = rtrim($bucketUrl, '/');
            return $bucketUrl . '/' . ltrim($value, '/');
        }

        return url('storage/' . ltrim($prefix . $value, '/'));
    }

    public function homeData(Request $request)
    {
        $site = [];
        try {
            $cached = App::make('siteSettingObj');
            $site = is_array($cached) ? $cached : [];
        } catch (\Throwable $e) {
            $site = [];
        }

        $sliders = [];
        if (Schema::hasTable('sliders')) {
            $cols = Schema::getColumnListing('sliders');
            $select = ['id'];
            foreach (['title', 'file', 'file_type', 'status', 'created_at'] as $c) {
                if (in_array($c, $cols, true)) {
                    $select[] = $c;
                }
            }

            $q = DB::table('sliders')->select($select)->orderByDesc('id');
            if (in_array('status', $cols, true)) {
                $q->where('status', 'active');
            }

            $rows = $q->limit(10)->get();
            $sliders = $rows->map(function ($r) {
                $file = property_exists($r, 'file') ? $r->file : null;
                return [
                    'id' => (int) ($r->id ?? 0),
                    'title' => property_exists($r, 'title') ? $r->title : null,
                    'file' => $file,
                    'url' => $this->resolveStoredUrl($file),
                ];
            })->values()->all();
        }

        $notices = [];
        if (Schema::hasTable('notices')) {
            $cols = Schema::getColumnListing('notices');
            $select = ['id'];
            foreach (['title', 'date', 'type', 'status', 'created_at'] as $c) {
                if (in_array($c, $cols, true)) {
                    $select[] = $c;
                }
            }

            $q = DB::table('notices')->select($select)->orderByDesc('id');
            if (in_array('type', $cols, true)) {
                $q->where('type', 'public');
            }
            if (in_array('status', $cols, true)) {
                $q->where('status', 'active');
            }

            $rows = $q->limit(15)->get();
            $notices = $rows->map(function ($r) {
                return [
                    'id' => (int) ($r->id ?? 0),
                    'title' => property_exists($r, 'title') ? $r->title : null,
                    'date' => property_exists($r, 'date') ? $r->date : null,
                ];
            })->values()->all();
        }

        return response()->json([
            'site' => $site,
            'sliders' => $sliders,
            'notices' => $notices,
            'links' => [
                'student_registration' => env('SRLMS_SSO_REGISTRATION_URL'),
                'student_login' => env('SRLMS_SSO_LOGIN_URL'),
                'live_chat' => env('CHAT_URL'),
            ],
        ]);
    }

    public function content(Request $request, string $slug)
    {
        if (!Schema::hasTable('contents')) {
            return response()->json([
                'slug' => $slug,
                'title' => '',
                'description' => '',
                'image' => null,
            ]);
        }

        $row = DB::table('contents')->where('slug', $slug)->first();
        if (!$row) {
            return response()->json([
                'slug' => $slug,
                'title' => '',
                'description' => '',
                'image' => null,
            ]);
        }

        $image = property_exists($row, 'image') ? $row->image : null;
        return response()->json([
            'slug' => $slug,
            'title' => $row->title ?? '',
            'description' => $row->description ?? '',
            'image' => $image,
            'image_url' => $this->resolveStoredUrl($image, 'upload/'),
        ]);
    }

    public function notices(Request $request)
    {
        if (!Schema::hasTable('notices')) {
            return response()->json(['data' => []]);
        }

        $cols = Schema::getColumnListing('notices');
        $select = ['id'];
        foreach (['title', 'date', 'type', 'status', 'created_at'] as $c) {
            if (in_array($c, $cols, true)) {
                $select[] = $c;
            }
        }

        $q = DB::table('notices')->select($select)->orderByDesc('id');
        if (in_array('type', $cols, true)) {
            $q->where('type', 'public');
        }
        if (in_array('status', $cols, true)) {
            $q->where('status', 'active');
        }

        $rows = $q->limit(100)->get();
        $data = $rows->map(function ($r) {
            return [
                'id' => (int) ($r->id ?? 0),
                'title' => property_exists($r, 'title') ? $r->title : null,
                'date' => property_exists($r, 'date') ? $r->date : null,
            ];
        })->values()->all();

        return response()->json(['data' => $data]);
    }

    public function noticeShow(Request $request, int $id)
    {
        if (!Schema::hasTable('notices')) {
            return response()->json([], 404);
        }

        $row = DB::table('notices')->where('id', $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        $out = (array) $row;
        foreach (['image', 'file', 'pdf'] as $f) {
            if (array_key_exists($f, $out)) {
                $out[$f . '_url'] = $this->resolveStoredUrl(is_string($out[$f]) ? $out[$f] : null, 'upload/');
            }
        }

        return response()->json($out);
    }
}
