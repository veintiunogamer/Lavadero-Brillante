<?php

namespace Tests\Feature\Concerns;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderService;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\VehicleType;
use App\Services\OrderReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Crea los datos mínimos (roles, usuarios, clientes, vehículos, servicios,
 * órdenes y pagos) para probar las exportaciones del informe de ventas.
 */
trait BuildsOrderReportData
{
    /** Etiquetas de negocio — idénticas a las de ReportController. */
    protected const STATUS_LABELS = [
        1 => 'Pendiente',
        2 => 'En Proceso',
        3 => 'Terminado',
        4 => 'Cancelado',
    ];

    protected const PAYMENT_STATUS_LABELS = [
        1 => 'Pendiente',
        2 => 'Parcial',
        3 => 'Pagado',
    ];

    protected const PAYMENT_METHOD_LABELS = [
        1 => 'Efectivo',
        2 => 'TPV',
        3 => 'Transferencia',
        4 => 'Otro',
    ];

    /** @var array<int, string> IDs de roles creados por tipo */
    private array $roleIds = [];

    protected function reportService(): OrderReportService
    {
        return new OrderReportService(
            self::STATUS_LABELS,
            self::PAYMENT_STATUS_LABELS,
            self::PAYMENT_METHOD_LABELS,
        );
    }

    /** Crea (una sola vez por tipo) el ID de un rol; la tabla roles no usa timestamps. */
    protected function roleId(int $type = 1): string
    {
        if (!isset($this->roleIds[$type])) {
            $id = (string) Str::uuid();

            DB::table('roles')->insert([
                'id' => $id,
                'name' => $type === 1 ? 'Administrador' : ('Rol ' . $type),
                'type' => $type,
                'status' => 1,
                'creation_date' => now(),
            ]);

            $this->roleIds[$type] = $id;
        }

        return $this->roleIds[$type];
    }

    protected function makeUser(string $name, int $roleType = 1): User
    {
        return User::forceCreate([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'email' => Str::lower(Str::slug($name)) . '-' . uniqid() . '@test.local',
            'username' => Str::lower(Str::slug($name)) . uniqid(),
            'password' => 'secret-password',
            'rol' => $this->roleId($roleType),
            'status' => 1,
            'creation_date' => now(),
        ]);
    }

    protected function makeClient(array $attributes = []): Client
    {
        return Client::create(array_merge([
            'id' => (string) Str::uuid(),
            'name' => 'Cliente Demo',
            'phone' => '600111222',
            'license_plaque' => '1234ABC',
            'brand' => 'Toyota',
            'fleet' => false,
            'status' => 1,
            'creation_date' => now(),
        ], $attributes));
    }

    protected function makeVehicleType(string $name = 'Turismo'): VehicleType
    {
        return VehicleType::create([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'status' => 1,
            'creation_date' => now(),
        ]);
    }

    protected function makeService(string $name, string $categoryName = 'Lavados'): Service
    {
        $category = Category::firstWhere('cat_name', $categoryName);

        if (!$category) {
            $category = Category::create([
                'id' => (string) Str::uuid(),
                'cat_name' => $categoryName,
                'status' => 1,
                'creation_date' => now(),
            ]);
        }

        return Service::create([
            'id' => (string) Str::uuid(),
            'category_id' => $category->id,
            'name' => $name,
            'details' => 'Detalle del servicio',
            'value' => 10,
            'duration' => 30,
            'status' => 1,
            'creation_date' => now(),
        ]);
    }

    protected function makeOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'id' => (string) Str::uuid(),
            'quantity' => 1,
            'dirt_level' => 1,
            'date' => now()->toDateString(),
            'vehicle_notes' => '',
            'order_notes' => '',
            'extra_notes' => '',
            'subtotal' => 0,
            'total' => 0,
            'status' => 1,
            'creation_date' => now(),
        ], $attributes));
    }

    /**
     * Asocia un servicio a la orden vía order_services, usando el mismo camino
     * que OrderController::store (OrderService::create). No se usa attach()
     * porque Laravel 12 habilita los timestamps del pivot al recibir
     * created_at e intentaría escribir una columna updated_at inexistente.
     */
    protected function attachServiceToOrder(Order $order, Service $service, float $total = 10.0): void
    {
        OrderService::create([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'service_id' => $service->id,
            'subtotal' => 0,
            'total' => $total,
            'created_at' => now(),
        ]);

        $order->unsetRelation('services');
    }

    protected function makePayment(Order $order, array $attributes = []): Payment
    {
        return Payment::create(array_merge([
            'id' => (string) Str::uuid(),
            'order_id' => $order->id,
            'type' => 1,
            'subtotal' => 0,
            'total' => 0,
            'status' => 1,
            'creation_date' => now(),
        ], $attributes));
    }

    /** Carga una orden con todas las relaciones que usa el informe. */
    protected function loadOrder(string $orderId): Order
    {
        return Order::with(['client', 'user', 'vehicleType', 'services', 'payments'])
            ->findOrFail($orderId);
    }

    /** Número de orden como lo calcula el sistema (consecutivo o UUID truncado). */
    protected function orderNumber(Order $order): string
    {
        return $order->consecutive_serial && $order->consecutive_number
            ? $order->consecutive_serial . '-' . $order->consecutive_number
            : strtoupper(substr($order->id, 0, 8));
    }
}