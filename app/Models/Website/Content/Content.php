<?php

namespace App\Models\Website\Content;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'contents';

    protected $guarded = ['id'];

    public function contentFiles()
    {
        return $this->hasMany(ContentFile::class)->oldest('sorting');
    }

    public function getMetaAttribute($value)
    {
        if (empty($value)) {
            return ['title' => '', 'description' => ''];
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return ['title' => '', 'description' => ''];
    }

    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        $v = (string) $value;
        if (preg_match('/^https?:\/\//i', $v)) {
            return $v;
        }

        $filePath1 = public_path($v);
        if (file_exists($filePath1)) {
            return url($v);
        }

        $filePath2 = public_path('storage/upload/' . ltrim($v, '/'));
        if (file_exists($filePath2)) {
            return url('storage/upload/' . ltrim($v, '/'));
        }

        return url('storage/upload/' . ltrim($v, '/'));
    }
}
