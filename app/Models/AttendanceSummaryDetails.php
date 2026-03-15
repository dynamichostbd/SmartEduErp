<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSummaryDetails extends Model
{
    protected $guarded = ['id'];

    public function student()
    {
        return $this->hasOne(Student::class, 'student_id', 'student_id')
            ->select(
                'id',
                'academic_session_id',
                'department_id',
                'academic_qualification_id',
                'academic_class_id',
                'student_type',
                'student_id',
                'college_roll',
                'admission_id',
                'name',
                'mobile',
                'reg_no',
                'status',
                'profile'
            );
    }
}
