<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectDemand extends Model
{
    // Asegúrate de especificar el nombre correcto
    protected $table = 'subjects_demand'; 

    protected $primaryKey = 'demand_id';
    public $timestamps = false;

    protected $fillable = [
        'student_count',
        'subject_id',
        'period_id',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function period()
    {
        return $this->belongsTo(RegistrationPeriod::class, 'period_id', 'period_id');
    }
}
