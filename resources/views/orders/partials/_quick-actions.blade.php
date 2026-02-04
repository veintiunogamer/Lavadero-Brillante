<!-- 
    Modal: Vista Rápida de Orden
    Se usa con Alpine.js para mostrar detalles de una orden
-->

<template x-if="showQuickViewModal">

    <div x-cloak @click.self="closeQuickViewModal()" @keydown.escape.window="closeQuickViewModal()"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
    style="background: rgba(0,0,0,0.5); z-index: 9999; display: none;" x-transition>

        <div class="bg-white rounded-4 shadow-lg" style="max-width: 800px; width: 95%; max-height: 90vh; overflow-y: auto;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom bg-light rounded-top-4">

                <h4 class="mb-0 fw-bold">
                    <i class="fa fa-list color-blue me-2"></i>
                    Detalle de Orden <span class="text-primary" x-text="selectedOrder ? (selectedOrder.consecutive_serial + ' - ' + selectedOrder.consecutive_number) : ''"></span>
                </h4>

                <div>
                    <span class="badge fs-6" :class="getStatusBadge(selectedOrder?.status)" 
                    x-text="getStatusText(selectedOrder?.status)"></span>
                </div>

                <button @click="closeQuickViewModal()" type="button" class="btn-close"></button>

            </div>

            <!-- Body -->
            <div class="p-4" x-show="selectedOrder">

                <!-- Info del Cliente -->
                <div class="row mb-4">

                    <div class="col-12">

                        <h5 class="fw-bold border-bottom pb-2">
                            <i class="fa-solid fa-user me-2 text-primary"></i>
                            Cliente
                        </h5>

                    </div>

                    <div class="col-md-3">
                        <label class="fw-bold small">Nombre</label>
                        <p class="mb-2" x-text="selectedOrder?.client?.name || 'N/A'"></p>
                    </div>

                    <div class="col-md-3">
                        <label class="fw-bold small">Teléfono</label>
                        <p class="mb-2" x-text="selectedOrder?.client?.phone || 'N/A'"></p>
                    </div>

                    <div class="col-md-3">
                        <label class="fw-bold small">Matrícula</label>
                        <p class="mb-2" x-text="selectedOrder?.client?.license_plaque || 'N/A'"></p>
                    </div>

                </div>

                <!-- Info de Servicios -->
                <div class="row mb-4">

                    <div class="col-12">
                        <h5 class="fw-bold border-bottom pb-2">
                            <i class="fa-solid fa-list-check me-2 text-primary"></i>
                            Servicios
                        </h5>
                    </div>

                    <div class="col-12">

                        <div class="table-responsive">

                            <table class="table table-sm table-bordered">

                                <thead class="table-dark">

                                    <tr>
                                        <th>Servicio</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <template x-for="service in (selectedOrder?.services || [])" :key="service.id">
                                        <tr>
                                            <td x-text="service.name"></td>
                                            <td class="text-center" x-text="service.pivot?.quantity || 1"></td>
                                            <td class="text-end" x-text="formatCurrency(service.pivot?.total || service.value)"></td>
                                        </tr>
                                    </template>

                                </tbody>

                                <tfoot class="table-light">

                                    <tr>
                                        <td colspan="2" class="fw-bold">Subtotal:</td>
                                        <td class="text-end" x-text="formatCurrency(selectedOrder?.subtotal || 0)"></td>
                                    </tr>

                                    <tr x-show="selectedOrder?.discount > 0">
                                        <td colspan="2" class="fw-bold text-danger">Descuento:</td>
                                        <td class="text-end text-danger" x-text="'-' + formatCurrency((selectedOrder?.subtotal * selectedOrder?.discount / 100) || 0)"></td>
                                    </tr>

                                    <tr>
                                        <td colspan="2" class="fw-bold fs-5">Total:</td>
                                        <td class="text-end fw-bold fs-5 text-success" x-text="formatCurrency(selectedOrder?.total || 0)"></td>
                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- Info de Horario y Pago -->
                <div class="row mb-4">

                    <div class="col-md-12">

                        <h5 class="border-bottom pb-2">
                            <i class="fa-solid fa-clock me-2 text-primary"></i>
                            Horario
                        </h5>

                        <div class="row">

                            <div class="col-3">
                                <label class="fw-bold small">Fecha</label>
                                <p class="mb-2" x-text="formatDate(selectedOrder?.creation_date)"></p>
                            </div>

                            <div class="col-3">
                                <label class="fw-bold small">Entrada</label>
                                <p class="mb-2" x-text="formatTime(selectedOrder?.hour_in)"></p>
                            </div>

                            <div class="col-3">
                                <label class="fw-bold small">Salida</label>
                                <p class="mb-2" x-text="formatTime(selectedOrder?.hour_out)"></p>
                            </div>

                        </div>

                    </div>

                    <div class="col-md-12">

                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-credit-card me-2 text-primary"></i>
                            Pago
                        </h5>

                        <div class="row" x-for="payment in (selectedOrder?.payments || [])" :key="payment.id">

                            <div class="col-3">
                                <label class="fw-bold small">Estado</label>
                                <p class="mb-2">
                                    <span class="badge" :class="getPaymentStatusBadge(payment.status)" 
                                        x-text="getPaymentStatusText(payment.status)"></span>
                                </p>
                            </div>

                            <div class="col-3">
                                <label class="fw-bold small">Método</label>
                                <span class="mb-2" x-text="getPaymentMethodText(payment.type)"></span>
                            </div>

                            <div class="col-3">
                                <label class="fw-bold small">Total Pagado</label>
                                <span class="mb-2 text-success" x-text="formatCurrency(payment.total || 0)"></span>
                            </div>

                            <div class="col-3" x-show="selectedOrder?.partial_payment > 0">
                                <label class="fw-bold small">Abono Parcial</label>
                                <span class="mb-2 text-primary" x-text="formatCurrency(selectedOrder?.partial_payment || 0)"></span>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Notas -->
                <div class="row" x-show="selectedOrder?.vehicle_notes || selectedOrder?.order_notes || selectedOrder?.extra_notes">
                    
                    <div class="col-12">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-sticky-note me-2 text-primary"></i>
                            Notas
                        </h5>
                    </div>

                    <div class="col-md-4" x-show="selectedOrder?.vehicle_notes">
                        <label class="fw-bold small">Vehículo</label>
                        <p class="small" x-text="selectedOrder?.vehicle_notes"></p>
                    </div>

                    <div class="col-md-4" x-show="selectedOrder?.extra_notes">
                        <label class="fw-bold small">Adicionales</label>
                        <p class="small" x-text="selectedOrder?.extra_notes"></p>
                    </div>

                    <div class="col-md-12" x-show="selectedOrder?.order_notes">
                        <label class="fw-bold small">Orden</label>
                        <p class="small" x-text="selectedOrder?.order_notes"></p>
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="col-12 p-4 border-top bg-light rounded-bottom-4">

                <div class="d-flex gap-2 flex-wrap align-items-center justify-content-center">

                    <button @click="closeQuickViewModal()" class="btn btn-danger">
                        <i class="fa-solid fa-times me-1"></i> Cerrar
                    </button>

                    <a :href="'/orders/' + selectedOrder?.id + '/edit'" class="btn btn-warning">
                        <i class="fa-solid fa-edit me-1"></i> Editar
                    </a>

                    <button @click="printOrder(selectedOrder?.id)" class="btn btn-info">
                        <i class="fa-solid fa-print me-1"></i> Imprimir
                    </button>

                </div>

            </div>

        </div>

    </div>

</template>