<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;


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
     * Muestra la vista de agendamientos (redirige al index principal)
     *
     * @author Jose Alzate <josealzate97@gmail.com>
     * @return \Illuminate\Http\RedirectResponse
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
        $orders = Order::with(['client', 'services'])
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
                'consecutive_serial' => 'nullable|string|max:9',
                'consecutive_number' => 'nullable|string|max:9',
                // Datos de facturación (opcionales)
                'invoice_required' => 'nullable|boolean',
                'invoice_business_name' => 'required_if:invoice_required,true|nullable|string|max:200',
                'invoice_tax_id' => 'required_if:invoice_required,true|nullable|string|max:50',
                'invoice_email' => 'nullable|email|max:100',
                'invoice_address' => 'required_if:invoice_required,true|nullable|string|max:250',
                'invoice_postal_code' => 'required_if:invoice_required,true|nullable|string|max:10',
                'invoice_city' => 'required_if:invoice_required,true|nullable|string|max:100',
            ]);


            // 1. Buscar o crear cliente (prioriza matrícula para evitar duplicados)
            $licensePlaque = strtoupper(trim($validated['license_plaque']));
            $clientPhone = trim($validated['client_phone']);

            $client = \App\Models\Client::whereRaw('UPPER(license_plaque) = ?', [$licensePlaque])->first();

            if (!$client && $clientPhone) {
                $client = \App\Models\Client::where('phone', $clientPhone)->first();
            }
            
            if (!$client) {

                $client = \App\Models\Client::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => $validated['client_name'],
                    'phone' => $clientPhone,
                    'license_plaque' => $licensePlaque,
                    'status' => \App\Models\Client::STATUS_ACTIVE,
                    'creation_date' => now(),
                ]);

            } else {

                $client->update([
                    'name' => $validated['client_name'],
                    'phone' => $clientPhone,
                    'license_plaque' => $licensePlaque,
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
                'consecutive_serial' => $validated['consecutive_serial'],
                'consecutive_number' => $validated['consecutive_number'],
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
                    'created_at' => now(),
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
     * Muestra el formulario de edición de una orden (usa la misma vista principal)
     *
     * @param Order $order
     * @return \Illuminate\View\View
    */
    public function edit(Order $order)
    {
        // Cargar relaciones necesarias
        $order->load(['client', 'services', 'user', 'payments']);
        
        $consecutive = $this->getConsecutive();
        $categories = \App\Models\Category::where('status', 1)->orderBy('cat_name')->get();
        $vehicleTypes = \App\Models\VehicleType::orderBy('name')->get();
        $users = \App\Models\User::where('status', 1)->orderBy('name')->get();
        
        // Usa la misma vista principal pero con la orden para editar
        return view('index', [
            'consecutive' => $consecutive,
            'categories' => $categories,
            'vehicleTypes' => $vehicleTypes,
            'users' => $users,
            'editOrder' => $order  // La orden a editar
        ]);
    }

     /**
     * Actualiza una orden existente
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Order $order)
    {
        try {

            $validated = $request->validate([
                'client_name' => 'required|string|max:100',
                'client_phone' => 'nullable|string|max:20',
                'license_plaque' => 'required|string|max:15',
                'assigned_user' => 'nullable|uuid',
                'vehicle_type_id' => 'required|uuid|exists:vehicle_types,id',
                'dirt_level' => 'required|integer|in:1,2,3',
                'services' => 'required|array|min:1',
                'services.*.service_id' => 'required|uuid|exists:services,id',
                'services.*.quantity' => 'required|integer|min:1',
                'services.*.price' => 'required|numeric|min:0',
                'vehicle_notes' => 'nullable|string|max:250',
                'order_notes' => 'nullable|string|max:250',
                'extra_notes' => 'nullable|string|max:250',
                'discount' => 'nullable|numeric|min:0|max:100',
                'subtotal' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'selected_date' => 'required|date',
                'hour_in' => 'required|string',
                'hour_out' => 'required|string',
                'payment_status' => 'required|integer|in:1,2,3',
                'partial_payment' => 'nullable|numeric|min:0',
                'payment_method' => 'required|integer|in:1,2,3,4',
                'order_status' => 'required|integer|in:1,2,3,4',
                'invoice_required' => 'boolean',
            ]);

            \DB::beginTransaction();

            // Actualizar cliente
            $client = $order->client;
            $client->update([
                'name' => $validated['client_name'],
                'phone' => $validated['client_phone'],
                'license_plaque' => strtoupper($validated['license_plaque']),
            ]);

            // Preparar fechas y horas
            $selectedDate = Carbon::parse($validated['selected_date']);
            $hourIn = Carbon::parse($validated['selected_date'] . ' ' . $validated['hour_in']);
            $hourOut = Carbon::parse($validated['selected_date'] . ' ' . $validated['hour_out']);

            // Actualizar orden
            $order->update([
                'assigned_user' => $validated['assigned_user'],
                'vehicle_type_id' => $validated['vehicle_type_id'],
                'dirt_level' => $validated['dirt_level'],
                'vehicle_notes' => $validated['vehicle_notes'] ?? '',
                'order_notes' => $validated['order_notes'] ?? '',
                'extra_notes' => $validated['extra_notes'] ?? '',
                'discount' => $validated['discount'] ?? 0,
                'subtotal' => $validated['subtotal'],
                'total' => $validated['total'],
                'hour_in' => $hourIn,
                'hour_out' => $hourOut,
                'payment_status' => $validated['payment_status'],
                'partial_payment' => $validated['partial_payment'],
                'payment_method' => $validated['payment_method'],
                'status' => $validated['order_status'],
                'invoice_required' => $validated['invoice_required'] ?? false,
                'invoice_business_name' => $request->input('invoice_business_name'),
                'invoice_tax_id' => $request->input('invoice_tax_id'),
                'invoice_email' => $request->input('invoice_email'),
                'invoice_address' => $request->input('invoice_address'),
                'invoice_postal_code' => $request->input('invoice_postal_code'),
                'invoice_city' => $request->input('invoice_city'),
                'consecutive_serial' => $request->input('consecutive_serial'),
                'consecutive_number' => $request->input('consecutive_number'),
            ]);

            // Eliminar servicios anteriores
            OrderService::where('order_id', $order->id)->delete();

            // Crear nuevos servicios
            foreach ($validated['services'] as $serviceData) {
                OrderService::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'order_id' => $order->id,
                    'service_id' => $serviceData['service_id'],
                    'quantity' => $serviceData['quantity'],
                    'total' => $serviceData['price'],
                ]);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizada exitosamente',
                'data' => [
                    'order' => $order->fresh(['client', 'services']),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            \DB::rollBack();

            \Log::error('Error al actualizar orden: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la orden: ' . $e->getMessage()
            ], 500);

        }
    }

    /**
     * Actualiza solo el estado de una orden (acción rápida)
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        try {

            $validated = $request->validate([
                'status' => 'required|integer|in:1,2,3,4',
                'note' => 'nullable|string|max:250',
            ]);

            $oldStatus = $order->status;
            
            $order->update([
                'status' => $validated['status'],
            ]);

            // Si hay nota de cancelación, agregarla a extra_notes
            if ($validated['status'] == 4 && !empty($validated['note'])) {
                $existingNotes = $order->extra_notes;
                $cancelNote = '[CANCELADO] ' . $validated['note'];
                $order->update([
                    'extra_notes' => $existingNotes ? $existingNotes . "\n" . $cancelNote : $cancelNote
                ]);
            }

            \Log::info("Orden {$order->id} cambió de estado {$oldStatus} a {$validated['status']}");

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'data' => [
                    'order' => $order->fresh(['client', 'services']),
                    'old_status' => $oldStatus,
                    'new_status' => $validated['status']
                ]
            ]);

        } catch (\Exception $e) {

            \Log::error('Error al actualizar estado de orden: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ], 500);

        }
    }

    /**
     * Obtiene los detalles de una orden específica
     *
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        try {

            $order->load(['client', 'services.category', 'user', 'payments']);

            return response()->json([
                'success' => true,
                'data' => $order
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la orden'
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
                $orders = Order::with(['client', 'services', 'user', 'payments'])
                ->where('status', Order::STATUS_PENDING)
                ->orderBy('creation_date', 'desc')
                ->get();

            } else {

                // Tab 2: Historial completo (En Proceso, Terminadas, Canceladas)
                $orders = Order::with(['client', 'services', 'user', 'payments'])
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
