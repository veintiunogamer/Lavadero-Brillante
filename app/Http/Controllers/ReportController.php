<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportSalesExport;
use App\Exports\ReportClientsExport;
use App\Services\OrderReportService;

class ReportController extends Controller
{
    private const MAX_SALES_PDF_ROWS = 50;

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

    /**
     * Datos de ventas y facturación
     */
    public function sales(Request $request)
    {
        [$start, $end, $periodLabel] = $this->resolveSalesWindow($request);

        $orders = collect($this->querySales($start, $end)->cursor());
        $orders = $this->filterSalesCollection($orders, $request);

        $data = $orders->map(function ($order) {

            $payment = $order->payments->first();

            return [
                'id' => $order->id,
                'consecutive_serial' => $order->consecutive_serial ?? null,
                'consecutive_number' => $order->consecutive_number ?? null,
                'creation_date' => $order->creation_date,
                'date' => $order->date,
                'hour_in' => $order->hour_in,
                'hour_out' => $order->hour_out,
                'subtotal' => $order->subtotal,
                'discount_value' => $order->discount_value,
                'taxes_value' => $order->taxes_value,
                'total' => $order->total,
                'status' => $order->status,
                'client' => $order->client,
                'user' => $order->user,
                'services' => $order->services,
                'payment' => $payment ? [
                    'status' => $payment->status,
                    'type' => $payment->type,
                    'subtotal' => $payment->subtotal,
                    'total' => $payment->total,
                ] : null,
            ];
        });

        $summary = [
            ...$this->buildSalesSummary($orders),
        ];

        return response()->json([
            'success' => true,
            'range' => $request->query('date') ? 'custom' : $request->query('range', 'month'),
            'start' => $start ? $start->toDateString() : '0000-01-01',
            'end' => $end ? $end->toDateString() : '9999-12-31',
            'periodLabel' => $periodLabel,
            'data' => $data,
            'summary' => $summary,
        ]);
    }

    /**
     * Datos de clientes y fidelización
     */
    public function clients(Request $request)
    {
        $search = $request->query('search');
        $fleet = $request->query('fleet');

        $clients = $this->queryClients($search, $fleet)->get();

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    /**
     * Genera PDF de cierre diario
     */
    public function dailyPdf(Request $request)
    {
        if (!class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            return response()->json([
                'success' => false,
                'message' => 'PDF no disponible. Instala barryvdh/laravel-dompdf y ejecuta composer install.'
            ], 501);
        }

        $date = $request->query('date');
        $target = $date ? Carbon::parse($date) : Carbon::today();

        $start = $target->copy()->startOfDay();
        $end = $target->copy()->endOfDay();

        $orders = $this->querySales($start, $end)
            ->limit(self::MAX_SALES_PDF_ROWS + 1)
            ->get();

        if ($orders->count() > self::MAX_SALES_PDF_ROWS) {
            return $this->salesPdfLimitResponse();
        }

        $summary = $this->buildSalesSummary($orders);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.sales', [
            'title' => 'Cierre Diario',
            'periodLabel' => $target->format('d/m/Y'),
            'orders' => $orders,
            'summary' => $summary,
            'report' => $this->orderReportService(),
            'company' => $this->getCompanyInfo(),
        ]);

        $filename = 'cierre-diario-' . $target->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Descarga el informe actual según el tab
     */
    public function currentPdf(Request $request)
    {
        if (!class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            return response()->json([
                'success' => false,
                'message' => 'PDF no disponible. Instala barryvdh/laravel-dompdf y ejecuta composer install.'
            ], 501);
        }

        $tab = $request->query('tab', 'sales');

        if ($tab === 'sales') {
            [$start, $end, $periodLabel] = $this->resolveSalesWindow($request);
            $orders = $this->querySales($start, $end)
                ->limit(self::MAX_SALES_PDF_ROWS + 1)
                ->get();

            if ($orders->count() > self::MAX_SALES_PDF_ROWS) {
                return $this->salesPdfLimitResponse();
            }

            $summary = $this->buildSalesSummary($orders);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.sales', [
                'title' => 'Ventas y Facturación',
                'periodLabel' => $periodLabel,
                'orders' => $orders,
                'summary' => $summary,
                'report' => $this->orderReportService(),
                'company' => $this->getCompanyInfo(),
            ]);

