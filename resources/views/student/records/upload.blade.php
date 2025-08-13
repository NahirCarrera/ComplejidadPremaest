@extends('layouts.app')

@section('title', 'Subir Record Académico')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Formulario de subida -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="card-header py-3" style="background-color: var(--primary-500);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="h5 mb-0" style="color: var(--text-light); font-weight: 600;>
                            <i class="fas fa-file-upload me-2"></i> Subir Record Académico
                        </h4>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger" style="border-radius: 8px;">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.records.process') }}" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                       <div class="mb-4">
                            <label for="academic_record" class="form-label fw-bold mb-3" style="color: var(--text-light);">
                                <i class="fas fa-file-pdf me-2" style="color: var(--primary-300);"></i> Archivo PDF *
                            </label>
                            
                            <div class="file-upload-card" 
                                id="dropzone"
                                style="border: 2px dashed var(--primary-300); 
                                        border-radius: 12px;
                                        background-color: var(--primary-50);
                                        padding: 2rem;
                                        text-align: center;
                                        cursor: pointer;
                                        transition: all 0.3s ease;"
                                onclick="document.getElementById('academic_record').click()">
                                
                                <input type="file" 
                                    class="d-none @error('academic_record') is-invalid @enderror" 
                                    id="academic_record" 
                                    name="academic_record" 
                                    accept=".pdf" 
                                    required
                                    onchange="updateFileName(this)">
                                
                                <div class="upload-icon mb-3" style="font-size: 2.5rem; color: var(--primary-400);">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                
                                <h5 class="mb-2" style="color: var(--primary-600); font-weight: 600;">
                                    Arrastra tu archivo aquí o haz clic
                                </h5>
                                
                                <p class="mb-0 small" style="color: var(--text-muted);">
                                    Solo archivos PDF (tamaño máximo: 2MB)
                                </p>
                                
                                <div id="file-name-display" class="mt-3 small" style="color: var(--primary-700); font-weight: 500;">
                                    Ningún archivo seleccionado
                                </div>
                            </div>
                            
                            @error('academic_record')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>



                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary btn-lg py-3" id="submitBtn"
                                    style="background-color: var(--primary-500); border: none; border-radius: 8px;">
                                <i class="fas fa-upload me-2"></i> Procesar Record
                            </button>
                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary py-3"
                               style="border-radius: 8px;">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de instrucciones -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100" style="border-radius: 10px; border-left: 4px solid var(--primary-500);">
                <div class="card-header py-3" style="background-color: var(--primary-100);">
                    <h5 class="mb-0" style="color: var(--primary-700);">
                        <i class="fas fa-info-circle me-2"></i> Instrucciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="instruction-step mb-4">
                        <div class="step-number d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                             style="width: 28px; height: 28px; background-color: var(--primary-500); color: white; font-weight: bold;">
                            1
                        </div>
                        <h6 style="color: var(--text-light);">Descargar tu record</h6>
                        <p class="text-muted small" style="color: var(--text-light);">
                            Accede al sistema académico de tu universidad y descarga tu record en formato PDF.
                        </p>
                    </div>

                    <div class="instruction-step mb-4">
                        <div class="step-number d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                             style="width: 28px; height: 28px; background-color: var(--primary-500); color: white; font-weight: bold;">
                            2
                        </div>
                        <h6 style="color: var(--text-light);">Verifica la información</h6>
                        <p class="text-muted small" style="color: var(--text-light);">
                            Asegúrate que el documento contenga todas tus asignaturas aprobadas y las notas finales.
                        </p>
                    </div>

                    <div class="instruction-step mb-4">
                        <div class="step-number d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                             style="width: 28px; height: 28px; background-color: var(--primary-500); color: white; font-weight: bold;">
                            3
                        </div>
                        <h6 style="color: var(--text-light);">Sube el archivo</h6>
                        <p class="text-muted small" style="color: var(--text-light);">
                            Selecciona el archivo PDF desde tu dispositivo y haz clic en "Procesar Record".
                        </p>
                    </div>

                    <div class="instruction-step">
                        <div class="step-number d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                             style="width: 28px; height: 28px; background-color: var(--primary-500); color: white; font-weight: bold;">
                            4
                        </div>
                        <h6 style="color: var(--text-light);">Espera confirmación</h6>
                        <p class="text-muted small" style="color: var(--text-light);">
                            El sistema procesará tu información y te notificará cuando esté listo.
                        </p>
                    </div>

                    <hr class="my-4" style="border-color: var(--border-light);">

                    <div class="alert alert-warning p-3" style="background-color: var(--primary-100); border-color: var(--primary-300); border-radius: 8px;">
                        <h6 class="alert-heading" style="color: var(--primary-700);">
                            <i class="fas fa-exclamation-triangle me-2"></i> Importante
                        </h6>
                        <p class="small mb-0" style="color: var(--text-secondary);">
                            Solo se aceptan archivos PDF oficiales generados por el sistema académico de la universidad.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';
        });
        
        // Validación de tamaño de archivo
        document.getElementById('academic_record').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size > 2 * 1024 * 1024) { // 2MB
                alert('El archivo excede el tamaño máximo permitido de 2MB');
                e.target.value = '';
            }
        });
    });
</script>


<script>
// Función para mostrar el nombre del archivo
function updateFileName(input) {
    const fileNameDisplay = document.getElementById('file-name-display');
    if (input.files.length > 0) {
        fileNameDisplay.textContent = input.files[0].name;
        fileNameDisplay.innerHTML += ' <i class="fas fa-check-circle" style="color: var(--success);"></i>';
        
        // Validar tamaño
        if (input.files[0].size > 2 * 1024 * 1024) {
            fileNameDisplay.innerHTML = '<span style="color: var(--danger);">El archivo excede 2MB</span>';
            input.value = '';
        }
    } else {
        fileNameDisplay.textContent = 'Ningún archivo seleccionado';
    }
}

// Drag and Drop functionality
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('academic_record');

// Evitar comportamientos por defecto
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Efectos al arrastrar
['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    dropzone.style.borderColor = 'var(--primary-500)';
    dropzone.style.backgroundColor = 'var(--primary-100)';
}

function unhighlight() {
    dropzone.style.borderColor = 'var(--primary-300)';
    dropzone.style.backgroundColor = 'var(--primary-50)';
}

// Manejar archivos soltados
dropzone.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0) {
        fileInput.files = files;
        updateFileName(fileInput);
    }
}
</script>
@endpush
@endsection