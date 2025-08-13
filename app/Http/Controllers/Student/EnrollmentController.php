<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\RegistrationPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    use \App\Http\Controllers\Concerns\HasCurrentPeriod;

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

    /**
     * Función para procesar la prematrícula
     */
    public function processPreEnrollment(Request $request)
    {
        $user = Auth::user();
        $currentPeriod = $this->getCurrentPeriod();
        
        if (!$currentPeriod) {
            return back()->with('error', 'No hay período de registro activo');
        }
        
        $validated = $request->validate([
            'subjects' => 'sometimes|array',
            'subjects.*' => 'exists:subjects,subject_id',
            'period_id' => 'required|exists:registration_periods,period_id'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Eliminar todas las asignaturas planificadas para ese estudiante
            DB::table('planned_subjects')
                ->where('student_id', $user->id)
                ->delete();
            
            // Agregar las nuevas selecciones si existen
            if (isset($validated['subjects'])) {
                $insertData = array_map(function($subjectId) use ($user, $currentPeriod) {
                    return [
                        'student_id' => $user->id,
                        'subject_id' => $subjectId,
                        'period_id' => $currentPeriod->period_id,
                        'registration_date' => now()
                    ];
                }, $validated['subjects']);
                
                DB::table('planned_subjects')->insert($insertData);
            }
            
            DB::commit();
            
            return redirect()->route('student.pre-enrollment.plan')
                            ->with('success', 'Planificación académica actualizada exitosamente');
                            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la prematrícula: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar asignatura planificada
     */
    public function removePlannedSubject($subjectId)
    {
        $user = Auth::user();
        $currentPeriod = $this->getCurrentPeriod();
        
        if (!$currentPeriod) {
            return back()->with('error', 'No hay período de registro activo');
        }
        
        $deleted = DB::table('planned_subjects')
                    ->where('student_id', $user->id) // Cambiado a $user->id
                    ->where('subject_id', $subjectId)
                    ->where('period_id', $currentPeriod->period_id)
                    ->delete();
        
        if ($deleted) {
            $this->updateSubjectDemand([$subjectId], $currentPeriod->period_id);
            return back()->with('success', 'Asignatura eliminada de tu planificación');
        }
        
        return back()->with('error', 'No se pudo eliminar la asignatura');
    }

    /**
     * Actualizar la demanda de asignaturas
     */
    protected function updateSubjectDemand(array $subjectIds, int $periodId)
    {
        foreach ($subjectIds as $subjectId) {
            $count = DB::table('planned_subjects')
                     ->where('subject_id', $subjectId)
                     ->where('period_id', $periodId)
                     ->count();
            
            DB::table('subjects_demand')
              ->updateOrInsert(
                  ['subject_id' => $subjectId, 'period_id' => $periodId],
                  ['student_count' => $count]
              );
        }
    }
}