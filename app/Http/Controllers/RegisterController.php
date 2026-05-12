<?php
namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function showRegistrationForm() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,correo',
            'password' => 'required|string|min:8|confirmed',
            'country_code' => 'required',
            'phone_prefix' => 'required',
            'telefono' => 'required|numeric',
            'idioma' => 'nullable|string|max:255',
            'zona_horaria' => 'required|string',
        ], [
            'email.unique' => 'Este correo ya está registrado en el sistema.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        try {
            DB::beginTransaction();

            // 1. Creamos el Usuario principal
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'correo' => $request->email, 
                'password' => Hash::make($request->password), 
                'country_code' => $request->country_code,
                'phone_prefix' => $request->phone_prefix,
                'telefono' => $request->telefono,
                'idioma' => $request->idioma,
                'zona_horaria' => $request->zona_horaria,
                'rol' => 'Paciente', 
            ]);

            // 2. Creamos el perfil de Paciente (Resolviendo errores de campos obligatorios)
            Paciente::create([
                'id_usuario' => $usuario->id_usuario, // Usamos la PK definida en tu modelo
                'fecha_registro' => now(),           // Resuelve error SQL 1364
                'historial' => 'Sin antecedentes registrados.', // Resuelve error SQL 1364
            ]);

            DB::commit();
            return redirect()->route('login')->with('success', '¡Registro exitoso! Ya puedes iniciar sesión.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Logueamos el error para debugging interno y avisamos al usuario
            Log::error("Error en registro: " . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Ocurrió un error técnico al crear tu perfil. Por favor, intenta más tarde.']);
        }
    }
}