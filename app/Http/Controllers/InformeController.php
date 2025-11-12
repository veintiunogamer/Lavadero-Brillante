<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InformeController extends Controller
{
    public function index()
    {
        return view('informes.index');
    }
}
