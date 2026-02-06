@extends('layouts.base')

@section('content')

    <div id="reports-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;"
         x-data="typeof reportsApp === 'function' ? reportsApp() : {}"
         x-init="init()">

        <div class="card rounded-4 reports-shell p-4 w-100" style="max-width: 1400px;">

            <div class="reports-header">
                <div>
                    <h2 class="reports-title">
                        <i class="fa-solid fa-chart-line icon color-blue"></i>
                        Informes y Analíticas
                    </h2>
                    <p class="reports-subtitle fw-bold">Panel de reportes y estadísticas.</p>
                </div>

                <div class="reports-controls">

                    <select class="form-select form-select-sm"
                            x-model="salesRange"
                            @change="changeSalesRange()"
                            :disabled="activeTab !== 'sales'">
                        <option value="month">Este Mes</option>
                        <option value="week">Esta Semana</option>
                    </select>

                    <button class="btn btn-success btn-sm" @click="downloadDailyPdf()">
                        <i class="fa-solid fa-file-pdf me-1"></i>
                        Generar Cierre Diario (PDF)
                    </button>

                    <button class="btn btn-primary btn-sm" @click="downloadCurrentPdf()">
                        <i class="fa-solid fa-download me-1"></i>
                        Descargar Informe Actual
                    </button>

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

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h4 class="reports-section-title mb-1">Ventas y Facturación</h4>
                            <span class="reports-section-hint" x-text="getRangeLabel()"></span>
                        </div>
                    </div>

                    <div x-show="loadingSales" class="text-center py-4">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <div x-show="!loadingSales && getFilteredData('sales').length === 0" class="reports-empty">
                        <h5>No hay datos para el periodo seleccionado</h5>
                        <p>Prueba a seleccionar otro rango de fechas o añade nuevas citas.</p>
                    </div>

                    <div x-show="!loadingSales && getFilteredData('sales').length > 0" class="table-responsive">

                        <table class="table table-hover table-bordered align-middle reports-table">

                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Cliente</th>
                                    <th>Servicios</th>
                                    <th>Fecha</th>
                                    <th>Subtotal</th>
                                    <th>Descuento %</th>
                                    <th>Total</th>
                                    <th>Pago</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>

                            <tbody>

                                <template x-for="order in getPaginatedData('sales')" :key="order.id">

                                    <tr>
                                        <td x-text="formatOrderNumber(order)"></td>
                                        <td x-text="order.client ? order.client.name : 'N/A'"></td>
                                        <td>
                                            <template x-if="order.services && order.services.length">
                                                <div>
                                                    <template x-for="service in order.services" :key="service.id">
                                                        <div x-text="service.name"></div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!order.services || order.services.length === 0">
                                                <span class="text-muted">N/A</span>
                                            </template>
                                        </td>
                                        <td x-text="formatDate(order.creation_date)"></td>
                                        <td x-text="formatCurrency(order.subtotal)"></td>
                                        <td x-text="formatPercent(order.discount)"></td>
                                        <td x-text="formatCurrency(order.total)"></td>
                                        <td>
                                            <span class="badge" :class="getPaymentStatusBadge(order.payment?.status)" x-text="getPaymentStatusText(order.payment?.status)"></span>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getOrderStatusBadge(order.status)" x-text="getOrderStatusText(order.status)"></span>
                                        </td>
                                    </tr>

                                </template>

                            </tbody>

                        </table>

                    </div>

                    <div x-show="!loadingSales && getFilteredData('sales').length > 0" class="reports-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                        
                        <div class="text-muted">
                            Total facturado:
                            <strong x-text="formatCurrency(salesSummary.total)"></strong>
                        </div>

                        <div class="text-muted">
                            Órdenes: <span x-text="salesSummary.orders"></span>
                        </div>

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

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h4 class="reports-section-title mb-1">Clientes y Fidelización</h4>
                            <span class="reports-section-hint">Búsqueda libre por nombre, teléfono o matrícula.</span>
                        </div>

                        <div class="position-relative" style="max-width: 320px; width: 100%;">
                            <input type="text"
                                   x-model="searchTerms.clients"
                                   @input="resetPagination('clients')"
                                   class="form-control"
                                   placeholder="Buscar clientes...">
                        </div>
                    </div>

                    <div x-show="loadingClients" class="text-center py-4">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <div x-show="!loadingClients && getFilteredData('clients').length === 0" class="reports-empty">
                        <h5>No hay clientes para mostrar</h5>
                        <p>Intenta ajustar el término de búsqueda.</p>
                    </div>

                    <div x-show="!loadingClients && getFilteredData('clients').length > 0" class="table-responsive">
                        <table class="table table-hover table-bordered align-middle reports-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Matrícula</th>
                                    <th>Citas</th>
                                    <th>Total gastado</th>
                                    <th>Última visita</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="client in getPaginatedData('clients')" :key="client.id">
                                    <tr>
                                        <td x-text="client.name"></td>
                                        <td x-text="client.phone || 'N/A'"></td>
                                        <td x-text="client.license_plaque || 'N/A'"></td>
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
                <div x-show="activeTab === 'productivity'" class="reports-empty">
                    <h5>Productividad Operativa</h5>
                    <p>Este informe se definirá con más detalle en la siguiente iteración.</p>
                </div>

            </div>

        </div>

    </div>

@endsection
