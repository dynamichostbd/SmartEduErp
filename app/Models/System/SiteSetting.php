<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $guarded = ['id'];

    private function resolveFileUrl($value): ?string
    {
        if (empty($value)) {
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

        // legacy: some tables store without the leading "upload/"
        if (str_starts_with($value, 'conf/')) {
            $value = 'upload/' . $value;
        }
        if (str_starts_with($value, 'upload/upload/')) {
            $value = substr($value, strlen('upload/'));
        }

        try {
            // Storage::url() uses the default filesystem disk (FILESYSTEM_DISK)
            $url = Storage::url($value);
            if (!empty($url)) {
                return $url;
            }
        } catch (\Throwable $e) {
            // ignore and fallback
        }

        $bucketUrl = trim((string) env('DO_ASSET_URL', ''));
        if ($bucketUrl !== '') {
            $bucketUrl = rtrim($bucketUrl, '/');
            return $bucketUrl . '/' . ltrim($value, '/');
        }

        return url('storage/' . ltrim($value, '/'));
    }

    public function getLogoAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getLogoSmallAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getFaviconAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getIdcardFrontPartAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getIdcardBackPartAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getAdmitCardImageAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getMarksheetImageAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getOnlineAdmissionFormImageAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getTeacherIdCardFrontAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getTeacherIdCardBackAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getPrincipleSignatureAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getAdminAdmitCardAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getSeatCardAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getAdminAdmitCardFrontAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }

    public function getAdminAdmitCardBackAttribute($value)
    {
        return $this->resolveFileUrl($value);
    }
}
