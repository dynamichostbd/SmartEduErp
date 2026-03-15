<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Student extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'online_admission_id',
        'academic_class_id',
        'academic_qualification_id',
        'academic_session_id',
        'department_id',
        'student_type',
        'student_id',
        'ssc_gpa',
        'reg_no',
        'admission_id',
        'hostel_id',
        'college_roll',
        'readmission_college_roll',
        'name',
        'gender',
        'dob',
        'religion',
        'nid',
        'blood_group',
        'fathers_name',
        'mothers_name',
        'email',
        'mobile',
        'address',
        'permanent_address',
        'profile',
        'guardian_type',
        'guardian_name',
        'guardian_mobile',
        'guardian_relations',
        'passing_year',
        'nationality',
        'extra_curricular_activity',
        'quota',
        'marital_status',
        'living_type',
        'hostel_room_no',
        'hostel_discount_percent',
        'hostel_admission_date',
        'hostel_release_date',
        'cluster_subjects',
        'subject_id',
        'subject_cluster_id',
        'college_roll_auto_generate',
        'password',
        'otp',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'cluster_subjects' => 'array',
    ];

    public function setPasswordAttribute($val)
    {
        if (!empty($val)) {
            $this->attributes['password'] = Hash::make($val);
        }
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
