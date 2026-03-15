<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultMarks extends Model
{
    protected $fillable = [
        'result_details_id',
        'subject_id',
        'ct_mark',
        'cq_mark',
        'mcq_mark',
        'practical_mark',
        'obtained_mark',
        'total_mark',
        'gpa',
        'letter_grade',
        'pass_marks',
        'additional_subject',
        'is_absent',
        'sorting',
        'created_by',
        'created_ip',
        'updated_by',
        'updated_ip',
    ];

    protected $casts = [
        'pass_marks' => 'array',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id')->select('id', 'name_en', 'is_child');
    }
}
