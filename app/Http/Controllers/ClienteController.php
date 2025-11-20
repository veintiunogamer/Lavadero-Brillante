<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClienteController extends Controller
{   
    /**
     * Muestra la lista de clientes.
     *
     * @return \Illuminate\View\View
    */
    public function index()
    {
        return view('clientes.index');
    }
}
