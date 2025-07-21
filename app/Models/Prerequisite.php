<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Prerequisite extends Pivot
{
    protected $table = 'prerequisites';
    
    protected $fillable = [
        'subject_id',
        'prerequisite_id'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function requiredSubject()
    {
        return $this->belongsTo(Subject::class, 'prerequisite_id');
    }
}