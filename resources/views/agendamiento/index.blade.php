@extends('layouts.base')
@section('content')
<div class="card-form">
    <h2 class="card-title"><i class="fa-solid fa-calendar-check icon"></i> Agendamiento</h2>
    <div class="tabs">
        <button class="tab-btn active">Pendientes</button>
        <button class="tab-btn">En Proceso</button>
        <button class="tab-btn">Terminados</button>
    </div>
    <div class="tab-content">
        <!-- Aquí va el contenido de cada tab -->
    </div>
</div>
@endsection