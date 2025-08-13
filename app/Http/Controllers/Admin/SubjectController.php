<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasCurrentPeriod;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    use HasCurrentPeriod;

    public function subjectsDemand(Request $request)
    {
        $currentPeriod = $this->getCurrentPeriod();
        $levels = Subject::select('level')->distinct()->orderBy('level')->pluck('level');

        $selectedLevel = $request->input('level');

        // Umbrales
        $lowDemandThreshold = 5;
        $highDemandThreshold = 15;

        // Query principal de asignaturas
        $subjectsQuery = Subject::select('subjects.*', 'subjects_demand.student_count')
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

        $subjects = $subjectsQuery->paginate(15);

        $maxDemand = $subjectsQuery->clone()->max('subjects_demand.student_count') ?? 0;

        $mostDemandedSubject = $subjectsQuery->clone()
            ->orderBy('subjects_demand.student_count', 'desc')
            ->first();

        $zeroDemandCount = Subject::whereNotIn('subjects.subject_id', function($query) use ($currentPeriod) {
                $query->select('subject_id')
                    ->from('subjects_demand')
                    ->when($currentPeriod, function($q) use ($currentPeriod) {
                        $q->where('period_id', $currentPeriod->period_id);
                    });
            })
            ->when($selectedLevel, function($query) use ($selectedLevel) {
                return $query->where('level', $selectedLevel);
            })
            ->count();

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
}
