<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    // Relación con materias aprobadas
    public function approvedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'approved_subjects', 'student_id', 'subject_id')
                   ->using(ApprovedSubject::class)
                   ->withPivot(['registration_date', 'period_id', 'grade']);
    }

    // Relación con materias planificadas
    public function plannedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'planned_subjects', 'student_id', 'subject_id')
                   ->using(PlannedSubject::class)
                   ->withPivot(['registration_date', 'period_id']);
    }
}