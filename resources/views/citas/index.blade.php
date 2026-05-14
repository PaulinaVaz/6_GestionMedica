@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        {{-- Título Dinámico según Rol --}}
        <h2 class="text-primary fw-bold">
            @if(auth()->user()->rol == 'Administrador')
                Registro Global de Citas
            @elseif(auth()->user()->rol == 'Especialista')
                Agenda de Consultas
            @else
                Mis Citas Médicas
            @endif
        </h2>

        {{-- Solo el paciente puede ver el botón de agendar --}}
        @if(auth()->user()->rol == 'Paciente')
            <a href="{{ route('citas.create') }}" class="btn btn-success fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Agendar Nueva Cita
            </a>
        @endif
    </div>

    {{-- Manejo de Errores de Perfil (Captura el error enviado desde el Controller) --}}
    @if(isset($error))
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $error }}
        </div>
    @endif

    {{-- Errores de validación o choques de horario --}}
    @if($errors->has('error'))
        <div class="alert alert-danger shadow-sm">
            {{ $errors->first('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success shadow-sm border-start border-4 border-success">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-info">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Historial de Solicitudes</h5>
            <span class="badge bg-light text-dark">{{ count($citas) }} registros</span>
        </div>
        <div class="card-body p-0"> {{-- p-0 para que la tabla llegue a los bordes --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4">Fecha y Hora</th>
                            {{-- Los Admin y Especialistas ven quién es el paciente --}}
                            @if(auth()->user()->rol != 'Paciente')
                                <th>Paciente</th>
                            @endif
                            <th>Especialista</th>
                            <th>Consultorio</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($citas as $c)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ date('d/m/Y', strtotime($c->fecha)) }}</div>
                                <div class="text-muted small"><i class="bi bi-clock me-1"></i>{{ date('H:i A', strtotime($c->hora)) }}</div>
                            </td>
                            
                            @if(auth()->user()->rol != 'Paciente')
                                <td class="fw-semibold">{{ $c->paciente->usuario->nombre ?? 'N/A' }}</td>
                            @endif

                            <td>{{ $c->especialista->usuario->nombre ?? 'Sin asignar' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $c->especialista->consultorio ?? 'N/A' }}</span></td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $c->motivo }}">
                                    {{ $c->motivo }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($c->estado) {
                                        'Pendiente' => 'bg-warning text-dark',
                                        'Completada' => 'bg-success',
                                        'Cancelada' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }} px-3 py-2">
                                    {{ strtoupper($c->estado) }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group shadow-sm">
                                    @if($c->estado == 'Pendiente')
                                        {{-- Editar --}}
                                        <a href="{{ route('citas.edit', $c->id_cita) }}" class="btn btn-sm btn-outline-primary" title="Modificar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        {{-- Completar: Solo para Especialista o Admin --}}
                                        @if(auth()->user()->rol != 'Paciente')
                                            <a href="{{ route('citas.completar', $c->id_cita) }}" class="btn btn-sm btn-success" title="Marcar como completada">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        @endif

                                        {{-- Cancelar --}}
                                        <form action="{{ route('citas.cancelar', $c->id_cita) }}" method="GET" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas cancelar esta cita?')" title="Cancelar">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small italic">Sin acciones</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar-x display-4"></i>
                                    <p class="mt-2">No se encontraron citas registradas.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4 text-center">
        <a href="{{ route('dashboard') }}" class="btn btn-link text-decoration-none text-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Panel Principal
        </a>
    </div>
</div>
@endsection