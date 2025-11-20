<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InformeController extends Controller
{   
    /**
     * Muestra la vista de informes.
     *
     * @return \Illuminate\View\View
    */
    public function index()
    {
        return view('informes.index');
    }
}
