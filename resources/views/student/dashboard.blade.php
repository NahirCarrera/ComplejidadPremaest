@extends('layouts.app')

@section('title', 'Dashboard Estudiante')

@section('content')
<div class="container-fluid" style="background-color: var(--bg-content); min-height: 100vh;">
    <!-- Page Heading -->
    <div class="d-flex align-items-center justify-content-between mb-4 py-3">
        <h1 class="h3 mb-0" style="color: var(--text-primary); font-weight: 600;">Panel del Estudiante</h1>
        <div class="period-indicator" style="background-color: var(--primary-100); color: var(--primary-800); padding: 0.5rem 1rem; border-radius: 20px;">
            <i class="fas fa-calendar-alt me-2"></i>
            <span>{{ $currentPeriod ? $currentPeriod->code : 'Ninguno' }}</span>
        </div>
    </div>

    <!-- Cards de Estadísticas -->
    <div class="row">
        <!-- Asignaturas Aprobadas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border-left: 4px solid var(--primary-500); border-radius: 8px; box-shadow: 0 4px 12px rgba(76, 175, 80, 0.1);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold mb-1" style="color: var(--primary-300);">Asignaturas Aprobadas</div>
                            <div class="h2 mb-0" style="color: var(--text-light); font-weight: 700;">{{ $approvedCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x" style="color: var(--primary-300);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asignaturas Planificadas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border-left: 4px solid var(--primary-400); border-radius: 8px; box-shadow: 0 4px 12px rgba(129, 199, 132, 0.1);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold mb-1" style="color: var(--primary-300);">Asignaturas Planificadas</div>
                            <div class="h2 mb-0" style="color: var(--text-light); font-weight: 700;">{{ $plannedCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x" style="color: var(--primary-300);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Créditos Aprobados -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border-left: 4px solid var(--primary-600); border-radius: 8px; box-shadow: 0 4px 12px rgba(67, 160, 71, 0.1);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold mb-1" style="color: var(--primary-300);">Créditos Aprobados</div>
                            <div class="h2 mb-0" style="color: var(--text-light); font-weight: 700;">{{ $approvedCredits }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x" style="color: var(--primary-300);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       <!-- Período de Prematrícula - Versión Simplificada -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card h-100" style="border-left: 4px solid var(--primary-600); border-radius: 8px; box-shadow: 0 4px 12px rgba(67, 160, 71, 0.1);">
        <div class="card-body">
            <div class="d-flex flex-column h-100">
                <!-- Título -->
                <div class="text-xs font-weight-bold mb-3" style="color: var(--primary-300);">
                    <i class="fas fa-calendar-day me-1"></i> Fechas
                </div>
                
                <!-- Fechas -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="color: var(--text-muted); font-size: 0.7rem;">Inicio</span>
                        <span style="color: var(--text-muted); font-size: 0.7rem;">Fin</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color: var(--text-light); font-weight: 600;">
                            {{ $currentPeriod ? \Carbon\Carbon::parse($currentPeriod->start_date)->format('d/m/Y') : '--/--/----' }}
                        </span>
                        <span style="color: var(--text-light); font-weight: 600;">
                            {{ $currentPeriod ? \Carbon\Carbon::parse($currentPeriod->end_date)->format('d/m/Y') : '--/--/----' }}
                        </span>
                    </div>
                </div>
                
                <!-- Días restantes -->
                @if($currentPeriod)
                    @php
                        $endDate = \Carbon\Carbon::parse($currentPeriod->end_date);
                        $today = \Carbon\Carbon::now();
                        
                        //castear a int para ignorar decimales
                       
                        $remainingDays = (int)max(0, $today->diffInDays($endDate, false));
                        // Si la fecha de fin ya pasó, mostrar 0
                        if ($remainingDays < 0) {
                            $remainingDays = 0;
                        }
                    @endphp
                    
                    <div class="mt-auto text-center">
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Días restantes</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-400); line-height: 1;">
                            {{ $remainingDays }}
                        </div>
                    </div>
                @else
                    <div class="text-center mt-2" style="color: var(--text-muted);">
                        No hay período activo
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
    </div>

    <!-- Secciones Inferiores -->
    <div class="row">
        <!-- Acciones Rápidas -->
        <div class="col-lg-6 mb-4">
            <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-header py-3" style="background-color: var(--primary-500);">
                    <h6 class="m-0 font-weight-bold" style="color: var(--text-light);">Acciones Rápidas</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('student.records.upload') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center py-3"
                           style="border-left: 3px solid var(--primary-500); transition: all 0.3s;">
                            <i class="fas fa-file-upload me-3" style="color: var(--primary-500);"></i>
                            <span style="color: var(--text-primary);">Registrar Record Académico</span>
                        </a>
                        <a href="{{ route('student.pre-enrollment.plan') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center py-3"
                           style="border-left: 3px solid var(--primary-500); transition: all 0.3s;">
                            <i class="fas fa-calendar-alt me-3" style="color: var(--primary-500);"></i>
                            <span style="color: var(--text-primary);">Planificación Académica</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Créditos -->
        <div class="col-lg-6 mb-4">
            <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-header py-3" style="background-color: var(--primary-500);">
                    <h6 class="m-0 font-weight-bold" style="color: var(--text-light);">Detalle de Créditos</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-4">
                            <div class="credit-card p-3" style="background-color: var(--primary-100); border-radius: 10px;">
                                <p class="mb-2" style="color: var(--text-primary);">Total Créditos</p>
                                <h3 class="font-weight-bold" style="color: var(--primary-700);">{{ $totalCredits }}</h3>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="credit-card p-3" style="background-color: var(--primary-100); border-radius: 10px;">
                                <p class="mb-2" style="color: var(--text-secondary);">Créditos Pendientes</p>
                                <h3 class="font-weight-bold" style="color: var(--primary-700);">{{ $pendingCredits }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="progress-info">
                        <div class="d-flex justify-content-between mb-1">
                            <span style="color: var(--text-light);">Progreso académico</span>
                            <span style="color: var(--primary-700); font-weight: 600;">{{ $progressPercentage }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: {{ $progressPercentage }}%; background: linear-gradient(90deg, var(--primary-400), var(--primary-600));"
                                 aria-valuenow="{{ $progressPercentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection