<!-- Modal: detalle de una orden de lavado -->
<template x-if="showQuickViewModal">
    <div x-cloak @click.self="closeQuickViewModal()" @keydown.escape.window="closeQuickViewModal()"
        class="order-detail-overlay" x-transition>

        <div class="order-detail-modal" @click.stop>
            <header class="order-detail-header">
                <div class="order-detail-heading">
                    <span class="order-detail-icon"><i class="fa-regular fa-clipboard"></i></span>
                    <div class="order-detail-title-wrap">
                        <h2>
                            Detalle de Orden
                            <span class="order-detail-number" x-text="selectedOrder ? (selectedOrder.consecutive_number && selectedOrder.consecutive_serial ? (selectedOrder.consecutive_number + ' - ' + selectedOrder.consecutive_serial) : ('#' + selectedOrder.id)) : ''"></span>
                            <span class="badge" :class="getStatusBadge(selectedOrder?.status)" x-text="getStatusText(selectedOrder?.status)"></span>
                        </h2>
                        <p>Resumen operativo del servicio de lavado</p>
                    </div>
                </div>
                <button @click="closeQuickViewModal()" type="button" class="order-detail-close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
            </header>

            <div class="order-detail-body" x-show="selectedOrder">
                <section class="order-detail-card order-detail-client">
                    <div class="order-detail-section-heading">
                        <span class="order-detail-section-icon"><i class="fa-solid fa-user"></i></span>
                        <div>
                            <h3>Información del cliente</h3>
                            <p>Datos para identificar la orden y el vehículo</p>
                        </div>
                        <span class="order-detail-vehicle" x-show="selectedOrder?.client?.brand">
                            <i class="fa-solid fa-car-side"></i>
                            <span x-text="selectedOrder?.client?.brand"></span>
                        </span>
                    </div>

                    <div class="order-detail-client-grid">
                        <div class="order-detail-data">
                            <label>Nombre</label>
                            <p x-text="selectedOrder?.client?.name || '--'"></p>
                        </div>
                        <div class="order-detail-data">
                            <label>Matrícula</label>
                            <div x-html="licensePlate(selectedOrder?.client?.license_plaque)"></div>
                            <span x-show="!selectedOrder?.client?.license_plaque && selectedOrder?.client?.temp_registration" class="badge" style="background:#fff1a8;color:#17324d" x-text="'Registro: ' + selectedOrder.client.temp_registration"></span>
                        </div>
                        <div class="order-detail-data">
                            <label>Modelo</label>
                            <p x-text="selectedOrder?.client?.brand || '--'"></p>
                        </div>
                        <div class="order-detail-data">
                            <label>Flota</label>
                            <p x-text="selectedOrder?.client?.fleet ? 'Sí' : 'No'"></p>
                        </div>
                        <div class="order-detail-data">
                            <label>Teléfono</label>
                            <p x-text="selectedOrder?.client?.phone || '--'"></p>
                        </div>
                    </div>
                </section>

                <div class="order-detail-main-grid">
                    <section class="order-detail-card order-detail-services">
                        <div class="order-detail-section-heading">
                            <span class="order-detail-section-icon"><i class="fa-solid fa-layer-group"></i></span>
                            <div>
                                <h3>Servicios Adquiridos</h3>
                                <p>Detalle de conceptos e importes</p>
                            </div>
                        </div>

                        <div class="table-responsive order-detail-table-wrap">
                            <table class="table order-detail-table">
                                <thead>
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
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Subtotal:</td>
                                        <td class="text-end" x-text="formatCurrency(selectedOrder?.subtotal || 0)"></td>
                                    </tr>
                                    <tr x-show="selectedOrder?.discount_value > 0">
                                        <td colspan="2" class="order-detail-discount">Descuento:</td>
                                        <td class="text-end order-detail-discount" x-text="'-' + formatCurrency(selectedOrder?.discount_value || 0)"></td>
                                    </tr>
                                    <tr x-show="selectedOrder?.taxes_value > 0">
                                        <td colspan="2" class="order-detail-tax">IVA (21%):</td>
                                        <td class="text-end order-detail-tax" x-text="'+' + formatCurrency(selectedOrder?.taxes_value || 0)"></td>
                                    </tr>
                                    <tr class="order-detail-total">
                                        <td colspan="2">Total:</td>
                                        <td class="text-end" x-text="formatCurrency(selectedOrder?.total || 0)"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </section>

                    <div class="order-detail-side-column">
                        <section class="order-detail-card order-detail-schedule">
                            <div class="order-detail-section-heading">
                                <span class="order-detail-section-icon"><i class="fa-regular fa-clock"></i></span>
                                <div>
                                    <h3>Horario del servicio</h3>
                                    <p>Cita programada</p>
                                </div>
                            </div>
                            <div class="order-detail-schedule-data">
                                <div class="order-detail-data">
                                    <label>Fecha programada</label>
                                    <p x-text="formatDate(selectedOrder?.creation_date)"></p>
                                </div>
                                <div class="order-detail-data">
                                    <label>Hora de entrada</label>
                                    <p x-text="formatTime(selectedOrder?.hour_in)"></p>
                                </div>
                                <div class="order-detail-data">
                                    <label>Hora de salida</label>
                                    <p x-text="formatTime(selectedOrder?.hour_out)"></p>
                                </div>
                            </div>
                        </section>

                        <section class="order-detail-card order-detail-notes" x-show="selectedOrder?.vehicle_notes || selectedOrder?.order_notes || selectedOrder?.extra_notes">
                            <div class="order-detail-section-heading">
                                <span class="order-detail-section-icon"><i class="fa-regular fa-comment-dots"></i></span>
                                <div><h3>Notas</h3></div>
                            </div>
                            <div class="order-detail-notes-list">
                                <div x-show="selectedOrder?.order_notes">
                                    <label>Orden</label>
                                    <p x-text="selectedOrder?.order_notes"></p>
                                </div>
                                <div x-show="selectedOrder?.vehicle_notes">
                                    <label>Vehículo</label>
                                    <p x-text="selectedOrder?.vehicle_notes"></p>
                                </div>
                                <div x-show="selectedOrder?.extra_notes">
                                    <label>Adicionales</label>
                                    <p x-text="selectedOrder?.extra_notes"></p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <section class="order-detail-card order-detail-payment">
                    <div class="order-detail-section-heading">
                        <span class="order-detail-section-icon order-detail-section-icon--green"><i class="fa-regular fa-credit-card"></i></span>
                        <div>
                            <h3>Información de pago</h3>
                            <p>Estado y registro del cobro</p>
                        </div>
                    </div>

                    <template x-if="(selectedOrder?.payments || []).length === 0">
                        <div class="order-detail-empty-payment">No hay pagos registrados.</div>
                    </template>
                    <template x-for="payment in (selectedOrder?.payments || [])" :key="payment.id">
                        <div class="order-detail-payment-grid">
                            <div class="order-detail-data">
                                <label>Estado</label>
                                <p><span class="badge" :class="getPaymentStatusBadge(payment.status)" x-text="getPaymentStatusText(payment.status)"></span></p>
                            </div>
                            <div class="order-detail-data">
                                <label>Método</label>
                                <p x-text="getPaymentMethodText(payment.type)"></p>
                            </div>
                            <div class="order-detail-data">
                                <label>Período de pago</label>
                                <p><span class="badge" :class="selectedOrder?.payment_period === 2 ? 'bg-info text-white' : 'bg-light text-dark'" x-text="selectedOrder?.payment_period === 2 ? 'Mensual' : 'Único'"></span></p>
                            </div>
                            <div class="order-detail-data">
                                <label>Total pagado</label>
                                <p class="order-detail-paid" x-text="formatCurrency(payment.total || 0)"></p>
                            </div>
                            <div class="order-detail-data" x-show="selectedOrder?.partial_payment > 0">
                                <label>Abono parcial</label>
                                <p class="order-detail-partial" x-text="formatCurrency(selectedOrder?.partial_payment || 0)"></p>
                            </div>
                        </div>
                    </template>
                </section>
            </div>

            <footer class="order-detail-footer">
                <button @click="closeQuickViewModal()" class="btn btn-danger"><i class="fa-solid fa-xmark me-1"></i>Cerrar</button>
                <a :href="'/orders/' + selectedOrder?.id + '/edit'" class="btn btn-primary" x-show="selectedOrder?.status !== 3"><i class="fa-solid fa-pen-to-square me-1"></i>Editar</a>
                <button @click="printOrder(selectedOrder?.id)" class="btn btn-warning"><i class="fa-solid fa-print me-1"></i>Imprimir</button>
            </footer>
        </div>
    </div>
</template>
