@extends('layouts.base')

@section('content')

    <div id="agendamiento-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof agendamientoApp === 'function' ? agendamientoApp() : {}" x-init="init()">
        
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

            <div class="col-12 d-flex justify-content-between align-items-center mb-3 p-4">
                <div class="col-6">
                    <h2 class="card-title mb-3">
                        <i class="fa-solid fa-calendar-check icon color-blue"></i> 
                        Agendamiento
                    </h2>
                    <p class="fw-bold">Gestión de citas y agendamientos.</p>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="ordersTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button :class="currentTab === 1 ? 'nav-link active' : 'nav-link'" id="pending-tab" @click="changeTab(1)" type="button" role="tab" aria-controls="pending" :aria-selected="currentTab === 1">Pendientes</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button :class="currentTab === 2 ? 'nav-link active' : 'nav-link'" id="in-process-tab" @click="changeTab(2)" type="button" role="tab" aria-controls="in-process" :aria-selected="currentTab === 2">En Proceso</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button :class="currentTab === 3 ? 'nav-link active' : 'nav-link'" id="completed-tab" @click="changeTab(3)" type="button" role="tab" aria-controls="completed" :aria-selected="currentTab === 3">Terminados</button>
                </li>
            </ul>

            <!-- Loading Spinner -->
            <div x-show="loading" class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>

            <!-- Sin resultados -->
            <div x-show="!loading && orders.length === 0" class="text-center py-5 p-4">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No hay agendamientos <span x-text="currentTab === 1 ? 'pendientes' : (currentTab === 2 ? 'en proceso' : 'terminados')"></span></p>
            </div>

            <!-- Tabla de agendamientos -->
            <div x-show="!loading && orders.length > 0" class="table-responsive p-4">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(order, index) in orders" :key="index">
                            <tr>
                                <td x-text="order.id"></td>
                                <td x-text="order.client ? order.client.name : 'N/A'"></td>
                                <td x-text="order.service ? order.service.name : 'N/A'"></td>
                                <td x-text="formatDate(order.creation_date)"></td>
                                <td x-text="formatTime(order.hour_in)"></td>
                                <td x-text="formatTime(order.hour_out)"></td>
                                <td x-text="formatCurrency(order.total)"></td>
                                <td>
                                    <span :class="getStatusBadge(order.status)" x-text="getStatusText(order.status)"></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info me-1">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

@endsection
