<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'child_subject_enabled_subject_ids' => 'array',
        'published_date' => 'date:Y-m-d',
    ];

    public function details()
    {
        return $this->hasMany(ResultDetails::class);
    }
}
