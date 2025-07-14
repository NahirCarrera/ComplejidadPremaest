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
