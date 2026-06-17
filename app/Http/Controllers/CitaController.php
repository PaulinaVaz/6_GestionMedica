<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CitaController extends Controller
{
    public function index()
    {
        $rol = Auth::user()->rol;

        if ($rol == 'Paciente') {
            // Verificamos primero si existe la relación con paciente para evitar el error "on null"
            $paciente = Auth::user()->paciente;

            if (!$paciente) {
                // Si el perfil no existe, enviamos una lista vacía y el mensaje de error a la vista
                return view('citas.index', [
                    'citas' => collect(), 
                    'error' => 'Tu perfil de paciente no ha sido completado. Por favor, contacta a la administración.'
                ]);
            }

            $idPacienteReal = $paciente->id_paciente;
            $citas = Cita::where('id_paciente', $idPacienteReal)->get();
        } else {
            // Los administradores y especialistas visualizan todos los registros
            $citas = Cita::with(['paciente', 'especialista.usuario'])->get();
        }

        return view('citas.index', compact('citas'));
    }

    public function create()
    {
        // Cargamos la lista de médicos para el formulario de agendamiento
        $especialistas = Especialista::with('usuario')->get();
        return view('citas.create', compact('especialistas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_especialista' => 'required',
            'fecha' => 'required|date',
            'hora' => 'required',
            'motivo' => 'required|string|max:255',
        ]);

        // Validación de seguridad: verificamos que el usuario tenga un registro en la tabla de pacientes
        $paciente = Auth::user()->paciente;

        if (!$paciente) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'No se puede agendar la cita porque no cuentas con un perfil de paciente asociado.']);
        }

        $horaFormateada = date('H:i:00', strtotime($request->hora));

        // Verificamos si el horario ya está ocupado por el mismo especialista
        $existeCita = Cita::where('id_especialista', $request->id_especialista)
            ->where('fecha', $request->fecha)
            ->where('hora', $horaFormateada)
            ->where('estado', '!=', 'Cancelada')
            ->exists();
        
        if ($existeCita) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ya existe una cita para ese especialista en la fecha y hora seleccionadas. Por favor, elige otro horario.']);
        }
        
        // Creación de la cita utilizando el ID verificado del paciente
        $cita = Cita::create([
            'id_paciente' => $paciente->id_paciente,
            'id_especialista' => $request->id_especialista,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'estado' => 'Pendiente',
            'motivo' => $request->motivo,
        ]);

        // Envío de notificación por correo electrónico al paciente
        Mail::raw("Hola " . Auth::user()->nombre . ", tu cita ha sido programada exitosamente para el día {$cita->fecha} a las {$cita->hora}.", function ($message) {
            $message->to(Auth::user()->correo)
                    ->subject('Confirmación de Cita Médica - Sabi Núcleo Médico');
        });

        return redirect()->route('citas.index')->with('success', 'Tu cita ha sido agendada y se ha enviado un correo de conﬁrmación.');
    }

    public function edit($id)
    {
        $cita = Cita::findOrFail($id);
        $especialistas = Especialista::with('usuario')->get();
        return view('citas.edit', compact('cita', 'especialistas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_especialista' => 'required',
            'fecha' => 'required|date',
            'hora' => 'required',
            'motivo' => 'required|string|max:255',
        ]);

        $horaFormateada = date('H:i:00', strtotime($request->hora));

        // Verificamos choques de horario ignorando la cita que estamos editando
        $existeCita = Cita::where('id_especialista', $request->id_especialista)
            ->where('fecha', $request->fecha)
            ->where('hora', $horaFormateada)
            ->where('estado', '!=', 'Cancelada')
            ->where('id_cita', '!=', $id) 
            ->exists();

        if ($existeCita) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'No se puede modificar: ese horario ya está ocupado por otra cita activa.']);
        }

        $cita = Cita::findOrFail($id);
        $cita->update([
            'id_especialista' => $request->id_especialista,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'motivo' => $request->motivo,
        ]);

        return redirect()->route('citas.index')->with('success', 'La cita se ha modificado correctamente.');
    }

    public function completar($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->update(['estado' => 'Completada']);
        return redirect()->route('citas.index')->with('success', 'La consulta se ha marcado como completada.');
    }

    public function cancelar($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->update(['estado' => 'Cancelada']);
        return redirect()->route('citas.index')->with('success', 'La cita se ha cancelado correctamente.');
    }
}