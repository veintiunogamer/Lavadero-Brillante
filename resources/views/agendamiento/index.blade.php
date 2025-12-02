@extends('layouts.base')

@section('content')

    <div id="agendamiento-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof agendamientoApp === 'function' ? agendamientoApp() : {}" x-init="init()">
        
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                <div class="col-6">
                    <h2 class="card-title mb-3">
                        <i class="fa-solid fa-calendar-check icon color-blue"></i> 
                        Agendamiento
                    </h2>
                    <p class="fw-bold">Gestión de citas y agendamientos.</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mb-4 mt-4">
                <div class="btn-group" role="group">
                    <button @click="changeTab(1)" :class="currentTab === 1 ? 'btn btn-primary' : 'btn btn-outline-primary'">
                        Pendientes
                    </button>
                    <button @click="changeTab(2)" :class="currentTab === 2 ? 'btn btn-primary' : 'btn btn-outline-primary'">
                        En Proceso
                    </button>
                    <button @click="changeTab(3)" :class="currentTab === 3 ? 'btn btn-primary' : 'btn btn-outline-primary'">
                        Terminados
                    </button>
                </div>
            </div>

            <hr>

            <!-- Loading Spinner -->
            <div x-show="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>

            <!-- Sin resultados -->
            <div x-show="!loading && orders.length === 0" class="text-center py-5">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No hay agendamientos <span x-text="currentTab === 1 ? 'pendientes' : (currentTab === 2 ? 'en proceso' : 'terminados')"></span></p>
            </div>

            <!-- Tabla de agendamientos -->
            <div x-show="!loading && orders.length > 0" class="table-responsive">
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
