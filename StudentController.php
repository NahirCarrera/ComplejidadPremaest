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
    // /**
    //  * Dashboard principal del estudiante
    //  */
    // public function dashboard()
    // {
    //     $user = Auth::user();
    //     $currentPeriod = RegistrationPeriod::latest('start_date')->first();

    //     // Asignaturas aprobadas
    //     $approvedCount = ApprovedSubject::where('student_id', $user->id)->count();
    //     $approvedCredits = ApprovedSubject::where('student_id', $user->id)
    //         ->join('subjects', 'approved_subjects.subject_id', '=', 'subjects.subject_id')
    //         ->sum('subjects.credits');

    //     // Asignaturas planificadas (para el período actual si existe)
    //     $plannedCount = $currentPeriod 
    //         ? DB::table('planned_subjects')
    //             ->where('student_id', $user->id)
    //             ->where('period_id', $currentPeriod->period_id)
    //             ->count()
    //         : 0;
    //         //sumar el valor del atributo credits de todas las asignaturas existentes en la tabla subjects
    //         //para calcular el total de creditos

    //     $totalCredits = $currentPeriod 
    //         ? DB::table('subjects')
    //             ->sum('subjects.credits')
    //         : 0;
    //     $pendingCredits = max(0, $totalCredits - $approvedCredits);
    //     $progressPercentage = $totalCredits > 0 ? round(($approvedCredits / $totalCredits) * 100) : 0;

    //     return view('student.dashboard', [
    //         'approvedCount' => $approvedCount,
    //         'plannedCount' => $plannedCount,
    //         'approvedCredits' => $approvedCredits,
    //         'currentPeriod' => $currentPeriod,
    //         'totalCredits' => $totalCredits,
    //         'pendingCredits' => $pendingCredits,
    //         'progressPercentage' => $progressPercentage
    //     ]);
    // }


    // /**
    //  * Mostrar formulario para subir record académico
    //  */
    // public function showUploadRecordForm()
    // {
    //     $periods = RegistrationPeriod::orderBy('start_date', 'desc')->get();
    //     $currentPeriod = $this->getCurrentPeriod();
        
    //     return view('student.records.upload', compact('periods', 'currentPeriod'));
    // }

    // /**
    //  * Detecta el binario de Python disponible en el sistema.
    //  */
    // protected function getPythonBinary(): string
    // {
    //     // Windows: Intenta con 'python' o la ruta común
    //     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    //         $possiblePaths = [
    //             'python',               // Alias general
    //             'py',                   // Launcher de Windows
    //             'C:\\Python39\\python.exe', // Ruta común 1
    //             'C:\\Python310\\python.exe', // Ruta común 2
    //             env('PYTHON_PATH')     // Desde .env (opcional)
    //         ];
    //     } 
    //     // Linux/macOS
    //     else {
    //         $possiblePaths = ['python3', 'python'];
    //     }

    //     foreach ($possiblePaths as $path) {
    //         $testCommand = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' 
    //             ? "where {$path}"
    //             : "which {$path}";

    //         exec($testCommand, $output, $exitCode);
            
    //         if ($exitCode === 0) {
    //             return $path;
    //         }
    //     }

    //     throw new \RuntimeException(
    //         "No se encontró Python instalado. Rutas probadas: " . implode(', ', $possiblePaths)
    //     );
    // }
    // /**
    //  * Procesar el record académico subido
    //  */
    // public function processRecord(Request $request)
    // {
    //     $validated = $request->validate([
    //         'academic_record' => 'required|mimes:pdf|max:2048',
    //     ]);

    //     $user = Auth::user();
        
    //     // Eliminar todas las asignaturas aprobadas para ese estudiante
    //         DB::table('approved_subjects')
    //             ->where('student_id', $user->id)
    //             ->delete();
    //     // Eliminar todas las asignaturas planificadas para ese estudiante
    //         DB::table('planned_subjects')
    //             ->where('student_id', $user->id)
    //             ->delete();
    //     DB::beginTransaction();
    //     try {
    //         // 1. Guardar el archivo en una ubicación PERSISTENTE (no temporal)
    //         $path = $request->file('academic_record')->store('student_records/'.$user->id, 'public');
    //         $fullPath = Storage::disk('public')->path($path);
            
    //         // 2. Ejecutar Python con la ruta persistente
    //         $scriptPath = base_path('scripts/process_record.py');
    //         $pythonBinary = $this->getPythonBinary();

    //         $command = sprintf(
    //             '"%s" "%s" "%s" %d',
    //             $pythonBinary,
    //             $scriptPath,
    //             $fullPath,
    //             $user->id
    //         );

    //         exec($command, $output, $returnCode);

    //         logger()->info("Python command executed:", [
    //             'command' => $command,
    //             'output' => $output,
    //             'exit_code' => $returnCode
    //         ]);
            
    //         // 3. Eliminar el archivo SOLO después de confirmar éxito
    //         if ($returnCode === 0) {
    //             Storage::disk('public')->delete($path);
    //             return redirect()->route('student.records.approved')
    //                         ->with('success', 'Record académico procesado exitosamente');
    //         } else {
    //             Storage::disk('public')->delete($path); // Limpieza en caso de error
    //             throw new \Exception("Error al procesar el PDF: " . implode("\n", $output));
    //         }
    //         DB::commit();                
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         if (isset($path)) {
    //             Storage::disk('public')->delete($path); // Limpieza final
    //         }
    //         return back()->with('error', $e->getMessage())->withInput();
    //     }
    // }
    /**
     * Mostrar asignaturas aprobadas
     */
    public function viewApprovedSubjects(Request $request)
    {
        $user = Auth::user();
        
        $query = DB::table('approved_subjects')
            ->where('student_id', $user->id)
            ->join('subjects', 'approved_subjects.subject_id', '=', 'subjects.subject_id')
            ->join('registration_periods', 'approved_subjects.period_id', '=', 'registration_periods.period_id')
            ->select(
                'subjects.name',
                'subjects.code',
                'subjects.credits',
                'registration_periods.code as period_code',
                'approved_subjects.registration_date',
                'registration_periods.period_id'
            )
            ->orderBy('approved_subjects.registration_date', 'desc');

        // Aplicar filtro por período si existe
        if ($request->has('period_filter') && $request->period_filter) {
            $query->where('registration_periods.period_id', $request->period_filter);
        }

        $approvedSubjects = $query->paginate(10);

        // Calcular total de créditos (filtrados o no)
        $totalCredits = $query->sum('subjects.credits');

        // Obtener todos los períodos para el filtro
        $periods = RegistrationPeriod::orderBy('start_date', 'desc')->get();

        return view('student.records.approved', [
            'approvedSubjects' => $approvedSubjects,
            'totalCredits' => $totalCredits,
            'periods' => $periods
        ]);
    }
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

//     /**
//      * Dashboard de administración (solo para admin)
//      */
//     public function adminDashboard()
//     {
//         $currentPeriod = $this->getCurrentPeriod();
//         $studentsCount = User::role('student')->count();
//         $subjectsCount = Subject::count();
//         $activeEnrollments = $currentPeriod 
//             ? DB::table('planned_subjects')
//               ->where('period_id', $currentPeriod->period_id)
//               ->count() 
//             : 0;
        
//         return view('admin.dashboard', compact(
//             'currentPeriod', 
//             'studentsCount',
//             'subjectsCount',
//             'activeEnrollments'
//         ));
//     }

//     /**
//      * Gestionar períodos académicos (solo para admin)
//      */
//     public function managePeriods()
//     {
//         $periods = RegistrationPeriod::orderBy('start_date', 'desc')->paginate(10);
//         return view('admin.periods.index', compact('periods'));
//     }

//     // ----------------------------
//     // Métodos auxiliares protegidos
//     // ----------------------------
    
//     /**
//      * Obtener el período de registro actual
//      */
//     protected function getCurrentPeriod()
//     {
//         return RegistrationPeriod::latest('start_date')->first();
//     }
    
//     /**
//      * Actualizar la demanda de asignaturas
//      */
//     protected function updateSubjectDemand(array $subjectIds, int $periodId)
//     {
//         foreach ($subjectIds as $subjectId) {
//             $count = DB::table('planned_subjects')
//                      ->where('subject_id', $subjectId)
//                      ->where('period_id', $periodId)
//                      ->count();
            
//             DB::table('subjects_demand')
//               ->updateOrInsert(
//                   ['subject_id' => $subjectId, 'period_id' => $periodId],
//                   ['student_count' => $count]
//               );
//         }
//     }
}