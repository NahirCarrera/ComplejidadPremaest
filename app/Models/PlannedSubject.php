<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PlannedSubject extends Pivot
{
    protected $table = 'planned_subjects';
    
    protected $fillable = [
        'student_id',
        'subject_id',
        'period_id',
        'registration_date'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function period()
    {
        return $this->belongsTo(RegistrationPeriod::class, 'period_id');
    }
}