<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudySession extends Model
{
    protected $fillable = [
        'subject',
        'content',
        'available_time',
        'study_plan',
    ];
}