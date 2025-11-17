<!DOCTYPE html>

<html lang="es">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lavadero Brillante</title>

        <!-- Font Awesome CDN -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Bootstrap 5 CDN -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicons -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon.png') }}">
        <link rel="shortcut icon" href="{{ asset('images/icon.png') }}">
        <meta name="theme-color" content="#0a2239">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Bootstrap 5 JS CDN -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    </head>

    <body class="app-bg">
        
        @include('layouts.header')
        @include('layouts.main')
        @include('layouts.footer')

    </body>

</html>
