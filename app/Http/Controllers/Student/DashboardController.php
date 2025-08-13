<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ApprovedSubject;
use App\Models\RegistrationPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use \App\Http\Controllers\Concerns\HasCurrentPeriod;

    /**
     * Dashboard principal del estudiante
     */
    public function dashboard()
    {
        $user = Auth::user();
        $currentPeriod = RegistrationPeriod::latest('start_date')->first();

        // Asignaturas aprobadas
        $approvedCount = ApprovedSubject::where('student_id', $user->id)->count();
        $approvedCredits = ApprovedSubject::where('student_id', $user->id)
            ->join('subjects', 'approved_subjects.subject_id', '=', 'subjects.subject_id')
            ->sum('subjects.credits');

        // Asignaturas planificadas (para el período actual si existe)
        $plannedCount = $currentPeriod 
            ? DB::table('planned_subjects')
                ->where('student_id', $user->id)
                ->where('period_id', $currentPeriod->period_id)
                ->count()
            : 0;

        $totalCredits = $currentPeriod 
            ? DB::table('subjects')
                ->sum('subjects.credits')
            : 0;
        $pendingCredits = max(0, $totalCredits - $approvedCredits);
        $progressPercentage = $totalCredits > 0 ? round(($approvedCredits / $totalCredits) * 100) : 0;

        return view('student.dashboard', [
            'approvedCount' => $approvedCount,
            'plannedCount' => $plannedCount,
            'approvedCredits' => $approvedCredits,
            'currentPeriod' => $currentPeriod,
            'totalCredits' => $totalCredits,
            'pendingCredits' => $pendingCredits,
            'progressPercentage' => $progressPercentage
        ]);
    }
}