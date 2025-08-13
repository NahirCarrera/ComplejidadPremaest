@extends('layouts.app')

@section('title', 'Asignaturas Aprobadas')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header py-3" style="background-color: var(--primary-900);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-white">
                            <i class="fas fa-check-circle me-2"></i> Asignaturas Aprobadas
                        </h4>
                        <div class="badge bg-white text-primary" style="font-size: 1rem;">
                            <i class="fas fa-star me-1"></i> {{ $totalCredits }} créditos
                        </div>
                    </div>
                </div>

                <div class="card-body" style="background-color: var(--bg-content);">
                    <!-- Filtro y estadísticas -->
                    <div class="row mb-4 g-3">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <form method="GET" action="{{ route('student.records.approved') }}" class="row g-2">
                                        <div class="col-md-8">
                                            <select name="period_filter" class="form-select" 
                                                    style="border: 1px solid var(--border-dark); border-radius: 8px;"
                                                    onchange="this.form.submit()">
                                                <option value="">Todos los períodos</option>
                                                @foreach($periods as $period)
                                                    <option value="{{ $period->period_id }}" 
                                                        {{ request('period_filter') == $period->period_id ? 'selected' : '' }}>
                                                        {{ $period->code }} ({{ $period->start_date->format('m/Y') }} - {{ $period->end_date->format('m/Y') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn w-100" 
                                                    style="background-color: var(--primary-500); color: white; border-radius: 8px;">
                                                <i class="fas fa-filter me-1"></i> Filtrar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0" style="background-color: var(--primary-100);">
                                <div class="card-body py-3 text-center">
                                    <h6 class="mb-0" style="color: var(--primary-800);">
                                        <i class="fas fa-book me-2"></i> {{ $approvedSubjects->total() }} asignaturas aprobadas
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($approvedSubjects->isEmpty())
                        <div class="alert alert-primary text-center" 
                             style="background-color: var(--primary-100); border-color: var(--primary-300); color: var(--primary-800); border-radius: 8px;">
                            <i class="fas fa-info-circle me-2"></i> No tienes asignaturas aprobadas registradas.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-borderless" style="border-radius: 8px; overflow: hidden;">
                                <thead>
                                    <tr style="background-color: var(--primary-300); color: white;">
                                        <th style="width: 5%; border-top-left-radius: 8px;">#</th>
                                        <th style="width: 15%;">Código</th>
                                        <th style="width: 60%;">Asignatura</th>
                                        <th style="width: 10%; text-align: center; border-top-right-radius: 8px;">Créditos</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($approvedSubjects as $index => $subject)
                                    <tr style="background-color: white; border-bottom: 1px solid var(--border-light);">
                                        <td class="align-middle" style="color: var(--text-muted);">{{ $approvedSubjects->firstItem() + $index }}</td>
                                        <td class="align-middle">
                                            <span class="badge" style="background-color: var(--primary-100); color: var(--primary-700);">
                                                {{ $subject->code }}
                                            </span>
                                        </td>
                                        <td class="align-middle fw-bold" style="color: var(--text-primary);">{{ $subject->name }}</td>
                                        <td class="align-middle text-center" style="color: var(--primary-600); font-weight: 600;">{{ $subject->credits }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: var(--primary-50);">
                                        <td colspan="3" class="fw-bold text-end" style="color: var(--primary-800);">Total créditos aprobados:</td>
                                        <td class="text-center fw-bold" style="color: var(--primary-700);">{{ $totalCredits }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small" style="color: var(--text-muted);">
                                Mostrando {{ $approvedSubjects->firstItem() }} a {{ $approvedSubjects->lastItem() }} de {{ $approvedSubjects->total() }} registros
                            </div>
                            <div>
                                {{ $approvedSubjects->withQueryString()->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: var(--primary-50);
        --bs-table-hover-bg: var(--primary-100);
    }
    
    .pagination .page-item.active .page-link {
        background-color: var(--primary-500);
        border-color: var(--primary-500);
    }
    
    .pagination .page-link {
        color: var(--primary-600);
    }
    
    .table tbody tr:hover {
        transform: translateX(4px);
        transition: transform 0.2s ease;
    }
</style>

@endsection