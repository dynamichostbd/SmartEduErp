<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTestResultMarks extends Model
{
    protected $fillable = [
        'class_test_result_details_id',
        'subject_id',
        'mark',
        'exam_mark',
        'pass_mark',
        'result_status',
        'created_by',
        'created_ip',
        'updated_by',
        'updated_ip',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class)->select('id', 'name_en');
    }

    public function class_test_result_details()
    {
        return $this->belongsTo(ClassTestResultDetails::class);
    }
}
