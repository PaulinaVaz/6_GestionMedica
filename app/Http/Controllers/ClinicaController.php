<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importante agregar esta fachada

class ClinicaController extends Controller
{
    // Método para manejar la raíz del sitio de Sabi
    public function index()
    {
        // Si el usuario ya está autenticado, lo mandamos al dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Si no está autenticado, lo mandamos directo al login
        return redirect()->route('login');
    }

    // Método para devolver la vista contacto
    public function contact()
    {
        return view('contact');
    }
}