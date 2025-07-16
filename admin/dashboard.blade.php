@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('content')
<div class="container-fluid" style="background-color: var(--bg-content); min-height: 100vh;">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 py-3">
        <h1 class="h3 mb-0" style="color: var(--text-primary); font-weight: 600;">Panel de Administración</h1>
        <div class="period-indicator" style="background-color: var(--primary-100); color: var(--primary-800); padding: 0.5rem 1rem; border-radius: 20px;">
            <i class="fas fa-calendar-alt me-2"></i>
            <span>{{ $currentPeriod ? $currentPeriod->code : 'Ningún período activo' }}</span>
        </div>
    </div>

    <!-- Tarjetas de Métricas Principales -->
    <div class="row">
        <!-- Total Estudiantes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border-left: 4px solid var(--primary-500); border-radius: 8px; box-shadow: 0 4px 12px rgba(76, 175, 80, 0.1);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold mb-1" style="color: var(--primary-300);">Total Estudiantes</div>
                            <div class="h2 mb-0" style="color: var(--text-light); font-weight: 700;">{{ $studentsCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x" style="color: var(--primary-300);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estudiantes Activos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border-left: 4px solid var(--primary-400); border-radius: 8px; box-shadow: 0 4px 12px rgba(129, 199, 132, 0.1);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold mb-1" style="color: var(--primary-300);">Estudiantes Activos</div>
                            <div class="h2 mb-0" style="color: var(--text-light); font-weight: 700;">{{ $activeEnrollments }}</div>
                            <div class="mt-2 small" style="color: var(--text-muted);">
                                {{ number_format(($activeEnrollments/$studentsCount)*100, 1) }}% participación
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x" style="color: var(--primary-300);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Asignaturas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border-left: 4px solid var(--primary-600); border-radius: 8px; box-shadow: 0 4px 12px rgba(67, 160, 71, 0.1);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold mb-1" style="color: var(--primary-300);">Total Asignaturas</div>
                            <div class="h2 mb-0" style="color: var(--text-light); font-weight: 700;">{{ $subjectsCount }}</div>
                            <div class="mt-2 small" style="color: var(--text-muted);">
                                {{ $noDemandSubjects }} sin demanda
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x" style="color: var(--primary-300);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Créditos Promedio -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border-left: 4px solid var(--primary-700); border-radius: 8px; box-shadow: 0 4px 12px rgba(56, 142, 60, 0.1);">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold mb-1" style="color: var(--primary-300);">Créditos Promedio</div>
                            <div class="h2 mb-0" style="color: var(--text-light); font-weight: 700;">
                                {{ $averageCredits }}
                            </div>
                            <div class="mt-2 small" style="color: var(--text-muted);">
                                por estudiante
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x" style="color: var(--primary-300);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div class="row">
        <!-- Demanda de Asignaturas -->
        <div class="col-lg-8 mb-4">
            <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-header py-3" style="background-color: var(--primary-500);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold" style="color: var(--text-light);">
                            <i class="fas fa-chart-bar me-2"></i> Demanda de Asignaturas
                        </h6>
                        <select class="form-select form-select-sm" style="width: auto; background-color: var(--primary-400); color: white; border: none;">
                            <option value="current">Período Actual</option>
                            @foreach($allPeriods as $period)
                            <option value="{{ $period->period_id }}">{{ $period->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="demandChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tendencia de Matrícula -->
        <div class="col-lg-4 mb-4">
            <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-header py-3" style="background-color: var(--primary-500);">
                    <h6 class="m-0 font-weight-bold" style="color: var(--text-light);">
                        <i class="fas fa-chart-line me-2"></i> Tendencia de Matrícula
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demanda por Nivel -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-header py-3" style="background-color: var(--primary-500);">
                    <h6 class="m-0 font-weight-bold" style="color: var(--text-light);">
                        <i class="fas fa-layer-group me-2"></i> Demanda por Nivel Académico
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead style="background-color: var(--primary-50); color: var(--primary-800);">
                                <tr>
                                    <th style="border-top-left-radius: 8px;">Nivel</th>
                                    <th>Total Asignaturas</th>
                                    <th>Total Matrículas</th>
                                    <th>Máxima Demanda</th>
                                    <th>Mínima Demanda</th>
                                    <th style="border-top-right-radius: 8px;">Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($demandByLevel as $level)
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td>
                                        <span class="badge" style="background-color: var(--primary-100); color: var(--primary-700);">
                                            Nivel {{ $level->level }}
                                        </span>
                                    </td>
                                    <td>{{ $level->total_subjects }}</td>
                                    <td>{{ $level->total_enrollments }}</td>
                                    <td>{{ $level->max_demand ?? 0 }}</td>
                                    <td>{{ $level->min_demand }}</td>
                                    <td>
                                        {{ $level->total_subjects > 0 ? round($level->total_enrollments / $level->total_subjects, 1) : 0 }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas y Asignaturas Más Demandadas -->
    <div class="row">
        <!-- Acciones Rápidas -->
        <div class="col-lg-4 mb-4">
            <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-header py-3" style="background-color: var(--primary-500);">
                    <h6 class="m-0 font-weight-bold" style="color: var(--text-light);">
                        <i class="fas fa-bolt me-2"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.periods.index') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center py-3"
                           style="border-left: 3px solid var(--primary-500); transition: all 0.3s;">
                            <i class="fas fa-calendar-plus me-3" style="color: var(--primary-500);"></i>
                            <span style="color: var(--text-primary);">Gestionar Períodos</span>
                        </a>
                        <a href="{{ route('admin.subjects.demand') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center py-3"
                           style="border-left: 3px solid var(--primary-500); transition: all 0.3s;">
                            <i class="fas fa-chart-pie me-3" style="color: var(--primary-500);"></i>
                            <span style="color: var(--text-primary);">Analizar Demanda</span>
                        </a>
                        <a href="#" 
                           class="list-group-item list-group-item-action d-flex align-items-center py-3"
                           style="border-left: 3px solid var(--primary-500); transition: all 0.3s;">
                            <i class="fas fa-user-graduate me-3" style="color: var(--primary-500);"></i>
                            <span style="color: var(--text-primary);">Gestionar Estudiantes</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asignaturas Más Demandadas -->
        <div class="col-lg-8 mb-4">
            <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <div class="card-header py-3" style="background-color: var(--primary-500);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold" style="color: var(--text-light);">
                            <i class="fas fa-star me-2"></i> Asignaturas Más Demandadas
                        </h6>
                        <div class="dropdown">
                            <button class="btn btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" 
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    style="background-color: var(--primary-400); color: white; border: none;">
                                <i class="fas fa-filter me-1"></i> Filtros
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="#">Top 5</a></li>
                                <li><a class="dropdown-item" href="#">Top 10</a></li>
                                <li><a class="dropdown-item" href="#">Por nivel</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead style="background-color: var(--primary-50); color: var(--primary-800);">
                                <tr>
                                    <th style="border-top-left-radius: 8px;">#</th>
                                    <th>Asignatura</th>
                                    <th>Estudiantes</th>
                                    <th>Nivel</th>
                                    <th style="border-top-right-radius: 8px;">Demanda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($popularSubjects as $index => $subject)
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td>
                                        <div class="rank-badge" style="background-color: var(--primary-100); color: var(--primary-700); 
                                            width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; 
                                            border-radius: 50%; font-weight: 600;">
                                            {{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold" style="color: var(--text-primary);">{{ $subject->code }}</div>
                                        <div style="color: var(--text-muted); font-size: 0.9rem;">{{ $subject->name }}</div>
                                    </td>
                                    <td style="font-weight: 600; color: var(--primary-600);">{{ $subject->student_count }}</td>
                                    <td>
                                        <span class="badge" style="background-color: var(--primary-100); color: var(--primary-700);">
                                            Nivel {{ $subject->level }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 8px; background-color: var(--border-light); border-radius: 4px;">
                                            <div class="progress-bar" style="width: {{ ($subject->student_count / $activeEnrollments) * 100 }}%; 
                                                background-color: var(--primary-500);"></div>
                                        </div>
                                        <small style="color: var(--text-muted);">
                                            {{ number_format(($subject->student_count / $activeEnrollments) * 100, 1) }}%
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de demanda de asignaturas
    const demandCtx = document.getElementById('demandChart').getContext('2d');
    const demandChart = new Chart(demandCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartSubjects) !!},
            datasets: [{
                label: 'Estudiantes Planificados',
                data: {!! json_encode($chartEnrollments) !!},
                backgroundColor: 'rgba(78, 115, 223, 0.7)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: 'rgba(78, 115, 223, 0.9)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.y} estudiantes (${((context.parsed.y / {{ $activeEnrollments }}) * 100).toFixed(1)}%)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Gráfico de tendencia de matrícula
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendLabels) !!},
            datasets: [{
                label: 'Estudiantes Matriculados',
                data: {!! json_encode($trendData) !!},
                backgroundColor: 'rgba(54, 185, 204, 0.2)',
                borderColor: 'rgba(54, 185, 204, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Filtro de asignaturas
    document.getElementById('subjectFilter').addEventListener('change', function() {
        // Lógica para actualizar el gráfico según el filtro
        console.log('Filtro seleccionado:', this.value);
    });
});
</script>

<style>
    .metric-card {
        transition: all 0.3s ease;
    }
    
    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .list-group-item:hover {
        background-color: var(--primary-50) !important;
    }
    
    .progress-bar {
        position: relative;
    }
    
    .progress-bar::after {
        content: '';
        position: absolute;
        right: -5px;
        top: -3px;
        width: 10px;
        height: 10px;
        background-color: inherit;
        border-radius: 50%;
        opacity: 0.8;
    }
    
    .table-hover tbody tr:hover {
        background-color: var(--primary-50) !important;
    }
</style>
@endpush