<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClienteController extends Controller
{   
    /**
     * Muestra la lista de clientes.
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
    */
    public function index()
    {
        return view('clientes.index');
    }
}
