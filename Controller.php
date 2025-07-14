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


}