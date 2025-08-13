<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PeriodController extends Controller
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
}
