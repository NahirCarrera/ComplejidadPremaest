@extends('layouts.app')

@section('title', 'Planificación Académica')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-lg" style="border-radius: 12px; border: none; overflow: hidden;">
                <div class="card-header py-3" style="background-color: var(--primary-900);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-white">
                            <i class="fas fa-calendar-alt me-2"></i> Planificación Académica
                        </h4>
                        @if($currentPeriod)
                        <div class="badge bg-white text-primary" style="font-size: 1rem;">
                            <i class="fas fa-clock me-1"></i> {{ $currentPeriod->code }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card-body" style="background-color: var(--bg-content);">
                    <!-- Información del período actual -->
                    @if($currentPeriod)
                        <div class="alert mb-4" style="background-color: var(--primary-100); border-left: 4px solid var(--primary-500); border-radius: 8px;">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-week me-3" style="font-size: 1.5rem; color: var(--primary-600);"></i>
                                        <div>
                                            <h6 class="mb-1" style="color: var(--primary-800);">Período actual</h6>
                                            <p class="mb-0" style="color: var(--text-primary);">
                                                {{ $currentPeriod->start_date->format('d/m/Y') }} - {{ $currentPeriod->end_date->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <div class="me-3 text-end">
                                            <h6 class="mb-1" style="color: var(--primary-800);">Créditos planificados</h6>
                                            <p class="mb-0 fw-bold" style="color: var(--primary-700); font-size: 1.2rem;">
                                                {{ $plannedCredits }}
                                            </p>
                                        </div>
                                        <div class="icon-circle" style="background-color: var(--primary-200); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                            <i class="fas fa-bookmark" style="color: var(--primary-600); font-size: 1.2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning" style="border-radius: 8px;">
                            <i class="fas fa-exclamation-triangle me-2"></i> No hay un período de registro activo actualmente.
                        </div>
                    @endif

                    <!-- Formulario de planificación -->
                    @if($currentPeriod)
                    <form method="POST" action="{{ route('student.pre-enrollment.process') }}" id="planningForm">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $currentPeriod->period_id }}">

                        <div class="row g-4">
                            <!-- Asignaturas disponibles -->
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header py-3" style="background-color: var(--primary-500); color: white; border-radius: 8px 8px 0 0 !important;">
                                        <h5 class="mb-0">
                                            <i class="fas fa-list-check me-2"></i> Asignaturas Disponibles
                                        </h5>
                                    </div>
                                    <div class="card-body" style="background-color: white; border-radius: 0 0 8px 8px;">
                                        @if($availableSubjects->isEmpty())
                                            <div class="alert alert-info" style="border-radius: 8px;">
                                                <i class="fas fa-info-circle me-2"></i> No hay asignaturas disponibles para este período.
                                            </div>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead>
                                                        <tr style="background-color: var(--primary-50); color: var(--primary-800);">
                                                            <th width="50" style="border-top-left-radius: 8px;"></th>
                                                            <th>Asignatura</th>
                                                            <th width="100" class="text-center">Créditos</th>
                                                            <th width="100" class="text-center" style="border-top-right-radius: 8px;">Nivel</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($availableSubjects as $subject)
                                                        <tr style="border-bottom: 1px solid var(--border-light);">
                                                            <td class="text-center">
                                                                <div class="form-check">
                                                                    <input type="checkbox" 
                                                                           name="subjects[]" 
                                                                           value="{{ $subject->subject_id }}"
                                                                           id="subject_{{ $subject->subject_id }}"
                                                                           {{ in_array($subject->subject_id, $plannedSubjects) ? 'checked' : '' }}
                                                                           class="form-check-input subject-check"
                                                                           style="width: 1.2em; height: 1.2em; margin-top: 0.2em;">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <label for="subject_{{ $subject->subject_id }}" class="mb-0 d-block">
                                                                    <div class="fw-bold" style="color: var(--primary-700);">{{ $subject->code }}</div>
                                                                    <div style="color: var(--text-primary);">{{ $subject->name }}</div>
                                                                    @if($subject->has_prerequisites)
                                                                    <div class="mt-1">
                                                                        <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">
                                                                            <i class="fas fa-exclamation-circle me-1"></i> Tiene prerrequisitos
                                                                        </span>
                                                                    </div>
                                                                    @endif
                                                                </label>
                                                            </td>
                                                            <td class="text-center" style="color: var(--primary-600); font-weight: 500;">{{ $subject->credits }}</td>
                                                            <td class="text-center">
                                                                <span class="badge" style="background-color: var(--primary-100); color: var(--primary-700);">
                                                                    {{ $subject->level }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Asignaturas planificadas -->
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header py-3" style="background-color: var(--success); color: white; border-radius: 8px 8px 0 0 !important;">
                                        <h5 class="mb-0">
                                            <i class="fas fa-check-circle me-2"></i> Mi Planificación
                                        </h5>
                                    </div>
                                    <div class="card-body" style="background-color: white; border-radius: 0 0 8px 8px;">
                                        @if(empty($plannedSubjects))
                                            <div class="alert alert-info" style="border-radius: 8px;">
                                                <i class="fas fa-info-circle me-2"></i> No has seleccionado asignaturas para este período.
                                            </div>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table align-middle mb-0">
                                                    <thead>
                                                        <tr style="background-color: var(--success-light); color: var(--success-dark);">
                                                            <th style="border-top-left-radius: 8px;">Asignatura</th>
                                                            <th class="text-center">Créditos</th>
                                                            <th class="text-center">Nivel</th>
                                                            <th class="text-center" style="border-top-right-radius: 8px;">Acción</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="plannedSubjectsList">
                                                        @foreach($availableSubjects->whereIn('subject_id', $plannedSubjects) as $subject)
                                                        <tr id="planned_{{ $subject->subject_id }}" style="border-bottom: 1px solid var(--border-light);">
                                                            <td>
                                                                <div class="fw-bold" style="color: var(--success-dark);">{{ $subject->code }}</div>
                                                                <div style="color: var(--text-primary);">{{ $subject->name }}</div>
                                                            </td>
                                                            <td class="text-center" style="color: var(--primary-800); font-weight: 500;">{{ $subject->credits }}</td>
                                                            <td class="text-center">
                                                                <span class="badge" style="background-color: var(--success-light); color: var(--success-dark);">
                                                                    {{ $subject->level }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm remove-subject" 
                                                                        data-subject="{{ $subject->subject_id }}"
                                                                        style="background-color: var(--danger-light); color: var(--danger); border: none; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                                                    <i class="fas fa-times me-1"></i> Quitar
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr style="background-color: var(--primary-50);">
                                                            <td class="text-end fw-bold" style="color: var(--primary-800);">Total créditos:</td>
                                                            <td class="text-center fw-bold" style="color: var(--primary-700);">{{ $plannedCredits }}</td>
                                                            <td colspan="2"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <div class="form-text" style="color: var(--text-muted);">
                                <i class="fas fa-lightbulb me-1" style="color: var(--primary-500);"></i> Selecciona las asignaturas que deseas planificar para este período.
                            </div>
                            <button type="submit" class="btn" id="submitBtn"
                                    style="background-color: var(--primary-500); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px;">
                                <i class="fas fa-save me-2"></i> Guardar Planificación
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar selección/deselección de asignaturas
    const checkboxes = document.querySelectorAll('.subject-check');
    const plannedList = document.getElementById('plannedSubjectsList');
    const form = document.getElementById('planningForm');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const subjectId = this.value;
            const isChecked = this.checked;
            
            if (isChecked) {
                // Agregar a la lista de planificadas
                const subjectRow = this.closest('tr');
                const subjectCode = subjectRow.querySelector('.fw-bold').textContent;
                const subjectName = subjectRow.querySelector('td:nth-child(2) div:nth-child(2)').textContent;
                const credits = subjectRow.querySelector('td:nth-child(3)').textContent;
                const level = subjectRow.querySelector('td:nth-child(4) .badge').textContent;
                const hasPrereq = subjectRow.querySelector('.badge.bg-warning') ? true : false;
                
                const newRow = document.createElement('tr');
                newRow.id = `planned_${subjectId}`;
                newRow.style.borderBottom = '1px solid var(--border-light)';
                newRow.innerHTML = `
                    <td>
                        <div class="fw-bold" style="color: var(--success-dark);">${subjectCode}</div>
                        <div style="color: var(--text-primary);">${subjectName}</div>
                        ${hasPrereq ? '<div class="mt-1"><span class="badge bg-warning text-dark" style="font-size: 0.7rem;"><i class="fas fa-exclamation-circle me-1"></i> Requiere prerrequisitos</span></div>' : ''}
                    </td>
                    <td class="text-center" style="color: var(--success-dark); font-weight: 500;">${credits}</td>
                    <td class="text-center">
                        <span class="badge" style="background-color: var(--success-light); color: var(--success-dark);">
                            ${level}
                        </span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm remove-subject" 
                                data-subject="${subjectId}"
                                style="background-color: var(--danger-light); color: var(--danger); border: none; padding: 0.25rem 0.5rem; border-radius: 4px;">
                            <i class="fas fa-times me-1"></i> Quitar
                        </button>
                    </td>
                `;
                
                plannedList.appendChild(newRow);
            } else {
                // Remover de la lista de planificadas
                const rowToRemove = document.getElementById(`planned_${subjectId}`);
                if (rowToRemove) {
                    rowToRemove.remove();
                }
            }
            
            updateCreditsTotal();
        });
    });
    
    // Delegación de eventos para botones de quitar
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-subject') || 
            e.target.closest('.remove-subject')) {
            const button = e.target.classList.contains('remove-subject') ? 
                          e.target : e.target.closest('.remove-subject');
            const subjectId = button.dataset.subject;
            
            // Desmarcar el checkbox correspondiente
            const checkbox = document.getElementById(`subject_${subjectId}`);
            if (checkbox) {
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
            }
        }
    });
    
    // Actualizar total de créditos
    function updateCreditsTotal() {
        let total = 0;
        document.querySelectorAll('#plannedSubjectsList tr').forEach(row => {
            const credits = parseInt(row.querySelector('td:nth-child(2)').textContent);
            total += credits;
        });
        
        document.querySelector('tfoot td:nth-child(2)').textContent = total;
    }
    
    // Manejar envío del formulario
    form.addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';
    });
});
</script>

<style>
    .subject-check {
        cursor: pointer;
    }
    
    .subject-check:checked {
        background-color: var(--success);
        border-color: var(--success);
    }
    
    tr:hover {
        background-color: var(--primary-50) !important;
        transition: background-color 0.2s ease;
    }
    
    .remove-subject:hover {
        background-color: var(--danger) !important;
        color: white !important;
    }
    
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush