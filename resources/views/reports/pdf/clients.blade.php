<!doctype html>

<html lang="es">

<head>

    <meta charset="utf-8">
    <title>{{ $title }}</title>

    <!-- Usamos DejaVu Sans para asegurar compatibilidad con caracteres especiales en PDF -->
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .header {
            width: 100%;
            margin-bottom: 12px;
        }

        .logo {
            width: 120px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .company-owner {
            color: #374151;
            margin: 2px 0 0;
        }

        .company-meta {
            color: #4b4c4d;
            margin: 2px 0 0;
        }

        .title {
            font-size: 18px;
            margin: 0;
        }

        .subtitle {
            color: #4b4c4d;
            margin: 2px 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #000000;
            color: #ffffff;
            text-align: left;
        }
    </style>

</head>

<body>

    @php
        $company = $company ?? [
            'name' => 'Lavadero Brillante',
            'owner' => 'Eusebio Borrego Lau',
            'nif' => '28614307F',
            'address' => 'Calle Dr. Fleming, 21',
            'city' => '46960 Aldaya',
            'logo' => public_path('images/logo_alterno.png'),
        ];
    @endphp

    <table class="header">

        <tr>
            <td style="width: 140px;">
                <img src="{{ $company['logo'] }}" alt="Logo" class="logo">
            </td>
            <td style="text-align: left;">
                <h1 class="company-name">{{ $company['name'] }}</h1>
                <div class="company-owner">{{ $company['owner'] }}</div>
                <div class="company-meta">{{ $company['nif'] }} | {{ $company['address'] }} | {{ $company['city'] }}</div>
                <div class="subtitle">{{ $title }}</div>
                <div class="subtitle">{{ $periodLabel }}</div>
            </td>
        </tr>

    </table>


    <table>

        <thead>
            <tr>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Matrícula</th>
                <th>Modelo</th>
                <th>Flota</th>
                <th>Citas</th>
                <th>Total gastado</th>
                <th>Última visita</th>
            </tr>
        </thead>

        <tbody>

            @forelse($clients as $client)
            <tr>
                <td>{{ $client->name }}</td>
                <td>{{ $client->phone ?? '--' }}</td>
                <td>{{ $client->license_plaque ?? '--' }}</td>
                <td>{{ $client->brand ?? '--' }}</td>
                <td>{{ $client->fleet == 1 ? 'Sí' : 'No' }}</td>
                <td>{{ isset($client->orders_count) ? $client->orders_count : '0' }}</td>
                <td>{{ number_format(isset($client->total_spent) ? $client->total_spent : '0', 2, ',', '.') }} €</td>
                <td>
                    {{ $client->last_order_date ? \Carbon\Carbon::parse($client->last_order_date)->format('d/m/Y') : '--' }}
                </td>
            </tr>
            @empty

            <tr>
                <td colspan="8">No hay datos para mostrar.</td>
            </tr>

            @endforelse

        </tbody>

    </table>

</body>

</html>
