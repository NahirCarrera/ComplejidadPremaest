<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationPeriod extends Model
{
    protected $primaryKey = 'period_id';
    protected $fillable = ['code', 'start_date', 'end_date', 'admin_id'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function approvedSubjects()
    {
        return $this->hasMany(ApprovedSubject::class, 'period_id');
    }

    public function plannedSubjects()
    {
        return $this->hasMany(PlannedSubject::class, 'period_id');
    }
}