            $filename = 'reporte-ventas-' . Carbon::now()->format('Ymd') . '.pdf';

            return $pdf->download($filename);
        }

        if ($tab === 'clients') {
            $search = $request->query('search');
            $fleet = $request->query('fleet');
            $clients = $this->queryClients($search, $fleet)->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.clients', [
                'title' => 'Clientes y Fidelización',
                'periodLabel' => 'Informe actual',
                'clients' => $clients,
                'company' => $this->getCompanyInfo(),
            ]);

            $filename = 'reporte-clientes-' . Carbon::now()->format('Ymd') . '.pdf';

            return $pdf->download($filename);
        }

        return response()->json([
            'success' => false,
            'message' => 'El informe solicitado no está disponible.'
        ], 400);
    }

    /**
     * Descarga el informe actual en Excel (CSV)
     */
    public function currentExcel(Request $request)
    {
        $tab = $request->query('tab', 'sales');

        if ($tab === 'sales') {

            [$start, $end, $periodLabel] = $this->resolveSalesWindow($request);

            $orders = collect($this->querySales($start, $end)->cursor());
            $summary = $this->buildSalesSummary($orders);

            $filename = 'reporte-ventas-' . Carbon::now()->format('Ymd') . '.xlsx';

            return Excel::download(
                new ReportSalesExport(
                    $orders,
                    $this->orderReportService(),
                    $this->getCompanyInfo(),
                    $periodLabel,
                    $summary
                ),
                $filename
            );
        }

        if ($tab === 'clients') {

            $search = $request->query('search');
            $fleet = $request->query('fleet');
            $clients = $this->queryClients($search, $fleet)->get();

            $filename = 'reporte-clientes-' . Carbon::now()->format('Ymd') . '.xlsx';

            return Excel::download(
                new ReportClientsExport($clients, $this->getCompanyInfo(), 'Informe actual'),
                $filename
            );
        }

        return response()->json([
            'success' => false,
            'message' => 'El informe solicitado no está disponible.'
        ], 400);
    }

    private function getRangeDates(string $range): array
    {
        $now = Carbon::now();

        if ($range === 'today') {
            return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }

        if ($range === 'week') {
            $start = $now->copy()->startOfWeek(Carbon::MONDAY);
            $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
            return [$start, $end];
        }

        return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
    }

    private function resolveSalesWindow(Request $request): array
    {
        $date = $request->query('date');

        if ($date) {
            $target = Carbon::parse($date);
            return [
                $target->copy()->startOfDay(),
                $target->copy()->endOfDay(),
                'Fecha: ' . $target->format('d/m/Y'),
            ];
        }

        $range = $request->query('range');

        // Sin rango ni fecha: mostrar TODOS los registros del sistema
        if (!isset($range)) {
            return [null, null, 'Todos los registros'];
        }

        if (!in_array($range, ['today', 'week', 'month'], true)) {
            $range = 'month';
        }

        [$start, $end] = $this->getRangeDates($range);

        $periodLabel = match ($range) {
            'today' => 'Hoy',
            'week' => 'Esta semana',
            default => 'Este mes',
        };

        return [$start, $end, $periodLabel];
    }

    private function querySales(?Carbon $start = null, ?Carbon $end = null)
    {
        return Order::with([
            'client:id,name,phone,license_plaque,temp_registration,fleet',
            'user:id,name',
            'vehicleType:id,name',
            'services:id,name',
            'payments:id,order_id,type,status,subtotal,total'
        ])
            ->select([
                'id',
                'client_id',
                'user_id',
                'vehicle_type_id',
                'consecutive_serial',
                'consecutive_number',
                'creation_date',
                'date',
                'hour_in',
                'hour_out',
                'subtotal',
                'discount_value',
                'taxes_value',
                'total',
                'status',
                'vehicle_notes',
            ])
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            })
            ->orderByDesc('date');
    }

    /**
     * Servicio compartido que prepara las filas del informe de órdenes
     * para Excel y PDF (misma lógica, mismos datos).
     */
    private function orderReportService(): OrderReportService
    {
        return new OrderReportService(
            $this->getOrderStatusLabels(),
            $this->getPaymentStatusLabels(),
            $this->getPaymentMethodLabels(),
        );
    }

    private function queryClients(?string $search = null, ?string $fleet = null)
    {
        $query = DB::table('client as c')
            ->leftJoin('order as o', 'c.id', '=', 'o.client_id')
            ->whereNull('o.deleted_at')
            ->select([
                'c.id',
                'c.name',
                'c.phone',
                'c.license_plaque',
                'c.temp_registration',
                'c.brand',
                'c.fleet',
                DB::raw('COUNT(o.id) as orders_count'),
                DB::raw('COALESCE(SUM(o.total), 0) as total_spent'),
                DB::raw('MAX(o.creation_date) as last_order_date'),
            ])
            ->groupBy('c.id', 'c.name', 'c.phone', 'c.license_plaque', 'c.temp_registration', 'c.brand', 'c.fleet')
            ->orderBy('c.name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('c.name', 'like', '%' . $search . '%')
                    ->orWhere('c.phone', 'like', '%' . $search . '%')
                    ->orWhere('c.license_plaque', 'like', '%' . $search . '%')
                    ->orWhere('c.temp_registration', 'like', '%' . $search . '%')
                    ->orWhere('c.brand', 'like', '%' . $search . '%')
                    ->orWhere('c.fleet', 'like', '%' . $search . '%');
            });
        }

        if ($fleet !== null && $fleet !== '') {
            $query->where('c.fleet', '=', $fleet);
        }

        return $query;
    }

    private function filterSalesCollection(Collection $orders, Request $request): Collection
    {
        $fleet = $request->query('fleet');
        $paymentStatus = $request->query('payment_status');
        $paymentMethod = $request->query('payment_method');
        $date = $request->query('date');

        return $orders->filter(function ($order) use ($fleet, $paymentStatus, $paymentMethod, $date) {
            if ($date && (string) $order->date !== (string) $date) {
                return false;
            }

            if ($fleet !== null && $fleet !== '') {
                $orderFleet = data_get($order, 'client.fleet');
                if ((string) (int) $orderFleet !== (string) $fleet) {
                    return false;
                }
            }

            $payment = $order->payments->first();

            if ($paymentStatus !== null && $paymentStatus !== '') {
                if (!$payment || (string) $payment->status !== (string) $paymentStatus) {
                    return false;
                }
            }

            if ($paymentMethod !== null && $paymentMethod !== '') {
                if (!$payment || (string) $payment->type !== (string) $paymentMethod) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    private function getCompanyInfo(): array
    {
        return [
            'name' => 'Lavadero Brillante',
            'owner' => 'Eusebio Borrego Lau',
            'nif' => '28614307F',
            'address' => 'Calle Dr. Fleming, 21',
            'city' => '46960 Aldaya',
            'logo' => public_path('images/logo_alterno.png'),
        ];
    }

    private function salesPdfLimitResponse(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'El PDF admite hasta ' . self::MAX_SALES_PDF_ROWS . ' registros. Reduce el periodo o descarga el informe en Excel.',
        ], 422);
    }

    private function buildSalesSummary(Collection $orders): array
    {
        return [
            'orders' => $orders->count(),
            'cash' => $orders->sum(function ($order) {
                $payment = $order->payments->first();
                return $payment && $payment->type == 1 ? $payment->total : 0;
            }),
            'card' => $orders->sum(function ($order) {
                $payment = $order->payments->first();
                return $payment && $payment->type == 2 ? $payment->total : 0;
            }),
            'transfer' => $orders->sum(function ($order) {
                $payment = $order->payments->first();
                return $payment && $payment->type == 3 ? $payment->total : 0;
            }),
            'subtotal' => $orders->sum('subtotal'),
            'discount_value' => $orders->sum('discount_value'),
            'taxes_value' => $orders->sum('taxes_value'),
            'total' => $orders->sum('total'),
        ];
    }

    private function getOrderStatusLabels(): array
    {
        return [
            1 => 'Pendiente',
            2 => 'En Proceso',
            3 => 'Terminado',
            4 => 'Cancelado',
        ];
    }

    private function getPaymentStatusLabels(): array
    {
        return [
            1 => 'Pendiente',
            2 => 'Parcial',
            3 => 'Pagado',
        ];
    }

    private function getPaymentMethodLabels(): array
    {
        return [
            1 => 'Efectivo',
            2 => 'TPV',
            3 => 'Transferencia',
            4 => 'Otro',
        ];
    }
}
