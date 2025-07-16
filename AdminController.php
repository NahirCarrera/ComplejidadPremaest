<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RegistrationPeriod;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
class AdminController extends Controller
{

    public function dashboard()
    {
        $currentPeriod = RegistrationPeriod::latest('start_date')->first();
        
        // 1. Totalidad de estudiantes
        $studentRoleId = Role::where('name', 'student')->value('id');
        $studentsCount = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.role_id', $studentRoleId)
            ->count();

        // 2. Estudiantes que planificaron (para el período actual)
        $activeEnrollments = $currentPeriod 
            ? DB::table('planned_subjects')
                ->where('period_id', $currentPeriod->period_id)
                ->distinct('student_id')
                ->count('student_id')
            : 0;

        // 3. Total de asignaturas
        $subjectsCount = Subject::count();

        // 4. Asignaturas con mayor y menor demanda por nivel
        $demandByLevel = Subject::select(
                'level',
                DB::raw('COUNT(subjects.subject_id) as total_subjects'),
                DB::raw('SUM(CASE WHEN ps.student_count IS NULL THEN 0 ELSE ps.student_count END) as total_enrollments'),
                DB::raw('MAX(ps.student_count) as max_demand'),
                DB::raw('MIN(COALESCE(ps.student_count, 0)) as min_demand')
            )
            ->leftJoin(DB::raw('(SELECT subject_id, COUNT(student_id) as student_count 
                                FROM planned_subjects 
                                WHERE period_id = '.($currentPeriod ? $currentPeriod->period_id : 'NULL').'
                                GROUP BY subject_id) as ps'), 
                'subjects.subject_id', '=', 'ps.subject_id')
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        // 5. Asignaturas sin demanda
        $noDemandSubjects = Subject::leftJoin('planned_subjects', function($join) use ($currentPeriod) {
                $join->on('subjects.subject_id', '=', 'planned_subjects.subject_id')
                    ->when($currentPeriod, function($query) use ($currentPeriod) {
                        $query->where('planned_subjects.period_id', $currentPeriod->period_id);
                    });
            })
            ->whereNull('planned_subjects.subject_id')
            ->count();

        // 6. Variación de demanda respecto a períodos anteriores
        $enrollmentTrend = RegistrationPeriod::select(
                'registration_periods.period_id',
                'registration_periods.code',
                DB::raw('COUNT(DISTINCT planned_subjects.student_id) as enrollment_count')
            )
            ->leftJoin('planned_subjects', 'registration_periods.period_id', '=', 'planned_subjects.period_id')
            ->groupBy('registration_periods.period_id', 'registration_periods.code')
            ->orderBy('registration_periods.start_date', 'desc')
            ->limit(5)
            ->get()
            ->reverse()
            ->values();

        // 7. Promedio de créditos tomados por los estudiantes
        $averageCredits = $currentPeriod 
            ? DB::table('planned_subjects')
                ->select(DB::raw('AVG(subjects.credits) as avg_credits'))
                ->join('subjects', 'planned_subjects.subject_id', '=', 'subjects.subject_id')
                ->where('planned_subjects.period_id', $currentPeriod->period_id)
                ->groupBy('planned_subjects.student_id')
                ->first()
            : null;

        // 8. Asignaturas más populares (para el gráfico)
        $popularSubjects = Subject::select(
                'subjects.subject_id',
                'subjects.code',
                'subjects.name',
                'subjects.level',
                DB::raw('COUNT(planned_subjects.student_id) as student_count')
            )
            ->leftJoin('planned_subjects', function($join) use ($currentPeriod) {
                $join->on('subjects.subject_id', '=', 'planned_subjects.subject_id')
                    ->when($currentPeriod, function($query) use ($currentPeriod) {
                        $query->where('planned_subjects.period_id', $currentPeriod->period_id);
                    });
            })
            ->groupBy('subjects.subject_id', 'subjects.code', 'subjects.name', 'subjects.level')
            ->orderByDesc('student_count')
            ->limit(10)
            ->get();

        // Preparar datos para gráficos
        $chartSubjects = $popularSubjects->pluck('code');
        $chartEnrollments = $popularSubjects->pluck('student_count');
        $trendLabels = $enrollmentTrend->pluck('code');
        $trendData = $enrollmentTrend->pluck('enrollment_count');

        // Obtener todos los periodos para el filtro
        $allPeriods = RegistrationPeriod::orderBy('start_date', 'desc')->get();

        return view('admin.dashboard', [
            'currentPeriod' => $currentPeriod,
            'studentsCount' => $studentsCount,
            'activeEnrollments' => $activeEnrollments,
            'subjectsCount' => $subjectsCount,
            'demandByLevel' => $demandByLevel,
            'noDemandSubjects' => $noDemandSubjects,
            'enrollmentTrend' => $enrollmentTrend,
            'averageCredits' => $averageCredits ? round($averageCredits->avg_credits, 2) : 0,
            'popularSubjects' => $popularSubjects,
            'chartSubjects' => $chartSubjects,
            'chartEnrollments' => $chartEnrollments,
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
            'allPeriods' => $allPeriods
        ]);
    }
    public function index()
    {
        $periods = RegistrationPeriod::orderBy('start_date', 'desc')->paginate(10);
        return view('admin.periods.index', compact('periods'));
    }

