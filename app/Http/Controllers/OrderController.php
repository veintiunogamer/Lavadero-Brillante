<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;

class OrderController extends Controller
{   
    /**
     * Muestra la vista principal con el código de orden generado.
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
    */
    public function index()
    {
        $consecutive = $this->getConsecutive();
        $categories = \App\Models\Category::where('status', 1)->orderBy('cat_name')->get();
        $vehicleTypes = \App\Models\VehicleType::orderBy('name')->get();
        
        return view('index', [
            'consecutive' => $consecutive,
            'categories' => $categories,
            'vehicleTypes' => $vehicleTypes
        ]);
    }

    /**
     * Genera el código de fecha y la secuencia diaria para las órdenes.
     * Formato: diamesaño - 20/11/2025 = 20112025
     * Secuencia: 000 - Primer orden del día
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return array
    */
    public function getConsecutive(): array
    {
        $today = Carbon::today();
        $dateCode = $today->format('dmY');
        
        $todayOrders = Order::whereDate('creation_date', $today)->count();
        $sequence = str_pad((string) ($todayOrders + 1), 3, '0', STR_PAD_LEFT);

        return [
            'date_code' => $dateCode,
            'sequence' => $sequence,
        ];
    }

    /**
     * Muestra la vista de agendamientos
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\View\View
     */
    public function agendamiento()
    {
        return view('orders.index');
    }

    /**
     * Obtiene los agendamientos por estado
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByStatus($status)
    {
        $orders = Order::with(['client', 'service'])
            ->where('status', $status)
            ->orderBy('creation_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'count' => $orders->count()
        ]);
    }

    
    // Métodos para crear, editar, eliminar órdenes se pueden agregar aquí
}
