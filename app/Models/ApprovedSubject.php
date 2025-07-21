<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ApprovedSubject extends Pivot
{
    protected $table = 'approved_subjects';
    
    protected $fillable = [
        'registration_date',
        'student_id',
        'subject_id',
        'period_id',
        'grade'
    ];

    protected $casts = [
        'registration_date' => 'date',
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