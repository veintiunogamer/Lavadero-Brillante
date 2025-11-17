@extends('layouts.base')
@section('content')

<div class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;">
    <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1200px;">

    <h2 class="card-title mb-3"><i class="fa-solid fa-calendar-check icon color-blue"></i> Agendamiento</h2>
    
    <div class="mb-4">
        <div class="btn-group" role="group">
            <button class="btn btn-primary active">Pendientes</button>
            <button class="btn btn-outline-primary">En Proceso</button>
            <button class="btn btn-outline-primary">Terminados</button>
        </div>
    </div>

    <hr>

    <div class="tab-content">
        <!-- Aquí va el contenido de cada tab -->
    </div>

    </div>
</div>

@endsection