<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServicioController extends Controller
{   
    /**
     * Muestra la lista de servicios.
     *
     * @return \Illuminate\View\View
    */
    public function index()
    {
        return view('servicios.index');
    }
}
