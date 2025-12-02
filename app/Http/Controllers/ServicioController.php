<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServicioController extends Controller
{   
    /**
     * Muestra la lista de servicios.
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
    */
    public function index()
    {
        return view('servicios.index');
    }
}
