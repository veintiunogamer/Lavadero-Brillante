<?php

namespace Tests\Feature;

use App\Services\OrderReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsOrderReportData;
use Tests\TestCase;

/**
 * Escenarios del mapeo de datos del informe de órdenes (17 campos).
 */
class OrderReportServiceTest extends TestCase
{
    use RefreshDatabase, BuildsOrderReportData;

    public function test_headings_contain_the_seventeen_required_fields_in_order(): void
    {
        $this->assertSame([
            '# Orden',
            'Fecha',
            'Cliente',
            'Teléfono',
            'Matrícula',
            'Categoría',
            'Flota',
            'Servicios',
            'Observaciones',
            'Nombre Lavad.',
            'Subtotal',
            'IVA',
            'Descuento ($)',
            'Pago',
            'Método',
            'Estado',
            'Total',
        ], OrderReportService::headings());
    }

    public function test_row_takes_each_field_from_its_real_relation(): void
    {
        $client = $this->makeClient([
            'name' => 'María Pérez',
            'phone' => '612345678',
            'license_plaque' => 'AB123CD',
            'fleet' => true,
        ]);
        $detailer = $this->makeUser('Juan Detallador');
        $vehicleType = $this->makeVehicleType('Turismo');
        $service = $this->makeService('Lavado Completo');

        $order = $this->makeOrder([
            'client_id' => $client->id,
            'user_id' => $detailer->id,
            'vehicle_type_id' => $vehicleType->id,
            'vehicle_notes' => 'Arañazo en la puerta derecha',
            'subtotal' => 100,
            'discount_value' => 10,
            'taxes_value' => 18.9,
            'total' => 108.9,
            'status' => 2,
            'consecutive_serial' => '17102026',
            'consecutive_number' => '005',
            'creation_date' => '2026-10-17 09:30:00',
        ]);
        $this->attachServiceToOrder($order, $service, 100.0);
        $this->makePayment($order, ['type' => 2, 'status' => 3, 'total' => 108.9]);

        $row = $this->reportService()->row($this->loadOrder($order->id));

        $this->assertSame([
            'orden', 'fecha', 'cliente', 'telefono', 'matricula', 'categoria',
            'flota', 'servicios', 'observaciones', 'nombre_lavad', 'subtotal',
            'iva', 'descuento', 'pago', 'metodo', 'estado', 'total',
        ], array_keys($row));

        $this->assertSame('17102026-005', $row['orden']);
        $this->assertSame('17/10/2026', $row['fecha']);
        $this->assertSame('María Pérez', $row['cliente']);
        $this->assertSame('612345678', $row['telefono']);
        $this->assertSame('AB123CD', $row['matricula']);
        $this->assertSame('Turismo', $row['categoria']);
        $this->assertSame('Sí', $row['flota']);
        $this->assertSame('Lavado Completo', $row['servicios']);
        $this->assertSame('Arañazo en la puerta derecha', $row['observaciones']);
        $this->assertSame('Juan Detallador', $row['nombre_lavad']);
        $this->assertSame(100.0, $row['subtotal']);
        $this->assertSame(18.9, $row['iva']);
        $this->assertSame(10.0, $row['descuento']);
        $this->assertSame('Pagado', $row['pago']);
        $this->assertSame('TPV', $row['metodo']);
        $this->assertSame('En Proceso', $row['estado']);
        $this->assertSame(108.9, $row['total']);
    }

    public function test_row_keeps_all_services_when_there_are_several(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder(['client_id' => $client->id]);

        $this->attachServiceToOrder($order, $this->makeService('Lavado Exterior'), 25.0);
        $this->attachServiceToOrder($order, $this->makeService('Encerado Rápido'), 15.0);
        $this->attachServiceToOrder($order, $this->makeService('Aspirado', 'Extras'), 8.0);

        $row = $this->reportService()->row($this->loadOrder($order->id));

        $this->assertSame('Lavado Exterior, Encerado Rápido, Aspirado', $row['servicios']);
        // Una orden con varios servicios sigue generando una única fila.
        $this->assertCount(1, [$row]);
    }

    public function test_row_shows_placeholder_when_there_are_no_observations(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder(['client_id' => $client->id, 'vehicle_notes' => '']);

        $row = $this->reportService()->row($this->loadOrder($order->id));

        $this->assertSame('--', $row['observaciones']);
    }

