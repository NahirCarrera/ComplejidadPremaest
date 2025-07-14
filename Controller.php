<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subject;
use App\Models\RegistrationPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ApprovedSubject;
class StudentController extends Controller
{
    
    /**
     * Mostrar asignaturas disponibles para prematrícula
     */
    public function showAvailableSubjects()
    {
        $user = Auth::user();
        $currentPeriod = $this->getCurrentPeriod();
        
        if (!$currentPeriod) {
            return redirect()->route('student.dashboard')
                            ->with('error', 'No hay período de registro activo actualmente');
        }
        
        // Obtener asignaturas disponibles usando la función PostgreSQL
        $availableSubjects = DB::select(
            "SELECT * FROM get_available_subjects_for_user(?)", 
            [$user->id]
        );
        
        // Convertir a colección para mejor manejo
        $availableSubjects = collect($availableSubjects)->map(function($item) {
            return (object)[
                'subject_id' => $item->subject_id,
                'code' => $item->subject_code,  // Usar subject_code en lugar de code
                'name' => $item->subject_name,  // Usar subject_name en lugar de name
                'credits' => $item->subject_credits,  // Usar subject_credits
                'level' => $item->subject_level,  // Usar subject_level
                'has_prerequisites' => $item->has_prerequisites
            ];

        });
        
        // Obtener asignaturas ya planificadas
        $plannedSubjects = DB::table('planned_subjects')
            ->where('student_id', $user->id)
            ->where('period_id', $currentPeriod->period_id)
            ->pluck('subject_id')
            ->toArray();
        
        // Calcular créditos planificados
        $plannedCredits = $availableSubjects
            ->whereIn('subject_id', $plannedSubjects)
            ->sum('credits');
        
        return view('student.enrollment.plan', [
            'availableSubjects' => $availableSubjects,
            'plannedSubjects' => $plannedSubjects,
            'currentPeriod' => $currentPeriod,
            'plannedCredits' => $plannedCredits
        ]);
    }

}