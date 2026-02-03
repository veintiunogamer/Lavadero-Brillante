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
        $users = \App\Models\User::where('status', 1)->orderBy('name')->get();
        
        return view('index', [
            'consecutive' => $consecutive,
            'categories' => $categories,
            'vehicleTypes' => $vehicleTypes,
            'users' => $users
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

    /**
     * Obtiene los servicios filtrados por categoría
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @param  int  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServicesByCategory($categoryId)
    {
        $services = \App\Models\Service::where('category_id', $categoryId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'value']);

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Almacena una nueva orden
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(\Illuminate\Http\Request $request)
    {
        try {

            \DB::beginTransaction();

            // Validar datos básicos
            $validated = $request->validate([
                'client_name' => 'required|string|max:100',
                'client_phone' => 'required|string|max:20',
                'license_plaque' => 'required|string|max:10',
                'assigned_user' => 'required|uuid|exists:users,id',
                'vehicle_type_id' => 'required|uuid|exists:vehicle_type,id',
                'dirt_level' => 'required|integer|min:1|max:3',
                'vehicle_notes' => 'nullable|string|max:250',
                'services' => 'required|array|min:1',
                'services.*.service_id' => 'required|uuid|exists:services,id',
                'services.*.quantity' => 'required|integer|min:1',
                'services.*.price' => 'required|numeric|min:0',
                'order_notes' => 'nullable|string|max:250',
                'extra_notes' => 'nullable|string|max:250',
                'discount' => 'nullable|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'selected_date' => 'required|date',
                'hour_in' => 'required|date_format:H:i',
                'hour_out' => 'required|date_format:H:i|after:hour_in',
                'payment_status' => 'required|integer|in:1,2,3', // 1=Pendiente, 2=Parcial, 3=Pagado
                'partial_payment' => 'nullable|numeric|min:0',
                'payment_method' => 'required|integer|in:1,2,3', // 1='efectivo', 2='tarjeta', 3='transferencia'
                'order_status' => 'required|integer|in:1,2,3',
                'tax_id' => 'nullable|uuid|exists:taxes,id',
                // Datos de facturación (opcionales)
                'invoice_required' => 'nullable|boolean',
                'invoice_business_name' => 'required_if:invoice_required,true|nullable|string|max:200',
                'invoice_tax_id' => 'required_if:invoice_required,true|nullable|string|max:50',
                'invoice_email' => 'nullable|email|max:100',
                'invoice_address' => 'required_if:invoice_required,true|nullable|string|max:250',
                'invoice_postal_code' => 'required_if:invoice_required,true|nullable|string|max:10',
                'invoice_city' => 'required_if:invoice_required,true|nullable|string|max:100',
            ]);


            // 1. Buscar o crear cliente
            $client = \App\Models\Client::where('phone', $validated['client_phone'])->first();
            
            if (!$client) {

                $client = \App\Models\Client::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => $validated['client_name'],
                    'phone' => $validated['client_phone'],
                    'license_plaque' => $validated['license_plaque'],
                    'status' => \App\Models\Client::STATUS_ACTIVE,
                    'creation_date' => now(),
                ]);

            }

            $orderId = \Illuminate\Support\Str::uuid();
            
            // Combinar fecha con horas
            $hourIn = Carbon::parse($validated['selected_date'])->setTimeFromTimeString($validated['hour_in']);
            $hourOut = Carbon::parse($validated['selected_date'])->setTimeFromTimeString($validated['hour_out']);

            // 2. Crear orden
            $order = Order::create([
                'id' => $orderId,
                'client_id' => $client->id,
                'user_id' => $validated['assigned_user'],
                'dirt_level' => $validated['dirt_level'],
                'date' => $validated['selected_date'],
                'hour_in' => $hourIn,
                'hour_out' => $hourOut,
                'quantity' => $validated['quantity'] ?? 1,
                'vehicle_type_id' => $validated['vehicle_type_id'],
                'vehicle_notes' => $validated['vehicle_notes'] ?? '',
                'discount' => $validated['discount'] ?? 0,
                'subtotal' => $validated['subtotal'],
                'taxes' => $validated['tax_id'] ?? null,
                'total' => $validated['total'],
                'partial_payment' => $validated['partial_payment'] ?? null,
                'order_notes' => $validated['order_notes'] ?? '',
                'extra_notes' => $validated['extra_notes'] ?? '',
                'status' => $validated['order_status'],
                'creation_date' => now(),
            ]);

            // 3. Crear pago asociado
            \App\Models\Payment::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'order_id' => $order->id,
                'type' => $validated['payment_method'],
                'subtotal' => $validated['subtotal'],
                'total' => $validated['total'],
                'status' => $validated['payment_status'],
                'creation_date' => now(),
            ]);

            // 4. Asociar servicios a la orden
            foreach ($validated['services'] as $service) {

                \App\Models\OrderService::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'order_id' => $orderId,
                    'service_id' => $service['service_id'],
                    'quantity' => $service['quantity'],
                    'subtotal' => 0,
                    'total' => $service['price'],
                ]);
                
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden creada exitosamente',
                'data' => [
                    'order' => $order,
                    'client' => $client,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            \DB::rollBack();

            \Log::error('Error al crear orden: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden: ' . $e->getMessage()
            ], 500);

        }
    }

    /**
     * Obtiene órdenes para los tabs (Pendientes vs Historial)
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @param  string  $tab
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersByTab($tab)
    {

        try {

            if ($tab === 'pending') {

                // Tab 1: Solo pendientes
                $orders = Order::with(['client', 'services', 'user'])
                ->where('status', Order::STATUS_PENDING)
                ->orderBy('creation_date', 'desc')
                ->get();

            } else {

                // Tab 2: Historial completo (En Proceso, Terminadas, Canceladas)
                $orders = Order::with(['client', 'services', 'user'])
                ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_IN_PROGRESS, Order::STATUS_COMPLETED, Order::STATUS_CANCELED])
                ->orderBy('creation_date', 'desc')
                ->get();

            }

            return response()->json([
                'success' => true,
                'data' => $orders,
                'count' => $orders->count()
            ]);

        } catch (\Exception $e) {

            \Log::error('Error al obtener órdenes por tab: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las órdenes'
            ], 500);

        }
    }
}
