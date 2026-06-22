@extends('layouts.base')

@section('content')

<div id="agendamiento-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" x-data="typeof agendamientoApp === 'function' ? agendamientoApp() : {}" x-init="init()">

    <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

        <div class="col-12 d-flex justify-content-between align-items-center mb-3 p-4">

            <div class="col-6">
                <h1 class="m-0 fw-bold">
                    <i class="fa-solid fa-calendar-check icon color-blue"></i>
                    AGENDAMIENTO
                </h1>
                <span class="fw-bold text-muted">Gestión de citas y agendamientos.</span>
            </div>

        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs p-2" id="ordersTabs" role="tablist">

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

        <!-- Filtro de búsqueda -->
        <div class="col-12 filter-section 
            d-flex flex-wrap align-items-center">

            <div class="col-12 border-bottom mb-1 border-2 border-white">
                <label class="fw-bold mb-1 fs-5">
                    <i class="fa-solid fa-filter text-primary me-1"></i>
                    Filtros de búsqueda
                </label>
            </div>

            <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                <label class="fw-bold mb-1">
                    <i class="fa-solid fa-magnifying-glass text-primary me-1"></i>
                    Búsqueda global
                </label>
                <input type="text"
                    x-model="searchTerm"
                    @input="resetPagination()"
                    class="input form-control pe-5"
                    placeholder="Buscar agendamientos...">
            </div>

            <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                <label class="fw-bold mb-1">
                    <i class="fa-solid fa-calendar-check text-primary me-1"></i>
                    Fecha de orden
                </label>
                <input type="date"
                    x-model="searchDate"
                    @change="resetPagination()"
                    class="input form-control">
            </div>

            <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                <label class="fw-bold mb-1">
                    <i class="fa-solid fa-exchange text-primary me-1"></i>
                    Estados de pago
                </label>
                <select class="input form-select form-select-lg"
                    x-model="paymentStatusFilter"
                    @change="resetPagination('sales')"
                    :disabled="activeTab !== 'sales'">
                    <option value="">Todos</option>
                    <option value="1">Pendiente</option>
                    <option value="2">Parcial</option>
                    <option value="3">Pagado</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                <label class="fw-bold mb-1">
                    <i class="fa-solid fa-credit-card text-primary me-1"></i>
                    Método de pago
                </label>
                <select x-model="searchPaymentType" @change="resetPagination()"
                    class="input form-select">
                    <option value="">Todos</option>
                    <option value="1">Efectivo</option>
                    <option value="2">TPV</option>
                    <option value="3">Transferencias</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                <label class="fw-bold mb-1">
                    <i class="fa-solid fa-car text-primary me-1"></i>
                    Flota
                </label>
                <select x-model="searchIsFleet" @change="resetPagination()"
                    class="input form-select">
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
            </div>

        </div>

        <!-- Loading Spinner -->
        <div x-show="loading" class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>


        <!-- Sin resultados -->
        <div x-show="!loading && getFilteredOrders().length === 0" class="citas-empty-state">

            <template x-if="searchTerm">
                <div>
                    <i class="fa-solid fa-magnifying-glass citas-empty-icon" style="color:#93c5fd;"></i>
                    <p class="citas-empty-title">Sin resultados</p>
                    <p class="citas-empty-sub">No se encontraron agendamientos para <strong x-text="'&quot;' + searchTerm + '&quot;'"></strong>.<br>Intenta con otro término de búsqueda.</p>
                </div>
            </template>

            <template x-if="!searchTerm && currentTab === 1">
                <div>
                    <i class="fa-solid fa-clock citas-empty-icon" style="color:#fde68a;"></i>
                    <p class="citas-empty-title">Sin pendientes</p>
                    <p class="citas-empty-sub">No hay agendamientos pendientes en este momento.<br>Los nuevos agendamientos aparecerán aquí.</p>
                </div>
            </template>

            <template x-if="!searchTerm && currentTab === 2">
                <div>
                    <i class="fa-solid fa-spinner citas-empty-icon" style="color:#86efac;"></i>
                    <p class="citas-empty-title">Nada en proceso</p>
                    <p class="citas-empty-sub">No hay agendamientos en proceso actualmente.<br>Los agendamientos activos aparecerán aquí.</p>
                </div>
            </template>

            <template x-if="!searchTerm && currentTab === 3">
                <div>
                    <i class="fa-solid fa-circle-check citas-empty-icon" style="color:#bfdbfe;"></i>
                    <p class="citas-empty-title">Sin terminados</p>
                    <p class="citas-empty-sub">Los agendamientos finalizados aparecerán aquí<br>una vez sean completados o cancelados.</p>
                </div>
            </template>

        </div>

        <!-- Tabla de agendamientos -->
        <div x-show="!loading && getFilteredOrders().length > 0" class="table-responsive p-4">

            <table class="table table-striped table-bordered align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Flota</th>
                        <th>Servicio</th>
                        <th>Pago</th>
                        <th>Metodo</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <template x-for="(order, index) in getPaginatedOrders()" :key="index">
                        <tr>
                            <td x-html="
                                `${order.date}
                                <br>
                                <span class='badge bg-success'>${formatTime(order.hour_in)}</span> -
                                <span class='badge bg-danger'>${formatTime(order.hour_out)}</span>`
                            ">
                            <td x-html="order.client ? order.client.name + '<br>' + order.client.license_plaque : '--'"></td>
                            <td x-text="order.client && order.client.fleet ? 'Sí' : 'No'"></td>
                            <td>
                                <template x-for="service in order.services" :key="service.id">
                                    <div x-text="service.name"></div>
                                </template>
                            </td>
                            <td>
                                <span class="badge" :class="getPaymentStatusBadge(order.payment?.status)" x-text="getPaymentStatusText(order.payment?.status)"></span>
                            </td>
                            <td x-text="getPaymentMethodText(order.payment?.type)"></td>
                            <td>
                                <span :class="getStatusBadge(order.status)" x-text="getStatusText(order.status)"></span>
                            </td>

                            <td x-text="formatCurrency(order.total)"></td>

                            <td class="text-center">

                                <div class="btn-group btn-group-md">

                                    <button class="btn btn-info" @click="openQuickView(order)" title="Ver detalles">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button class="btn btn-success" @click="openStatusTypeModal(order)" title="Cambiar estado"
                                        x-show="order.status !== 3 || order.payment?.status !== 3">
                                        <i class="fa-solid fa-exchange-alt"></i>
                                    </button>

                                    <a :href="'/orders/' + order.id + '/edit'" class="btn btn-primary" title="Editar" x-show="order.status !== 3">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>

                                    <button class="btn btn-warning" @click="printOrder(order.id)" title="Imprimir orden">
                                        <i class="fa-solid fa-print"></i>
                                    </button>

                                </div>

                            </td>
                        </tr>

                    </template>

                </tbody>

            </table>

            <!-- Paginador -->
            <div x-show="getTotalPages() > 1" class="d-flex justify-content-between align-items-center mt-3">
                <div class="badge bg-primary text-light p-2 col-1">
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