    public function test_row_shows_placeholders_for_missing_optional_relations(): void
    {
        // Orden sin cliente, sin detallador, sin tipo de vehículo y sin pago:
        // user_id y vehicle_type_id son anulables en el esquema (p. ej. si se
        // elimina el usuario) y los pagos no son obligatorios a nivel de tabla.
        $order = $this->makeOrder([
            'client_id' => null,
            'user_id' => null,
            'vehicle_type_id' => null,
        ]);

        $row = $this->reportService()->row($this->loadOrder($order->id));

        $this->assertSame('--', $row['cliente']);
        $this->assertSame('--', $row['telefono']);
        $this->assertSame('--', $row['matricula']);
        $this->assertSame('--', $row['flota']);
        $this->assertSame('--', $row['categoria']);
        $this->assertSame('--', $row['servicios']);
        $this->assertSame('--', $row['nombre_lavad']);
        $this->assertSame('--', $row['pago']);
        $this->assertSame('--', $row['metodo']);
    }

    public function test_row_uses_stored_amounts_without_recalculating_them(): void
    {
        // Descuento e IVA guardados en la orden: el informe debe reflejarlos
        // tal cual (el descuento no se resta dos veces ni el IVA se recalcula).
        $client = $this->makeClient();
        $order = $this->makeOrder([
            'client_id' => $client->id,
            'subtotal' => 200,
            'discount_value' => 20,
            'taxes_value' => 37.8,
            'total' => 217.8,
        ]);
        $this->makePayment($order, ['type' => 1, 'status' => 3, 'subtotal' => 200, 'total' => 217.8]);

        $row = $this->reportService()->row($this->loadOrder($order->id));

        $this->assertSame(200.0, $row['subtotal']);
        $this->assertSame(37.8, $row['iva']);
        $this->assertSame(20.0, $row['descuento']);
        $this->assertSame(217.8, $row['total']);
    }

    public function test_row_translates_order_and_payment_states(): void
    {
        $client = $this->makeClient();

        $paid = $this->makeOrder(['client_id' => $client->id, 'status' => 3]);
        $this->makePayment($paid, ['type' => 3, 'status' => 3, 'total' => 30]);

        $pending = $this->makeOrder(['client_id' => $client->id, 'status' => 2]);
        $this->makePayment($pending, ['type' => 1, 'status' => 1, 'total' => 0]);

        $paidRow = $this->reportService()->row($this->loadOrder($paid->id));
        $pendingRow = $this->reportService()->row($this->loadOrder($pending->id));

        $this->assertSame('Terminado', $paidRow['estado']);
        $this->assertSame('Pagado', $paidRow['pago']);
        $this->assertSame('Transferencia', $paidRow['metodo']);

        $this->assertSame('En Proceso', $pendingRow['estado']);
        $this->assertSame('Pendiente', $pendingRow['pago']);
        $this->assertSame('Efectivo', $pendingRow['metodo']);
    }

    public function test_row_keeps_zero_amounts_as_numbers(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder([
            'client_id' => $client->id,
            'subtotal' => 0,
            'discount_value' => 0,
            'taxes_value' => 0,
            'total' => 0,
        ]);
        $this->makePayment($order, ['type' => 1, 'status' => 1, 'total' => 0]);

        $row = $this->reportService()->row($this->loadOrder($order->id));

        $this->assertSame(0.0, $row['subtotal']);
        $this->assertSame(0.0, $row['iva']);
        $this->assertSame(0.0, $row['descuento']);
        $this->assertSame(0.0, $row['total']);
    }

    public function test_each_order_generates_exactly_one_row_even_with_many_services(): void
    {
        $client = $this->makeClient();

        $simple = $this->makeOrder(['client_id' => $client->id, 'creation_date' => now()->subMinute()]);
        $this->attachServiceToOrder($simple, $this->makeService('Lavado Exterior'), 25.0);

        $complex = $this->makeOrder(['client_id' => $client->id]);
        $this->attachServiceToOrder($complex, $this->makeService('Lavado Exterior'), 25.0);
        $this->attachServiceToOrder($complex, $this->makeService('Encerado Rápido'), 15.0);
        $this->attachServiceToOrder($complex, $this->makeService('Aspirado', 'Extras'), 8.0);

        $orders = \App\Models\Order::with(['client', 'user', 'vehicleType', 'services', 'payments'])
            ->orderBy('creation_date')
            ->get();

        $rows = $orders->map(fn ($order) => $this->reportService()->row($order));

        $this->assertCount(2, $rows);
        $this->assertSame(
            [$this->orderNumber($simple), $this->orderNumber($complex)],
            $rows->pluck('orden')->all()
        );
    }
}
