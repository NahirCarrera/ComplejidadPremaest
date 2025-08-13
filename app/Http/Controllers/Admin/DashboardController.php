<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationPeriod;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
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
}
