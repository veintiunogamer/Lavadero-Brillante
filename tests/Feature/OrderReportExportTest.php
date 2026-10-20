<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\OrderReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Tests\Feature\Concerns\BuildsOrderReportData;
use Tests\TestCase;

class OrderReportExportTest extends TestCase
{
    use RefreshDatabase, BuildsOrderReportData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->makeUser('Administrador Test', 1);

        // maatwebsite/excel escribe archivos temporales de salida en
        // storage/framework/laravel-excel, que en este entorno no es
        // escribible (pertenece a otro usuario). Redirigimos a /tmp.
        $tmp = sys_get_temp_dir() . '/lavadero-excel-tmp';
        if (!is_dir($tmp)) {
            mkdir($tmp, 0777, true);
        }
        config(['excel.temporary_files.local_path' => $tmp]);
    }

    protected function actAsAdmin(): static
    {
        $this->actingAs($this->admin);

        return $this;
    }

    protected function buildCompleteOrder(array $orderAttrs = []): Order
    {
        $client = $this->makeClient([
            'name' => 'Cliente Export',
            'phone' => '600000001',
            'license_plaque' => 'EXP-001',
            'fleet' => true,
        ]);
        $detailer = $this->makeUser('Detallador Export');
        $vehicleType = $this->makeVehicleType('Turismo');

        $order = $this->makeOrder(array_merge([
            'client_id' => $client->id,
            'user_id' => $detailer->id,
            'vehicle_type_id' => $vehicleType->id,
            'vehicle_notes' => 'Observación de prueba',
            'subtotal' => 100,
            'discount_value' => 10,
            'taxes_value' => 18.9,
            'total' => 108.9,
            'status' => 3,
            'consecutive_serial' => '17102026',
            'consecutive_number' => '007',
            'creation_date' => '2026-10-17 09:30:00',
            'date' => '2026-10-17',
            'hour_in' => '2026-10-17 08:30:00',
            'hour_out' => '2026-10-17 10:45:00',
        ], $orderAttrs));

        $this->attachServiceToOrder($order, $this->makeService('Lavado Completo'), 100.0);
        $this->attachServiceToOrder($order, $this->makeService('Encerado'), 25.0);
        $this->makePayment($order, ['type' => 2, 'status' => 3, 'total' => 108.9]);

        return $order;
    }

    /**
     * Las descargas (Excel y PDF) devuelven un BinaryFileResponse, cuyo
     * getContent() es false: hay que leer el archivo temporal que se sirve.
     */
    protected function responseContent($response): string
    {
        $base = $response->baseResponse;

        if ($base instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            return (string) file_get_contents($base->getFile()->getPathname());
        }

        return (string) $response->getContent();
    }

    protected function readXlsxFromResponse($response): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $content = $this->responseContent($response);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx') . '.xlsx';
        file_put_contents($tempFile, $content);

        return (new Xlsx())->load($tempFile);
    }

    protected function expectedHeadings(): array
    {
        return [
            'Fecha', 'Entrada - Salida', 'Cliente', 'Teléfono', 'Matrícula', 'Categoría',
            'Flota', 'Servicios', 'Detallador', 'Subtotal',
            'IVA', 'Descuento ($)', 'Pago', 'Método', 'Estado', 'Total', 'Observaciones',
        ];
    }
    public function test_reports_view_has_no_order_number_column_and_renders_plate(): void
    {
        $this->actAsAdmin();

        $response = $this->get('/reports');

        $response->assertStatus(200);

        $html = $response->getContent();

        // La columna "# Orden" se ha retirado de la tabla de ventas.
        $this->assertStringNotContainsString('# Orden', $html);
        $this->assertStringNotContainsString('formatOrderNumber(order)', $html);

        // Se muestran la placa del cliente y las horas de entrada/salida.
        $this->assertStringContainsString('licensePlate(order.client ? order.client.license_plaque : null)', $html);
        $this->assertStringContainsString('licensePlate(client.license_plaque)', $html);
        $this->assertStringContainsString('formatTime(order.hour_in)', $html);
        $this->assertStringContainsString('formatTime(order.hour_out)', $html);
    }

    /* ------------------------------------------------------------------ */
    /* JSON del endpoint de ventas                                       */
    /* ------------------------------------------------------------------ */

    public function test_sales_endpoint_returns_plate_and_entry_exit_hours(): void
    {
        $this->actAsAdmin();

        $client = $this->makeClient([
            'name' => 'Cliente JSON',
            'license_plaque' => '1234ABC',
        ]);

        $this->makeOrder([
            'client_id' => $client->id,
            'date' => now()->toDateString(),
            'hour_in' => '2026-10-17 08:30:00',
            'hour_out' => '2026-10-17 10:45:00',
        ]);

        $response = $this->get('/reports/sales?range=month');

        $response->assertStatus(200);

        $row = $response->json('data.0');

        $this->assertSame('Cliente JSON', $row['client']['name']);
        $this->assertSame('1234ABC', $row['client']['license_plaque']);
        $this->assertSame('2026-10-17 08:30:00', $row['hour_in']);
        $this->assertSame('2026-10-17 10:45:00', $row['hour_out']);
    }

    /* ------------------------------------------------------------------ */
    /* Excel                                                              */
    /* ------------------------------------------------------------------ */

    public function test_excel_export_returns_17_headings_in_order(): void
    {
        $this->actAsAdmin();

        $this->buildCompleteOrder();

        $response = $this->get('/reports/excel/current?tab=sales&range=month');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString(
            'filename=reporte-ventas-' . now()->format('Ymd') . '.xlsx',
            (string) $response->headers->get('Content-Disposition')
        );

        $spreadsheet = $this->readXlsxFromResponse($response);
        $sheet = $spreadsheet->getActiveSheet();

        $headings = [];
        foreach (range('A', 'Q') as $col) {
            $headings[] = $sheet->getCell($col . '6')->getValue();
        }

        $this->assertSame($this->expectedHeadings(), $headings);
    }

    public function test_excel_export_one_row_per_order_even_with_multiple_services(): void
    {
        $this->actAsAdmin();

        $order = $this->buildCompleteOrder();

        $response = $this->get('/reports/excel/current?tab=sales&range=month');
        $response->assertStatus(200);

        $spreadsheet = $this->readXlsxFromResponse($response);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('17/10/2026', $sheet->getCell('A7')->getValue());
        $this->assertSame('08:30 - 10:45', $sheet->getCell('B7')->getValue());
        $this->assertSame('Cliente Export', $sheet->getCell('C7')->getValue());
        // El teléfono numérico se guarda como número en el XLSX
        // (conversión de PhpSpreadsheet, comportamiento preexistente).
        $this->assertEquals(600000001, $sheet->getCell('D7')->getValue());
        $this->assertSame('EXP-001', $sheet->getCell('E7')->getValue());
        $this->assertSame('Turismo', $sheet->getCell('F7')->getValue());
        $this->assertSame('Sí', $sheet->getCell('G7')->getValue());
        // El join de servicios no define ORDER BY: se conserva el orden de
        // inserción, igual que asume OrderReportServiceTest.
        $this->assertSame('Lavado Completo, Encerado', $sheet->getCell('H7')->getValue());
        $this->assertSame('Detallador Export', $sheet->getCell('I7')->getValue());
        $this->assertSame(100.0, $sheet->getCell('J7')->getValue());
        $this->assertSame(18.9, $sheet->getCell('K7')->getValue());
        $this->assertSame(10.0, $sheet->getCell('L7')->getValue());
        $this->assertSame('Pagado', $sheet->getCell('M7')->getValue());
        $this->assertSame('TPV', $sheet->getCell('N7')->getValue());
        $this->assertSame('Terminado', $sheet->getCell('O7')->getValue());
        $this->assertSame(108.9, $sheet->getCell('P7')->getValue());
        $this->assertSame('Observación de prueba', $sheet->getCell('Q7')->getValue());

        // No debe haber una segunda fila de datos.
        $this->assertNull($sheet->getCell('A8')->getValue());
    }

    public function test_excel_export_placeholder_when_optional_relations_missing(): void
    {
        $this->actAsAdmin();

        $client = $this->makeClient(['name' => 'Sin Relaciones', 'phone' => '', 'license_plaque' => '', 'fleet' => false]);
        $order = $this->makeOrder([
            'client_id' => $client->id,
            'subtotal' => 0,
            'total' => 0,
            'creation_date' => '2026-10-17 10:00:00',
            'date' => '2026-10-17',
        ]);
        $this->makePayment($order, ['type' => 1, 'status' => 1, 'total' => 0]);

        $response = $this->get('/reports/excel/current?tab=sales&range=month');
        $response->assertStatus(200);

        $spreadsheet = $this->readXlsxFromResponse($response);
        $sheet = $spreadsheet->getActiveSheet();

        // Sin horas de entrada/salida registradas.
        $this->assertSame('--', $sheet->getCell('B7')->getValue());
        $this->assertSame('--', $sheet->getCell('F7')->getValue());
        $this->assertSame('--', $sheet->getCell('H7')->getValue());
        $this->assertSame('--', $sheet->getCell('I7')->getValue());
        $this->assertSame('--', $sheet->getCell('Q7')->getValue());
        $this->assertSame('Pendiente', $sheet->getCell('M7')->getValue());
        $this->assertSame('Efectivo', $sheet->getCell('N7')->getValue());
        $this->assertSame('Pendiente', $sheet->getCell('O7')->getValue());
        // Los importes en 0 quedan como celda vacía en el XLSX: es el
        // comportamiento preexistente de la exportación (PhpSpreadsheet
        // descarta los 0 con la comparación no estricta de nulos).
        $this->assertNull($sheet->getCell('J7')->getValue());
        $this->assertNull($sheet->getCell('P7')->getValue());
    }

    public function test_excel_export_respects_date_filter(): void
    {
        $this->actAsAdmin();

        $this->buildCompleteOrder(['date' => '2026-10-17', 'creation_date' => '2026-10-17 09:30:00']);
        $this->buildCompleteOrder(['date' => '2026-10-18', 'creation_date' => '2026-10-18 09:30:00']);

        $response = $this->get('/reports/excel/current?tab=sales&date=2026-10-17');
        $response->assertStatus(200);

        $spreadsheet = $this->readXlsxFromResponse($response);
        $sheet = $spreadsheet->getActiveSheet();

        // Primera columna: Fecha (la columna "# Orden" ya no existe).
        $this->assertSame('17/10/2026', $sheet->getCell('A7')->getValue());
        // No debe haber una segunda fila de datos.
        $this->assertNull($sheet->getCell('A8')->getValue());
    }

    public function test_excel_export_amounts_are_numbers_not_strings(): void
    {
        $this->actAsAdmin();

        $this->buildCompleteOrder(['subtotal' => 200, 'discount_value' => 20, 'taxes_value' => 37.8, 'total' => 217.8]);

        $response = $this->get('/reports/excel/current?tab=sales&range=month');
        $response->assertStatus(200);

        $spreadsheet = $this->readXlsxFromResponse($response);
        $sheet = $spreadsheet->getActiveSheet();

        foreach (['J7', 'K7', 'L7', 'P7'] as $cell) {
            $this->assertIsNumeric($sheet->getCell($cell)->getValue(), "La celda {$cell} debe ser numérica");
        }
    }

    /* ------------------------------------------------------------------ */
    /* PDF                                                                */
    /* ------------------------------------------------------------------ */

    public function test_pdf_export_returns_downloadable_pdf(): void
    {
        $this->actAsAdmin();

        $this->buildCompleteOrder();

        $response = $this->get('/reports/pdf/current?tab=sales&range=month');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString(
            'filename=reporte-ventas-' . now()->format('Ymd') . '.pdf',
            (string) $response->headers->get('Content-Disposition')
        );

        $content = $this->responseContent($response);
        $this->assertStringStartsWith('%PDF', $content, 'El cuerpo debe comenzar con la cabecera de PDF');
        $this->assertGreaterThan(1000, strlen($content), 'El PDF no debe estar vacío');
    }

    public function test_pdf_view_renders_seventeen_headings(): void
    {
        $this->buildCompleteOrder();
        $orders = \App\Models\Order::with(['client', 'user', 'vehicleType', 'services', 'payments'])->get();
        $report = new OrderReportService(
            self::STATUS_LABELS,
            self::PAYMENT_STATUS_LABELS,
            self::PAYMENT_METHOD_LABELS,
        );

        $html = view('reports.pdf.sales', [
            'title' => 'Ventas y Facturación',
            'periodLabel' => 'Este mes',
            'orders' => $orders,
            'summary' => [],
            'report' => $report,
            'company' => [
                'name' => 'Lavadero Brillante',
                'owner' => 'Eusebio Borrego Lau',
                'nif' => '28614307F',
                'address' => 'Calle Dr. Fleming, 21',
                'city' => '46960 Aldaya',
                'logo' => public_path('images/logo_alterno.png'),
            ],
        ])->render();

        foreach ($this->expectedHeadings() as $heading) {
            $this->assertStringContainsString($heading, $html, "La vista PDF debe incluir el encabezado '{$heading}'");
        }

        // El PDF pinta sus columnas en el mismo orden que dicta el servicio.
        preg_match_all('/<th>(.*?)<\/th>/u', $html, $thMatches);
        $this->assertSame(OrderReportService::headings(), $thMatches[1]);

        $this->assertStringContainsString('08:30 - 10:45', $html);
        $this->assertStringContainsString('Cliente Export', $html);
        $this->assertStringContainsString('Detallador Export', $html);
        $this->assertStringContainsString('Observación de prueba', $html);
        // La columna "# Orden" ya no existe en el informe.
        $this->assertStringNotContainsString('# Orden', $html);
    }

    /* ------------------------------------------------------------------ */
    /* Equivalencia entre formatos                                        */
    /* ------------------------------------------------------------------ */

    public function test_both_formats_use_the_same_row_data(): void
    {
        $this->actAsAdmin();

        $order = $this->buildCompleteOrder();
        $loaded = \App\Models\Order::with(['client', 'user', 'vehicleType', 'services', 'payments'])->findOrFail($order->id);

        $row = array_values((new OrderReportService(
            self::STATUS_LABELS,
            self::PAYMENT_STATUS_LABELS,
            self::PAYMENT_METHOD_LABELS,
        ))->row($loaded));

        $response = $this->get('/reports/excel/current?tab=sales&range=month');
        $response->assertStatus(200);

        $spreadsheet = $this->readXlsxFromResponse($response);
        $sheet = $spreadsheet->getActiveSheet();

        foreach (range('A', 'Q') as $index => $col) {
            $expected = $row[$index];
            $actual = $sheet->getCell($col . '7')->getValue();

            if (is_numeric($expected) && is_numeric($actual)) {
                // Importes y teléfonos: el XLSX guarda los valores numéricos
                // como número, no como texto (comportamiento preexistente).
                $this->assertEquals($expected, $actual, "Columna {$col}7 no coincide");
            } else {
                $this->assertSame($expected, $actual, "Columna {$col}7 no coincide");
            }
        }
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->app->make('auth')->guard('web')->logout();

        $this->get('/reports/excel/current?tab=sales')->assertRedirect(route('login'));
        $this->get('/reports/pdf/current?tab=sales')->assertRedirect(route('login'));
    }

}

