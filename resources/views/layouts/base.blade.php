<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lavadero Brillante</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-bg">
    @include('layouts.header')
    @include('layouts.main')
    @include('layouts.footer')
</body>
</html>