    public function create()
    {
        return view('admin.periods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:45|unique:registration_periods,code',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        RegistrationPeriod::create([
            'code' => $validated['code'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'admin_id' => Auth::id()
        ]);

        return redirect()->route('admin.periods.index')
            ->with('success', 'Período académico creado exitosamente');
    }

    public function edit(RegistrationPeriod $period)
    {
        return view('admin.periods.edit', compact('period'));
    }

    public function update(Request $request, RegistrationPeriod $period)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:45',
                Rule::unique('registration_periods')->ignore($period->period_id, 'period_id')
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $period->update($validated);

        return redirect()->route('admin.periods.index')
            ->with('success', 'Período académico actualizado exitosamente');
    }

    public function destroy(RegistrationPeriod $period)
    {
        // Verificar si hay registros asociados antes de eliminar
        if ($period->approvedSubjects()->exists() || $period->plannedSubjects()->exists()) {
            return back()->with('error', 'No se puede eliminar el período porque tiene registros asociados');
        }

        $period->delete();

        return redirect()->route('admin.periods.index')
            ->with('success', 'Período académico eliminado exitosamente');
    }
    public function subjectsDemand(Request $request)
    {
    $currentPeriod = $this->getCurrentPeriod();
    $levels = Subject::select('level')->distinct()->orderBy('level')->pluck('level');
    
    $selectedLevel = $request->input('level');
    
    // Definir umbrales directamente
    $lowDemandThreshold = 5;  // Umbral para baja demanda
    $highDemandThreshold = 15; // Umbral para alta demanda
    
    // Main subjects query
    $subjectsQuery = Subject::select(
            'subjects.*', 
            'subjects_demand.student_count'
        )
        ->leftJoin('subjects_demand', function($join) use ($currentPeriod) {
            $join->on('subjects.subject_id', '=', 'subjects_demand.subject_id')
                ->when($currentPeriod, function($query) use ($currentPeriod) {
                    $query->where('subjects_demand.period_id', $currentPeriod->period_id);
                });
        })
        ->when($selectedLevel, function($query) use ($selectedLevel) {
            return $query->where('subjects.level', $selectedLevel);
        })
        ->orderBy('subjects_demand.student_count', 'desc');
    
    // Get paginated results
    $subjects = $subjectsQuery->paginate(15);
    
    // Additional statistics
    $maxDemand = $subjectsQuery->clone()
        ->max('subjects_demand.student_count') ?? 0;
    
    $mostDemandedSubject = $subjectsQuery->clone()
        ->orderBy('subjects_demand.student_count', 'desc')
        ->first();
    
    $zeroDemandCount = Subject::whereNotIn('subjects.subject_id', function($query) use ($currentPeriod) {
            $query->select('subject_id')
                ->from('subjects_demand')
                ->when($currentPeriod, function($query) use ($currentPeriod) {
                    $query->where('period_id', $currentPeriod->period_id);
                });
        })
        ->when($selectedLevel, function($query) use ($selectedLevel) {
            return $query->where('level', $selectedLevel);
        })
        ->count();
    
    // Clasificar asignaturas por nivel de demanda usando los umbrales definidos
    $lowDemandCount = Subject::whereHas('demand', function($query) use ($currentPeriod, $lowDemandThreshold) {
            $query->when($currentPeriod, function($q) use ($currentPeriod) {
                $q->where('period_id', $currentPeriod->period_id);
            })
            ->where('student_count', '>', 0)
            ->where('student_count', '<', $lowDemandThreshold);
        })
        ->when($selectedLevel, function($query) use ($selectedLevel) {
            return $query->where('level', $selectedLevel);
        })
        ->count();
    
    $mediumDemandCount = Subject::whereHas('demand', function($query) use ($currentPeriod, $lowDemandThreshold, $highDemandThreshold) {
            $query->when($currentPeriod, function($q) use ($currentPeriod) {
                $q->where('period_id', $currentPeriod->period_id);
            })
            ->where('student_count', '>=', $lowDemandThreshold)
            ->where('student_count', '<', $highDemandThreshold);
        })
        ->when($selectedLevel, function($query) use ($selectedLevel) {
            return $query->where('level', $selectedLevel);
        })
        ->count();
    
    $highDemandCount = Subject::whereHas('demand', function($query) use ($currentPeriod, $highDemandThreshold) {
            $query->when($currentPeriod, function($q) use ($currentPeriod) {
                $q->where('period_id', $currentPeriod->period_id);
            })
            ->where('student_count', '>=', $highDemandThreshold);
        })
        ->when($selectedLevel, function($query) use ($selectedLevel) {
            return $query->where('level', $selectedLevel);
        })
        ->count();
    
    // Total de estudiantes registrados
    $totalStudents = User::whereHas('approvedSubjects', function($query) use ($currentPeriod) {
            $query->when($currentPeriod, function($q) use ($currentPeriod) {
                $q->where('period_id', $currentPeriod->period_id);
            });
        })
        ->orWhereHas('plannedSubjects', function($query) use ($currentPeriod) {
            $query->when($currentPeriod, function($q) use ($currentPeriod) {
                $q->where('period_id', $currentPeriod->period_id);
            });
        })
        ->distinct()
        ->count();
    
    // Demand by level data for chart
    $demandByLevel = [];
    foreach ($levels as $level) {
        $demandByLevel[$level] = DB::table('subjects_demand')
            ->join('subjects', 'subjects_demand.subject_id', '=', 'subjects.subject_id')
            ->when($currentPeriod, function($query) use ($currentPeriod) {
                $query->where('subjects_demand.period_id', $currentPeriod->period_id);
            })
            ->where('subjects.level', $level)
            ->sum('subjects_demand.student_count');
    }
    
    return view('admin.subjects.demand', compact(
        'subjects', 
        'levels', 
        'selectedLevel', 
        'currentPeriod',
        'maxDemand',
        'mostDemandedSubject',
        'zeroDemandCount',
        'lowDemandCount',
        'mediumDemandCount',
        'highDemandCount',
        'totalStudents',
        'demandByLevel',
        'lowDemandThreshold',
        'highDemandThreshold'
    ));
}

public function getSubjectStudents(Subject $subject)
{
    $currentPeriod = $this->getCurrentPeriod();
    
    $approvedStudents = $subject->approvedStudents()
        ->when($currentPeriod, function($query) use ($currentPeriod) {
            $query->where('period_id', $currentPeriod->period_id);
        })
        ->select('users.name', 'users.email', DB::raw("'approved' as type"), 'approved_subjects.registration_date')
        ->get();
    
    $plannedStudents = $subject->plannedStudents()
        ->when($currentPeriod, function($query) use ($currentPeriod) {
            $query->where('period_id', $currentPeriod->period_id);
        })
        ->select('users.name', 'users.email', DB::raw("'planned' as type"), 'planned_subjects.registration_date')
        ->get();
    
    $students = $approvedStudents->merge($plannedStudents);
    
    return response()->json($students);
}
    /**
     * Obtener el período de registro actual
     */
    protected function getCurrentPeriod()
    {
        return RegistrationPeriod::latest('start_date')->first();
    }
}
