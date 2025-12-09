@extends('layouts.base')

@section('title', 'Configuraciones')


@section('content')

    <div id="settings-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;">
        
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

            <div class="col-12 d-flex justify-content-between align-items-center mb-3 p-4">
                <div class="col-6">
                    <h3 class="card-title mb-3">
                        <i class="fa-solid fa-cog icon color-blue"></i> 
                        Configuraciones
                    </h3>
                    <p class="fw-bold small text-muted">Configuraciones del sistema.</p>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab" aria-controls="categories" aria-selected="true"><i class="fa-solid fa-tags me-2"></i>Categorías</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab" aria-controls="services" aria-selected="false"><i class="fa-solid fa-tools me-2"></i>Servicios</button>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vehicle-types-tab" data-bs-toggle="tab" data-bs-target="#vehicle-types" type="button" role="tab" aria-controls="vehicle-types" aria-selected="false"><i class="fa-solid fa-car me-2"></i>Tipos de Vehículo</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="clients-tab" data-bs-toggle="tab" data-bs-target="#clients" type="button" role="tab" aria-controls="clients" aria-selected="false"><i class="fa-solid fa-users me-2"></i>Clientes</button>
                </li>

            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3" id="settingsTabContent">

                <div class="tab-pane fade show active" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                    <div id="categories-content">
                        <p>Cargando categorías...</p>
                    </div>
                </div>

                <div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab">
                    <div id="services-content">
                        <p>Cargando servicios...</p>
                    </div>
                </div>

                <div class="tab-pane fade" id="vehicle-types" role="tabpanel" aria-labelledby="vehicle-types-tab">
                    <div id="vehicle-types-content">
                        <p>Cargando tipos de vehículo...</p>
                    </div>
                </div>

                <div class="tab-pane fade" id="clients" role="tabpanel" aria-labelledby="clients-tab">
                    <div id="clients-content">
                        <p>Cargando clientes...</p>
                    </div>
                </div>

            </div>

        </div>

    </div>

    @include('category.formCategory')

@endsection