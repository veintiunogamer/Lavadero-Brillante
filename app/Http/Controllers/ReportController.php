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

class ReportController extends Controller
{
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

        $orders = $this->querySales($start, $end)->get();
        $orders = $this->filterSalesCollection($orders, $request);

        $data = $orders->map(function ($order) {

            $payment = $order->payments->first();

            return [
                'id' => $order->id,
                'consecutive_serial' => $order->consecutive_serial ?? null,
                'consecutive_number' => $order->consecutive_number ?? null,
                'creation_date' => $order->creation_date,
                'date' => $order->date,
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

        return response()->json([
            'success' => true,
            'range' => $request->query('date') ? 'custom' : $request->query('range', 'month'),
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
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

        $orders = $this->querySales($start, $end)->get();

        $summary = [
            'orders' => $orders->count(),
            'subtotal' => $orders->sum('subtotal'),
            'taxes_value' => $orders->sum('taxes_value'),
            'discount_value' => $orders->sum('discount_value'),
            'total' => $orders->sum('total'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.sales', [
            'title' => 'Cierre Diario',
            'periodLabel' => $target->format('d/m/Y'),
            'orders' => $orders,
            'summary' => $summary,
            'statusLabels' => $this->getOrderStatusLabels(),
            'paymentStatusLabels' => $this->getPaymentStatusLabels(),
            'paymentMethodLabels' => $this->getPaymentMethodLabels(),
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
            $orders = $this->filterSalesCollection($this->querySales($start, $end)->get(), $request);

            $summary = [
                'orders' => $orders->count(),
                'subtotal' => $orders->sum('subtotal'),
                'taxes_value' => $orders->sum('taxes_value'),
                'discount_value' => $orders->sum('discount_value'),
                'total' => $orders->sum('total'),
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.sales', [
                'title' => 'Ventas y Facturación',
                'periodLabel' => $periodLabel,
                'orders' => $orders,
                'summary' => $summary,
                'statusLabels' => $this->getOrderStatusLabels(),
                'paymentStatusLabels' => $this->getPaymentStatusLabels(),
                'paymentMethodLabels' => $this->getPaymentMethodLabels(),
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

            $orders = $this->filterSalesCollection($this->querySales($start, $end)->get(), $request);

            $filename = 'reporte-ventas-' . Carbon::now()->format('Ymd') . '.xlsx';

            return Excel::download(
                new ReportSalesExport(
                    $orders,
                    $this->getOrderStatusLabels(),
                    $this->getPaymentStatusLabels(),
                    $this->getPaymentMethodLabels(),
                    $this->getCompanyInfo(),
                    $periodLabel
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

        $range = $request->query('range', 'month');

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

    private function querySales(Carbon $start, Carbon $end)
    {
        return Order::with([
            'client:id,name,phone,license_plaque,fleet',
            'services:id,name',
            'payments:id,order_id,type,status,subtotal,total'
        ])
            ->select([
                'id',
                'client_id',
                'user_id',
                'consecutive_serial',
                'consecutive_number',
                'creation_date',
                'date',
                'subtotal',
                'discount_value',
                'taxes_value',
                'total',
                'status',
            ])
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date');
    }

    private function queryClients(?string $search = null, ?string $fleet = null)
    {
        $query = DB::table('client as c')
            ->leftJoin('order as o', 'c.id', '=', 'o.client_id')
            ->select([
                'c.id',
                'c.name',
                'c.phone',
                'c.license_plaque',
                'c.brand',
                'c.fleet',
                DB::raw('COUNT(o.id) as orders_count'),
                DB::raw('COALESCE(SUM(o.total), 0) as total_spent'),
                DB::raw('MAX(o.creation_date) as last_order_date'),
            ])
            ->groupBy('c.id', 'c.name', 'c.phone', 'c.license_plaque', 'c.brand', 'c.fleet')
            ->orderBy('c.name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('c.name', 'like', '%' . $search . '%')
                    ->orWhere('c.phone', 'like', '%' . $search . '%')
                    ->orWhere('c.license_plaque', 'like', '%' . $search . '%')
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
