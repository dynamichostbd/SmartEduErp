<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSummary extends Model
{
    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(AttendanceSummaryDetails::class, 'attendance_summarie_id');
    }
}
