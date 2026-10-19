<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;

/**
 * Prepara los datos del informe de ventas de órdenes.
 *
 * Es la única fuente de verdad de los 17 campos del informe: tanto la
 * exportación a Excel (App\Exports\ReportSalesExport) como el PDF
 * (resources/views/reports/pdf/sales.blade.php) construyen sus filas a partir
 * de este servicio, de modo que ambos formatos muestran exactamente la misma
 * información, con los mismos encabezados y en el mismo orden.
 */
class OrderReportService
{
    /**
     * Encabezados del informe, en el orden exacto requerido.
     */
    public const HEADINGS = [
        '# Orden',
        'Fecha',
        'Cliente',
        'Teléfono',
        'Matrícula',
        'Categoría',
        'Flota',
        'Servicios',
        'Observaciones',
        'Detallador',
        'Subtotal',
        'IVA',
        'Descuento ($)',
        'Pago',
        'Método',
        'Estado',
        'Total',
    ];

    /**
     * Claves de la fila que contienen importes económicos.
     * En Excel se exportan como números; en PDF se formatean al pintarlos.
     */
    public const AMOUNT_FIELDS = ['subtotal', 'iva', 'descuento', 'total'];

    private array $statusLabels;
    private array $paymentStatusLabels;
    private array $paymentMethodLabels;

    public function __construct(array $statusLabels, array $paymentStatusLabels, array $paymentMethodLabels)
    {
        $this->statusLabels = $statusLabels;
        $this->paymentStatusLabels = $paymentStatusLabels;
        $this->paymentMethodLabels = $paymentMethodLabels;
    }

    /**
     * Encabezados del informe (17 columnas, en orden).
     */
    public static function headings(): array
    {
        return self::HEADINGS;
    }

    /**
     * Construye la fila del informe para una orden.
     *
     * - Los importes se devuelven como números (sin símbolos monetarios) para
     *   que Excel los almacene como celdas numéricas; el PDF los formatea al
     *   pintarlos. Son los valores ya guardados en la orden: no se recalculan.
     * - Las relaciones opcionales inexistentes se muestran como '--'.
     * - Los servicios se unen con ", " para no perder ninguno.
     *
     * @return array<string, mixed> Filas con las claves en orden de self::HEADINGS
     */
    public function row(Order $order): array
    {
        $client = $order->client;
        $payment = $order->payments->first();
        $observations = trim((string) ($order->vehicle_notes ?? ''));

        return [
            'orden'       => $this->orderNumber($order),
            // Fecha del servicio (order.date), la misma fuente que pinta la
            // tabla del informe y que usa el filtro por rango: no creation_date
            // (instante de registro), para que tabla y exportación coincidan.
            'fecha'       => $order->date
                ? Carbon::parse($order->date)->format('d/m/Y')
                : '--',
            'cliente'     => optional($client)->name ?? '--',
            'telefono'    => optional($client)->phone ?? '--',
            'matricula'   => optional($client)->license_plaque ?? '--',
            'categoria'   => optional($order->vehicleType)->name ?? '--',
            'flota'       => $client ? ($client->fleet ? 'Sí' : 'No') : '--',
            'servicios'   => $order->services->pluck('name')->filter()->join(', ') ?: '--',
            'observaciones' => $observations !== '' ? $observations : '--',
            // Detallador asignado a la orden (order.user_id → users.name)
            'nombre_lavad' => optional($order->user)->name ?? '--',
            'subtotal'    => round((float) ($order->subtotal ?? 0), 2),
            'iva'         => round((float) ($order->taxes_value ?? 0), 2),
            'descuento'   => round((float) ($order->discount_value ?? 0), 2),
            'pago'        => $payment
                ? ($this->paymentStatusLabels[$payment->status] ?? 'Desconocido')
                : '--',
            'metodo'      => $payment
                ? ($this->paymentMethodLabels[$payment->type] ?? 'Desconocido')
                : '--',
            'estado'      => $this->statusLabels[$order->status] ?? 'Desconocido',
            'total'       => round((float) ($order->total ?? 0), 2),
        ];
    }

    /**
     * Número de orden: consecutivo del sistema o, en su defecto, las primeras
     * letras del UUID.
     */
    private function orderNumber(Order $order): string
    {
        return $order->consecutive_serial && $order->consecutive_number
            ? $order->consecutive_serial . '-' . $order->consecutive_number
            : strtoupper(substr($order->id, 0, 8));
    }
}
