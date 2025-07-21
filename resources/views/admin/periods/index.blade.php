@extends('layouts.app')

@section('title', 'Gestión de Períodos Académicos')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Períodos de Pre - Matrícula</h1>
        <a href="{{ route('admin.periods.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Período
        </a>
    </div>

    @include('partials.alerts')

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $period)
                        <tr>
                            <td>{{ $period->code }}</td>
                            <td>{{ $period->start_date->format('d/m/Y') }}</td>
                            <td>{{ $period->end_date->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.periods.edit', $period) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.periods.destroy', $period) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay períodos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $periods->links() }}
        </div>
    </div>
</div>
@endsection