<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Order;
class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        return view('orders.index', compact('orders'));
    }
    // Métodos para crear, editar, eliminar órdenes se pueden agregar aquí
}
