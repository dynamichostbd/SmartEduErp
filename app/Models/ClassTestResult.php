<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTestResult extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'exam_date' => 'date:Y-m-d',
        'published_date' => 'date:Y-m-d',
    ];

    public function details()
    {
        return $this->hasMany(ClassTestResultDetails::class);
    }
}
