<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        $range = $request->query('range', 'month');

        if (!in_array($range, ['today', 'week', 'month'], true)) {
            $range = 'month';
        }

        [$start, $end] = $this->getRangeDates($range);

        $orders = Order::with([
            'client:id,name,phone,license_plaque',
            'user:id,name',
            'services:id,name',
            'payments:id,order_id,type,status,subtotal,total'
        ])->whereBetween('creation_date', [$start, $end])
        ->orderByDesc('creation_date')
        ->get();

        $data = $orders->map(function ($order) {
            
            $payment = $order->payments->first();

            return [
                'id' => $order->id,
                'consecutive_serial' => $order->consecutive_serial ?? null,
                'consecutive_number' => $order->consecutive_number ?? null,
                'creation_date' => $order->creation_date,
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
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
            'subtotal' => $orders->sum('subtotal'),
            'discount' => $orders->sum('discount'),
            'total' => $orders->sum('total'),
        ];

        return response()->json([
            'success' => true,
            'range' => $range,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'data' => $data,
            'summary' => $summary,
        ]);
    }

    /**
     * Datos de clientes y fidelización
     */
    public function clients()
    {
        $clients = DB::table('client as c')
            ->leftJoin('order as o', 'c.id', '=', 'o.client_id')
            ->select([
                'c.id',
                'c.name',
                'c.phone',
                'c.license_plaque',
                'c.status',
                DB::raw('COUNT(o.id) as orders_count'),
                DB::raw('COALESCE(SUM(o.total), 0) as total_spent'),
                DB::raw('MAX(o.creation_date) as last_order_date'),
            ])
            ->groupBy('c.id', 'c.name', 'c.phone', 'c.license_plaque', 'c.status')
            ->orderBy('c.name')
            ->get();

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
            'discount' => $orders->sum('discount'),
            'total' => $orders->sum('total'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.sales', [
            'title' => 'Cierre Diario',
            'periodLabel' => $target->format('d/m/Y'),
            'orders' => $orders,
            'summary' => $summary,
            'statusLabels' => $this->getOrderStatusLabels(),
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
            $range = $request->query('range', 'month');
            if (!in_array($range, ['today', 'week', 'month'], true)) {
                $range = 'month';
            }

            [$start, $end] = $this->getRangeDates($range);
            $orders = $this->querySales($start, $end)->get();

            $summary = [
                'orders' => $orders->count(),
                'subtotal' => $orders->sum('subtotal'),
                'discount' => $orders->sum('discount'),
                'total' => $orders->sum('total'),
            ];

            $periodLabel = match ($range) {
                'today' => 'Hoy',
                'week' => 'Esta semana',
                default => 'Este mes',
            };

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.sales', [
                'title' => 'Ventas y Facturación',
                'periodLabel' => $periodLabel,
                'orders' => $orders,
                'summary' => $summary,
                'statusLabels' => $this->getOrderStatusLabels(),
            ]);

            $filename = 'reporte-ventas-' . Carbon::now()->format('Ymd') . '.pdf';

            return $pdf->download($filename);
        }

        if ($tab === 'clients') {
            $search = $request->query('search');
            $clients = $this->queryClients($search)->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.clients', [
                'title' => 'Clientes y Fidelización',
                'periodLabel' => 'Informe actual',
                'clients' => $clients,
            ]);

            $filename = 'reporte-clientes-' . Carbon::now()->format('Ymd') . '.pdf';

            return $pdf->download($filename);
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

    private function querySales(Carbon $start, Carbon $end)
    {
        return Order::with(['client:id,name,phone,license_plaque', 'services:id,name'])
            ->whereBetween('creation_date', [$start, $end])
            ->orderByDesc('creation_date');
    }

    private function queryClients(?string $search = null)
    {
        $query = DB::table('client as c')
            ->leftJoin('order as o', 'c.id', '=', 'o.client_id')
            ->select([
                'c.id',
                'c.name',
                'c.phone',
                'c.license_plaque',
                DB::raw('COUNT(o.id) as orders_count'),
                DB::raw('COALESCE(SUM(o.total), 0) as total_spent'),
                DB::raw('MAX(o.creation_date) as last_order_date'),
            ])
            ->groupBy('c.id', 'c.name', 'c.phone', 'c.license_plaque')
            ->orderBy('c.name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('c.name', 'like', '%' . $search . '%')
                    ->orWhere('c.phone', 'like', '%' . $search . '%')
                    ->orWhere('c.license_plaque', 'like', '%' . $search . '%');
            });
        }

        return $query;
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
}
