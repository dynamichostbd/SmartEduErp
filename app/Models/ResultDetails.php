<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultDetails extends Model
{
    protected $fillable = [
        'result_id',
        'student_id',
        'total_mark_without_additional',
        'total_mark',
        'gpa_without_additional',
        'gpa',
        'letter_grade',
        'result_status',
        'merit_position_in_department',
        'merit_position_in_class',
    ];

    public $timestamps = false;

    public function marks()
    {
        return $this->hasMany(ResultMarks::class, 'result_details_id')
            ->select('id', 'result_details_id', 'subject_id', 'ct_mark', 'cq_mark', 'mcq_mark', 'practical_mark', 'obtained_mark', 'total_mark', 'gpa', 'letter_grade', 'additional_subject', 'is_absent', 'sorting');
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->select('id', 'student_id', 'name', 'mobile', 'college_roll', 'fathers_name', 'mothers_name', 'reg_no', 'student_type');
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }
}
