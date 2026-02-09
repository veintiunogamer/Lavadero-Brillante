@extends('layouts.base')

@section('content')

    <div id="agendamiento-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof agendamientoApp === 'function' ? agendamientoApp() : {}" x-init="init()">
        
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

            <div class="col-12 d-flex justify-content-between align-items-center mb-3 p-4">
                
                <div class="col-6">
                    <h3 class="card-title mb-3">
                        <i class="fa-solid fa-calendar-check icon color-blue"></i> 
                        Agendamiento
                    </h3>
                    <p class="fw-bold small text-muted">Gestión de citas y agendamientos.</p>
                </div>

                <div class="col-6 d-flex justify-content-end">
                    <div class="position-relative" style="max-width: 350px; width: 100%;">
                        <input type="text"
                               x-model="searchTerm"
                               @input="resetPagination()"
                               class="form-control pe-5"
                               placeholder="Buscar agendamientos...">
                        <i class="fa-solid fa-search position-absolute"
                           style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                    </div>
                </div>

            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs p-4" id="ordersTabs" role="tablist">

                <li class="nav-item" role="presentation">
                    <button :class="currentTab === 1 ? 'nav-link active' : 'nav-link'" id="pending-tab" @click="changeTab(1)" type="button" role="tab" aria-controls="pending" :aria-selected="currentTab === 1"><i class="fa-solid fa-clock me-2"></i>Pendientes</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button :class="currentTab === 2 ? 'nav-link active' : 'nav-link'" id="in-process-tab" @click="changeTab(2)" type="button" role="tab" aria-controls="in-process" :aria-selected="currentTab === 2"><i class="fa-solid fa-spinner me-2"></i>En Proceso</button>
                </li>
                
                <li class="nav-item" role="presentation">
                    <button :class="currentTab === 3 ? 'nav-link active' : 'nav-link'" id="completed-tab" @click="changeTab(3)" type="button" role="tab" aria-controls="completed" :aria-selected="currentTab === 3"><i class="fa-solid fa-check-circle me-2"></i>Terminados</button>
                </li>

            </ul>

            <!-- Loading Spinner -->
            <div x-show="loading" class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>

            <!-- Sin resultados -->
            <div x-show="!loading && getFilteredOrders().length === 0" class="text-center py-5 p-4">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted" x-text="searchTerm ? 'No se encontraron resultados.' : ('No hay agendamientos ' + (currentTab === 1 ? 'pendientes' : (currentTab === 2 ? 'en proceso' : 'terminados')))"></p>
            </div>

            <!-- Tabla de agendamientos -->
            <div x-show="!loading && getFilteredOrders().length > 0" class="table-responsive p-4">

                <table class="table table-striped table-bordered align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Placa</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Total</th>
                            <th>Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <template x-for="(order, index) in getPaginatedOrders()" :key="index">
                            <tr>
                                <td x-text="order.client ? order.client.name : 'N/A'"></td>
                                <td x-text="order.client ? order.client.license_plaque : 'N/A'"></td>
                                <td>
                                    <template x-for="service in order.services" :key="service.id">
                                        <div x-text="service.name"></div>
                                    </template>
                                </td>
                                <td x-text="formatDate(order.creation_date)"></td>
                                <td x-text="formatTime(order.hour_in)"></td>
                                <td x-text="formatTime(order.hour_out)"></td>
                                <td x-text="formatCurrency(order.total)"></td>
                                <td>
                                    <span class="badge" :class="getPaymentStatusBadge(order.payment?.status)" x-text="getPaymentStatusText(order.payment?.status)"></span>
                                </td>
                                <td>
                                    <span :class="getStatusBadge(order.status)" x-text="getStatusText(order.status)"></span>
                                </td>
                                <td>
                                    
                                    <button class="btn btn-sm btn-info me-1" @click="openQuickView(order)" title="Ver detalles">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button class="btn btn-sm btn-warning me-1" @click="openStatusTypeModal(order)" title="Cambiar estado"
                                            x-show="order.status !== 3 || order.payment?.status !== 3">
                                        <i class="fa-solid fa-exchange-alt"></i>
                                    </button>

                                    <a :href="'/orders/' + order.id + '/edit'" class="btn btn-sm btn-primary" title="Editar" x-show="order.status !== 3">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>

                                </td>
                            </tr>

                        </template>

                    </tbody>

                </table>

                <!-- Paginador -->
                <div x-show="getTotalPages() > 1" class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Página <span x-text="currentPage"></span> de <span x-text="getTotalPages()"></span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="currentPage === 1 ? 'disabled' : ''">
                                <button class="page-link" @click="goToPage(currentPage - 1)">«</button>
                            </li>
                            <template x-for="page in getTotalPages()" :key="page">
                                <li class="page-item" :class="page === currentPage ? 'active' : ''">
                                    <button class="page-link" @click="goToPage(page)" x-text="page"></button>
                                </li>
                            </template>
                            <li class="page-item" :class="currentPage === getTotalPages() ? 'disabled' : ''">
                                <button class="page-link" @click="goToPage(currentPage + 1)">»</button>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>

        </div>

        <!-- Modales -->
        @include('orders.partials._quick-actions')
        @include('orders.modals._change-status')
        @include('orders.modals._select-status-type')
        @include('orders.modals._change-payment')

    </div>

@endsection
