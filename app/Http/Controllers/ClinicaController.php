<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClinicaController extends Controller
{
    // Método para devolver la vista index
    public function index()
    {
        return view('index');
    }

    // Método para devolver la vista contacto
    public function contact()
    {
        return view('contact');
    }
}