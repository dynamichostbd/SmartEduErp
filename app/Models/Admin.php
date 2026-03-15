<?php

namespace App\Models;

use App\Models\System\Role;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'role_id',
        'type',
        'name',
        'email',
        'username',
        'password',
        'department_id',
        'profile',
        'mobile',
        'address',
        'emergency_contacts',
        'status',
        'otp',
        'otp_expired_at',
        'is_two_factor_auth',
        'block',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function setPasswordAttribute($val)
    {
        $this->attributes['password'] = Hash::make($val);
    }

    public function getProfileAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $filePath1 = public_path($value);
        if (file_exists($filePath1)) {
            return url($value);
        }

        $filePath2 = public_path('storage/upload/' . $value);
        if (file_exists($filePath2)) {
            return url('storage/upload/' . $value);
        }

        $bucketUrl = env('DO_ASSET_URL', 'https://smartedubd.blr1.cdn.digitaloceanspaces.com/blr1_storage');
        $bucketUrl = rtrim($bucketUrl, '/');
        $value = ltrim($value, '/');

        return "{$bucketUrl}/{$value}";
    }
}
