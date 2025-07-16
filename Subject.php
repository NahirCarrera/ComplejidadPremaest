<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $primaryKey = 'subject_id';
    protected $fillable = ['name', 'code', 'credits', 'level'];

    public function prerequisites()
    {
        return $this->belongsToMany(Subject::class, 'prerequisites', 'subject_id', 'prerequisite_id');
    }

    public function approvedBy()
    {
        return $this->belongsToMany(User::class, 'approved_subjects', 'subject_id', 'student_id');
    }

    public function plannedBy()
    {
        return $this->belongsToMany(User::class, 'planned_subjects', 'subject_id', 'student_id');
    }

    public function demand()
    {
        return $this->hasMany(SubjectDemand::class, 'subject_id', 'subject_id');
    }

}