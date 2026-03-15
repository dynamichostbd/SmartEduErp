<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTestResultDetails extends Model
{
    protected $fillable = ['class_test_result_id', 'student_id', 'total_mark', 'result_status'];

    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Student::class)->select('id', 'name', 'mobile', 'college_roll', 'student_id');
    }

    public function marks()
    {
        return $this->hasMany(ClassTestResultMarks::class, 'class_test_result_details_id')
            ->select('id', 'class_test_result_details_id', 'subject_id', 'mark', 'pass_mark', 'exam_mark', 'result_status');
    }

    public function class_test_result()
    {
        return $this->belongsTo(ClassTestResult::class);
    }
}
