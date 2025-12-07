<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{   
    /**
     * Muestra la vista de informes.
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
    */
    public function index()
    {
        return view('reports.index');
    }
}
