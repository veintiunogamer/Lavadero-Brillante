@extends('layouts.base')

@section('content')

<div id="reports-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;"
    x-data="typeof reportsApp === 'function' ? reportsApp() : {}"
    x-init="init()">

    <div class="card rounded-4 reports-shell p-4 w-100" style="max-width: 1400px;">

        <div class="reports-header col-12 d-flex justify-content-between align-items-center mb-3 p-4">

            <div class="reports-title-section">
                <h1 class="reports-title fw-bold">
                    <i class="fa-solid fa-chart-line icon color-blue"></i>
                    INFORMES Y ESTADÍSTICAS
                </h1>
                <span class="reports-subtitle fw-bold text-muted">Panel de reportes y estadísticas.</span>
            </div>


        </div>

        <div class="reports-tabs">

            <button class="reports-tab" :class="activeTab === 'sales' ? 'active' : ''" @click="changeTab('sales')">
                <i class="fa-solid fa-dollar-sign me-2"></i> Ventas y Facturación
            </button>

            <button class="reports-tab" :class="activeTab === 'clients' ? 'active' : ''" @click="changeTab('clients')">
                <i class="fa-solid fa-users me-2"></i> Clientes y Fidelización
            </button>

            <button class="reports-tab" :class="activeTab === 'productivity' ? 'active' : ''" @click="changeTab('productivity')">
                <i class="fa-solid fa-chart-line me-2"></i> Productividad Operativa
            </button>

        </div>

        <div class="reports-panel mt-3">

            <!-- ==================== VENTAS Y FACTURACIÓN ==================== -->
            <div x-show="activeTab === 'sales'">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-2 mb-3 border-bottom pb-3">

                    <div>
                        <h2 class="reports-section-title m-0">
                            <i class="fa-solid fa-dollar-sign text-primary"></i>
                            Ventas y Facturación
                        </h2>

                        <span class="reports-section-hint fw-bold" x-text="getRangeLabel()"></span>
                    </div>

                </div>

                <div x-show="loadingSales" class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>

                <!-- ==================== FILTROS Y CONTROLES ==================== -->
                <div class="col-12 filter-section
                    d-flex flex-wrap align-items-center">

                    <div class="col-12 border-bottom mb-1 border-2 border-white">
                        <label class="fw-bold mb-1 fs-5">
                            <i class="fa-solid fa-filter text-primary me-1"></i>
                            Filtros de búsqueda
                        </label>
                    </div>


                    <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                        <label class="fw-bold">
                            <i class="fa-solid fa-clock text-primary me-1"></i>
                            Periodo
                        </label>
                        <select class="form-select form-select-lg"
                            x-model="salesRange"
                            @change="changeSalesRange()"
                            :disabled="activeTab !== 'sales'">
                            <option value="today">Hoy</option>
                            <option value="month">Este Mes</option>
                            <option value="week">Esta Semana</option>
                        </select>

                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                        <label class="fw-bold">
                            <i class="fa-solid fa-calendar-check text-primary me-1"></i>
                            Fecha de orden
                        </label>
                        <input type="date" class="form-control form-control-lg"
                            x-model="customStartDate"
                            @change="changeSalesRange('custom')"
                            :disabled="activeTab !== 'sales'">
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                        <label class="fw-bold">
                            <i class="fa-solid fa-car text-primary me-1"></i>
                            Flota
                        </label>
                        <select class="form-select form-select-lg"
                            x-model="fleetFilter"
                            @change="resetPagination('sales')"
                            :disabled="activeTab !== 'sales'">
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">
                        <label class="fw-bold">
                            <i class="fa-solid fa-exchange text-primary me-1"></i>
                            Estado de pago
                        </label>
                        <select class="form-select form-select-lg"
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
                        <label class="fw-bold">
                            <i class="fa-solid fa-credit-card text-primary me-1"></i>
                            Método de pago
                        </label>
                        <select class="form-select form-select-lg"
                            x-model="paymentMethodsFilter"
                            @change="resetPagination('sales')"
                            :disabled="activeTab !== 'sales'">
                            <option value="">Todos</option>
                            <option value="1">Efectivo</option>
                            <option value="2">TPV</option>
                            <option value="3">Transferencia</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-12 p-1 mt-2">

                        <button class="btn btn-success text-white btn-lg float-end" @click="openExportModal()">
                            <i class="fa-solid fa-download me-1"></i>
                            Exportar
                        </button>

                    </div>

                </div>

                <!-- Empty State -->
                <div x-show="!loadingSales && getFilteredData('sales').length === 0" class="citas-empty-state" style="min-height: 220px;">
                    <i class="fa-solid fa-receipt citas-empty-icon" style="color:#fde68a;"></i>
                    <p class="citas-empty-title">Sin ventas en este periodo</p>
                    <p class="citas-empty-sub">No hay datos de ventas para el rango de fechas seleccionado.<br>Prueba con otro periodo o verifica que existan citas completadas.</p>
                </div>

                <!-- Contenido -->
                <div x-show="!loadingSales && getFilteredData('sales').length > 0" class="table-responsive">

                    <!-- ==================== TABLA ==================== -->
                    <table class="table table-hover table-bordered align-middle reports-table">

                        <thead>
                            <tr>
                                <th># Orden</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Flota</th>
                                <th>Servicios</th>

                                <th>Subtotal</th>
                                <th>IVA</th>
                                <th>Descuento</th>

                                <th>Pago</th>
                                <th>Método</th>
                                <th>Estado</th>

                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>

                            <template x-for="order in getPaginatedData('sales')" :key="order.id">

                                <tr>
                                    <td x-text="formatOrderNumber(order)"></td>
                                    <td x-text="formatDate(order.creation_date)"></td>
                                    <td x-text="order.client ? order.client.name : '--'"></td>
                                    <td x-text="order.client && order.client.fleet == 1 ? 'Sí' : 'No'"></td>
                                    <td>
                                        <template x-if="order.services && order.services.length">
                                            <div>
                                                <template x-for="service in order.services" :key="service.id">
                                                    <div x-text="service.name"></div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!order.services || order.services.length === 0">
                                            <span class="text-muted">--</span>
                                        </template>
                                    </td>

                                    <td x-text="formatCurrency(order.subtotal)"></td>
                                    <td x-text="formatCurrency(order.taxes_value)"></td>
                                    <td x-text="order.discount_value ? formatCurrency(order.discount_value) : '--'"></td>

                                    <td>
                                        <span class="badge" :class="getPaymentStatusBadge(order.payment?.status)" x-text="getPaymentStatusText(order.payment?.status)"></span>
                                    </td>
                                    <td class="text-capitalize text-center">
                                        <span class="text-dark" x-text="getPaymentMethodText(order.payment?.type)"></span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getOrderStatusBadge(order.status)" x-text="getOrderStatusText(order.status)"></span>
                                    </td>

                                    <td x-text="formatCurrency(order.total)"></td>
                                </tr>

                            </template>

                        </tbody>

                    </table>

                </div>

                <div x-show="!loadingSales && getFilteredData('sales').length > 0" class="reports-footer d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <!-- Resumen & Totales -->
                    <div class="fw-bold">
                        Efectivo:
                        <strong x-text="formatCurrency(salesSummary.cash)"></strong>
                    </div>

                    <div class="fw-bold">
                        TPV:
                        <strong x-text="formatCurrency(salesSummary.card)"></strong>
                    </div>

                    <div class="fw-bold">
                        Transferencia:
                        <strong x-text="formatCurrency(salesSummary.transfer)"></strong>
                    </div>

                    <div class="fw-bold">
                        Total facturado:
                        <strong x-text="formatCurrency(salesSummary.total)"></strong>
                    </div>

                    <div class="fw-bold">
                        Órdenes: <span x-text="salesSummary.orders"></span>
                    </div>

                    <!-- paginador -->
                    <nav x-show="getTotalPages('sales') > 1">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="currentPage.sales === 1 ? 'disabled' : ''">
                                <button class="page-link" @click="goToPage('sales', currentPage.sales - 1)">«</button>
                            </li>
                            <template x-for="page in getTotalPages('sales')" :key="page">
                                <li class="page-item" :class="page === currentPage.sales ? 'active' : ''">
                                    <button class="page-link" @click="goToPage('sales', page)" x-text="page"></button>
                                </li>
                            </template>
                            <li class="page-item" :class="currentPage.sales === getTotalPages('sales') ? 'disabled' : ''">
                                <button class="page-link" @click="goToPage('sales', currentPage.sales + 1)">»</button>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>

            <!-- ==================== CLIENTES Y FIDELIZACIÓN ==================== -->
            <div x-show="activeTab === 'clients'">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-2 mb-3 border-bottom pb-3">

                    <div>
                        <h2 class="reports-section-title m-0">
                            <i class="fa-solid fa-user-check text-primary"></i>
                            Clientes y Fidelización
                        </h2>
                        <span class="reports-section-hint fw-bold">Búsqueda libre por nombre, teléfono o matrícula.</span>
                    </div>
                </div>

                <div x-show="loadingClients" class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>

                <div class="col-12 filter-section
                    d-flex flex-wrap align-items-center">

                    <div class="col-12 border-bottom mb-1 border-2 border-white">
                        <label class="fw-bold mb-1 fs-5">
                            <i class="fa-solid fa-filter text-primary me-1"></i>
                            Filtros de búsqueda
                        </label>
                    </div>


                    <div class="col-3 p-1 mt-2">
                        <label class="fw-bold">
                            <i class="fa-solid fa-magnifying-glass text-primary me-1"></i>
                            Búsqueda global
                        </label>
                        <input type="text"
                            x-model="searchTerms.clients"
                            @input="resetPagination('clients')"
                            class="form-control form-control-lg"
                            placeholder="Buscar clientes...">
                    </div>

                    <div class="col-3 p-1 mt-2">
                        <label class="fw-bold">
                            <i class="fa-solid fa-car text-primary me-1"></i>
                            Flota
                        </label>
                        <select class="form-select form-select-lg"
                            x-model="fleetFilter"
                            @change="resetPagination('clients')">
                            <option value=" 1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-6 p-1 mt-2">

                        <button class="btn btn-primary text-white btn-lg float-end" @click="openExportModal()">
                            <i class="fa-solid fa-download me-1"></i>
                            Exportar
                        </button>

                    </div>

                </div>

                <div x-show="!loadingClients && getFilteredData('clients').length === 0" class="citas-empty-state" style="min-height: 220px;">
                    <i class="fa-solid fa-users citas-empty-icon" style="color:#86efac;"></i>
                    <p class="citas-empty-title">Sin clientes para mostrar</p>
                    <p class="citas-empty-sub">No hay clientes que coincidan con la búsqueda.<br>Intenta ajustar el término o verifica que existan clientes registrados.</p>
                </div>

                <div x-show="!loadingClients && getFilteredData('clients').length > 0" class="table-responsive">
                    <table class="table table-hover table-bordered align-middle reports-table">
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
                            <template x-for="client in getPaginatedData('clients')" :key="client.id">
                                <tr>
                                    <td x-text="client.name"></td>
                                    <td x-text="client.phone || '--'"></td>
                                    <td x-text="client.license_plaque || '--'"></td>
                                    <td x-text="client.brand || '--'"></td>
                                    <td x-text="client.fleet == 1 ? 'Sí' : 'No'"></td>
                                    <td x-text="client.orders_count"></td>
                                    <td x-text="formatCurrency(client.total_spent)"></td>
                                    <td x-text="formatDate(client.last_order_date)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="!loadingClients && getFilteredData('clients').length > 0" class="reports-footer d-flex justify-content-end">
                    <nav x-show="getTotalPages('clients') > 1">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="currentPage.clients === 1 ? 'disabled' : ''">
                                <button class="page-link" @click="goToPage('clients', currentPage.clients - 1)">«</button>
                            </li>
                            <template x-for="page in getTotalPages('clients')" :key="page">
                                <li class="page-item" :class="page === currentPage.clients ? 'active' : ''">
                                    <button class="page-link" @click="goToPage('clients', page)" x-text="page"></button>
                                </li>
                            </template>
                            <li class="page-item" :class="currentPage.clients === getTotalPages('clients') ? 'disabled' : ''">
                                <button class="page-link" @click="goToPage('clients', currentPage.clients + 1)">»</button>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>

            <!-- ==================== PRODUCTIVIDAD OPERATIVA ==================== -->
            <div x-show="activeTab === 'productivity'" class="citas-empty-state" style="min-height: 220px;">
                <i class="fa-solid fa-chart-line citas-empty-icon" style="color:#bfdbfe;"></i>
                <p class="citas-empty-title">Productividad Operativa</p>
                <p class="citas-empty-sub fw-bold my-3">Este informe estará disponible en la próxima actualización</p>
            </div>

        </div>

    </div>

    <!-- Modal Exportar -->
    <div x-cloak x-show="showExportModal" x-transition.opacity class="reports-export-backdrop" @click="closeExportModal()" aria-hidden="true">

        <div class="reports-export-modal" @click.stop>

            <div class="reports-export-header border-bottom pb-3">
                <h4 class="mb-0">
                    <i class="fa-solid fa-download text-primary"></i>&nbsp;
                    Exportar informe
                </h4>
                <button class="btn-close" type="button" aria-label="Cerrar" @click="closeExportModal()"></button>
            </div>

            <div class="reports-export-options">

                <button class="reports-export-option reports-export-option--pdf" @click="downloadCurrent('pdf')">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>PDF</span>
                </button>

                <button class="reports-export-option reports-export-option--excel" @click="downloadCurrent('excel')">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Excel</span>
                </button>

            </div>

        </div>

    </div>

</div>

@endsection