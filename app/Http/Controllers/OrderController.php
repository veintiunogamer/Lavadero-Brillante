<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;

class OrderController extends Controller
{   
    /**
     * Muestra la vista principal con el código de orden generado.
     *
     * @return \Illuminate\View\View
    */
    public function index()
    {
        $consecutive = $this->getConsecutive();

        return view('index', ['consecutive' => $consecutive]);
    }

    /**
     * Genera el código de fecha y la secuencia diaria para las órdenes. 
     * diamesaño - 20/11/2025 = 20112025
     * 000 - Primer orden del día
     * @return array
    */
    public function getConsecutive(): array
    {
        $today = Carbon::today();

        $dateCode = $today->format('dmY');
        $todayOrders = Order::whereDate('creation_date', $today)->count() + 1;
        $sequence = str_pad((string) $todayOrders, 3, '0', STR_PAD_LEFT);

        return [
            'date_code' => $dateCode,
            'sequence' => $sequence,
        ];
    }

    
    // Métodos para crear, editar, eliminar órdenes se pueden agregar aquí
}
