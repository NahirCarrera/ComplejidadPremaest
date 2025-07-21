@extends('layouts.app')

@section('title', 'Demanda de Asignaturas')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header py-3" style="background-color: var(--primary-900);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-white">
                            <i class="fas fa-chart-line me-2"></i> Demanda de Asignaturas
                        </h4>
                        <div class="d-flex">
                            <select id="levelFilter" class="form-select me-2" style="border-radius: 8px;">
                                <option value="">Todos los niveles</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level }}" {{ $selectedLevel == $level ? 'selected' : '' }}>Nivel {{ $level }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm me-2" id="filterButton"
                                    style="background-color: var(--primary-500); color: white; border-radius: 8px;">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                            <button class="btn btn-sm" 
                                    style="background-color: var(--success-500); color: white; border-radius: 8px;">
                                <i class="fas fa-file-excel me-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="background-color: var(--bg-content);">
                    <!-- Periodo actual y total estudiantes -->
                    <div class="row mb-4 g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-calendar-alt fa-2x" style="color: var(--primary-500);"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1" style="color: var(--text-muted);">Período Académico</h6>
                                            <h5 class="mb-0" style="color: var(--text-light);">
                                                {{ $currentPeriod ? $currentPeriod->code : 'No hay período activo' }}
                                                @if($currentPeriod)
                                                    <small class="text-muted">
                                                        ({{ $currentPeriod->start_date->format('d/m/Y') }} - {{ $currentPeriod->end_date->format('d/m/Y') }})
                                                    </small>
                                                @endif
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-users fa-2x" style="color: var(--primary-500);"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1" style="color: var(--text-muted);">Total Estudiantes</h6>
                                            <h5 class="mb-0" style="color: var(--text-light);">
                                                {{ $totalStudents }} estudiantes registrados
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas de demanda -->
                    <div class="row mb-4 g-3">
                        <!-- Total asignaturas -->
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: var(--primary-100);">
                                <div class="card-body text-center py-3">
                                    <h6 class="mb-1" style="color: var(--primary-800);">
                                        <i class="fas fa-book me-2"></i> Asignaturas
                                    </h6>
                                    <h4 class="mb-0" style="color: var(--primary-700);">
                                        {{ $subjects->total() }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sin demanda -->
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: var(--danger-100);">
                                <div class="card-body text-center py-3">
                                    <h6 class="mb-1" style="color: var(--danger-800);">
                                        <i class="fas fa-exclamation-circle me-2"></i> Sin demanda
                                    </h6>
                                    <h4 class="mb-0" style="color: var(--danger-700);">
                                        {{ $zeroDemandCount ?? 0 }}
                                        <small class="text-muted">({{ $subjects->total() > 0 ? round(($zeroDemandCount/$subjects->total())*100, 1) : 0 }}%)</small>
                                    </h4>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mayor demanda -->
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: var(--success-100);">
                                <div class="card-body text-center py-3">
                                    <h6 class="mb-1" style="color: var(--success-800);">
                                        <i class="fas fa-chart-line me-2"></i> Mayor demanda
                                    </h6>
                                    @php
                                        $maxDemandPercentage = $totalStudents > 0
                                            ? round(($maxDemand / $totalStudents) * 100, 1)
                                            : 0;
                                    @endphp

                                    <h4 class="mb-0" style="color: var(--success-700);">
                                        {{ $maxDemand }}
                                        @if($totalStudents > 0)
                                            <small class="text-muted">({{ $maxDemandPercentage }}%)</small>
                                        @endif
                                    </h4>

                                    @if($mostDemandedSubject)
                                    <p class="mb-0 small" style="color: var(--success-600);">
                                        {{ Str::limit($mostDemandedSubject->name, 20) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Demanda promedio -->
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: var(--warning-100);">
                                <div class="card-body text-center py-3">
                                    <h6 class="mb-1" style="color: var(--warning-800);">
                                        <i class="fas fa-balance-scale me-2"></i> Promedio
                                    </h6>
                                    @php
                                    $averageDemand = $subjects->isNotEmpty()
                                        ? round($subjects->avg('student_count'), 1)
                                        : 0;

                                    $averageDemandPercentage = ($totalStudents > 0 && $averageDemand > 0)
                                        ? round(($averageDemand / $totalStudents) * 100, 1)
                                        : 0;
                                @endphp

                                <h4 class="mb-0" style="color: var(--warning-700);">
                                    {{ $averageDemand }}
                                    @if($totalStudents > 0)
                                        <small class="text-muted">({{ $averageDemandPercentage }}%)</small>
                                    @endif
                                </h4>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de asignaturas -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header py-3" style="background-color: var(--primary-100); border-bottom: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0" style="color: var(--primary-800);">
                                    <i class="fas fa-list-ul me-2"></i> Detalle por Asignatura
                                </h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" 
                                            data-bs-toggle="dropdown" aria-expanded="false"
                                            style="background-color: var(--primary-500); color: white; border-radius: 8px;">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i> Exportar</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.subjects.demand') }}"><i class="fas fa-sync-alt me-2"></i> Ver todas</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless" style="border-radius: 8px; overflow: hidden;">
                                    <thead>
                                        <tr style="background-color: var(--primary-300); color: white;">
                                            <th style="width: 5%; border-top-left-radius: 8px;">#</th>
                                            <th style="width: 10%;">Código</th>
                                            <th style="width: 30%;">Asignatura</th>
                                            <th style="width: 10%;">Nivel</th>
                                            <th style="width: 10%;">Créditos</th>
                                            <th style="width: 20%;">Estudiantes</th>
                                            <th style="width: 10%;">% Total</th>
                                            <th style="width: 10%; border-top-right-radius: 8px;">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($subjects as $subject)
                                        <tr style="background-color: white; border-bottom: 1px solid var(--border-light);">
                                            <td class="align-middle" style="color: var(--text-muted);">
                                                {{ $loop->iteration + ($subjects->currentPage() - 1) * $subjects->perPage() }}
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge" style="background-color: var(--primary-100); color: var(--primary-700);">
                                                    {{ $subject->code }}
                                                </span>
                                            </td>
                                            <td class="align-middle fw-bold" style="color: var(--text-primary);">
                                                {{ $subject->name }}
                                            </td>
                                            <td class="align-middle" style="color: var(--text-muted);">
                                                Nivel {{ $subject->level }}
                                            </td>
                                            <td class="align-middle" style="color: var(--text-muted);">
                                                {{ $subject->credits }}
                                            </td>
                                            <td class="align-middle">
                                                <div class="progress" style="height: 24px; border-radius: 6px;">
                                                    <div class="progress-bar 
                                                        @if($subject->student_count > $highDemandThreshold) bg-success
                                                        @elseif($subject->student_count > $lowDemandThreshold) bg-info
                                                        @else bg-warning
                                                        @endif" 
                                                        role="progressbar" 
                                                        style="width: {{ min(100, ($subject->student_count / ($maxDemand ?: 1)) * 100) }}%"
                                                        aria-valuenow="{{ $subject->student_count }}" 
                                                        aria-valuemin="0" 
                                                        aria-valuemax="{{ $maxDemand }}">
                                                        {{ $subject->student_count }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle" style="color: var(--text-muted);">
                                                @if($totalStudents > 0)
                                                    {{ round(($subject->student_count / $totalStudents) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if($subject->student_count == 0)
                                                    <span class="badge" style="background-color: var(--danger-100); color: var(--danger-700);">Sin demanda</span>
                                                @elseif($subject->student_count < $lowDemandThreshold)
                                                    <span class="badge" style="background-color: var(--warning-100); color: var(--warning-700);">Baja</span>
                                                @elseif($subject->student_count > $highDemandThreshold)
                                                    <span class="badge" style="background-color: var(--success-100); color: var(--success-700);">Alta</span>
                                                @else
                                                    <span class="badge" style="background-color: var(--info-100); color: var(--info-700);">Media</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4" style="color: var(--text-muted);">
                                                <i class="fas fa-info-circle me-2"></i> No hay asignaturas con demanda para el filtro seleccionado
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($subjects->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small" style="color: var(--text-muted);">
                                    Mostrando {{ $subjects->firstItem() }} a {{ $subjects->lastItem() }} de {{ $subjects->total() }} registros
                                </div>
                                <div>
                                    {{ $subjects->withQueryString()->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header py-3" style="background-color: var(--primary-100);">
                                    <h5 class="mb-0" style="color: var(--primary-800);">
                                        <i class="fas fa-chart-bar me-2"></i> Demanda por Nivel
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="demandByLevelChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header py-3" style="background-color: var(--primary-100);">
                                    <h5 class="mb-0" style="color: var(--primary-800);">
                                        <i class="fas fa-chart-pie me-2"></i> Distribución de Demanda
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie">
                                        <canvas id="demandDistributionChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Estudiantes -->
<div class="modal fade" id="studentsModal" tabindex="-1" aria-labelledby="studentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
            <div class="modal-header py-3" style="background-color: var(--primary-900); color: white;">
                <h5 class="modal-title" id="studentsModalLabel">
                    <i class="fas fa-users me-2"></i> Estudiantes inscritos en <span id="modalSubjectName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background-color: var(--bg-content);">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr style="background-color: var(--primary-100);">
                                <th style="color: var(--primary-800);">Nombre</th>
                                <th style="color: var(--primary-800);">Email</th>
                                <th style="color: var(--primary-800);">Tipo</th>
                                <th style="color: var(--primary-800);">Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <!-- Datos cargados via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="background-color: var(--primary-50);">
                <button type="button" class="btn" data-bs-dismiss="modal"
                        style="background-color: var(--primary-500); color: white; border-radius: 8px;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    
    .progress {
        background-color: var(--border-light);
    }
    
    .table tbody tr:hover {
        transform: translateX(4px);
        transition: transform 0.2s ease;
        background-color: var(--primary-50) !important;
    }
    
    .pagination .page-item.active .page-link {
        background-color: var(--primary-500);
        border-color: var(--primary-500);
    }
    
    .pagination .page-link {
        color: var(--primary-600);
    }
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter functionality
        document.getElementById('filterButton').addEventListener('click', function() {
            const level = document.getElementById('levelFilter').value;
            const url = new URL("{{ route('admin.subjects.demand') }}");
            if(level) url.searchParams.append('level', level);
            window.location.href = url.toString();
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Demand by level chart
        const ctx = document.getElementById('demandByLevelChart').getContext('2d');
        const demandByLevelChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($levels) !!}.map(level => 'Nivel ' + level),
                datasets: [{
                    label: 'Total Estudiantes',
                    data: {!! json_encode($demandByLevel) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
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
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Demand distribution chart
        const ctx2 = document.getElementById('demandDistributionChart').getContext('2d');
        const demandDistributionChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Alta demanda', 'Demanda media', 'Baja demanda', 'Sin demanda'],
                datasets: [{
                    data: [
                        {{ $highDemandCount }},
                        {{ $mediumDemandCount }},
                        {{ $lowDemandCount }},
                        {{ $zeroDemandCount }}
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });

    function showStudentsModal(subjectId, subjectName) {
        $('#modalSubjectName').text(subjectName);
        $('#studentsTableBody').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>');
        
        fetch(`/admin/subjects/${subjectId}/students`)
            .then(response => response.json())
            .then(data => {
                if(data.length > 0) {
                    let html = '';
                    data.forEach(student => {
                        html += `
                            <tr>
                                <td>${student.name}</td>
                                <td>${student.email}</td>
                                <td>
                                    <span class="badge ${student.type === 'approved' ? 'bg-success' : 'bg-info'}">
                                        ${student.type === 'approved' ? 'Aprobada' : 'Planeada'}
                                    </span>
                                </td>
                                <td>${new Date(student.registration_date).toLocaleDateString()}</td>
                            </tr>
                        `;
                    });
                    $('#studentsTableBody').html(html);
                } else {
                    $('#studentsTableBody').html('<tr><td colspan="4" class="text-center py-4 text-muted">No hay estudiantes registrados para esta asignatura</td></tr>');
                }
                
                var modal = new bootstrap.Modal(document.getElementById('studentsModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                $('#studentsTableBody').html('<tr><td colspan="4" class="text-center py-4 text-danger">Error al cargar los estudiantes</td></tr>');
                
                var modal = new bootstrap.Modal(document.getElementById('studentsModal'));
                modal.show();
            });
    }
</script>
@endpush