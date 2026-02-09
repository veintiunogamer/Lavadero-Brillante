<!-- 
    Modal: Cambio de Estado de Pago
-->

<template x-if="showPaymentModal">
    <div x-cloak @click.self="closePaymentModal()" @keydown.escape.window="closePaymentModal()"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
    style="background: rgba(0,0,0,0.5); z-index: 9999; display: none;" x-transition>
        
        <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 650px; width: 95%;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">
                    <i class="fa-solid fa-credit-card color-blue me-2"></i>
                    Cambiar Estado de Pago
                </h4>
                <button @click="closePaymentModal()" type="button" class="btn-close"></button>
            </div>

            <!-- Info actual -->
            <div class="bg-light rounded-3 p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Cliente:</span>
                        <strong x-text="paymentModalOrder?.client?.name || 'N/A'"></strong>
                    </div>

                    <div>
                        <span class="text-muted">Placa:</span>
                        <strong x-text="paymentModalOrder?.client?.license_plaque || 'N/A'"></strong>
                    </div>
                </div>

                <div class="mt-2">
                    <span class="text-muted">Estado actual:</span>
                    <span class="badge ms-2" :class="getPaymentStatusBadge(paymentModalOrder?.payment?.status)" 
                    x-text="getPaymentStatusText(paymentModalOrder?.payment?.status)"></span>
                </div>

            </div>

            <!-- Selector de nuevo estado -->
            <div class="mb-4">

                <label class="form-label fw-bold">Nuevo Estado</label>

                <div class="d-flex flex-wrap gap-2">

                    <button type="button" class="btn flex-fill" @click="newPaymentStatus = 1"
                    :class="newPaymentStatus === 1 ? 'btn-warning' : 'btn-outline-warning'">
                        <i class="fa-solid fa-clock me-1"></i> Pendiente
                    </button>

                    <button type="button" class="btn flex-fill" @click="newPaymentStatus = 2"
                    :class="newPaymentStatus === 2 ? 'btn-info' : 'btn-outline-info'">
                        <i class="fa-solid fa-spinner me-1"></i> Parcial
                    </button>

                    <button type="button" class="btn flex-fill" @click="newPaymentStatus = 3"
                    :class="newPaymentStatus === 3 ? 'btn-success' : 'btn-outline-success'">
                        <i class="fa-solid fa-check me-1"></i> Pagado
                    </button>

                </div>

            </div>

            <!-- Monto parcial -->
            <div class="mb-4" x-show="newPaymentStatus === 2">
                <label class="form-label fw-bold">Monto Parcial</label>
                <input type="number" min="0" step="0.01" class="form-control" x-model="paymentPartialAmount"
                placeholder="Ingresa el monto pagado">
                <small class="text-muted d-block mt-1">
                    Total de la orden: <strong x-text="formatCurrency(paymentModalOrder?.total || 0)"></strong>
                </small>
            </div>

            <hr>

            <!-- Footer -->
            <div class="d-flex flex-wrap justify-content-center justify-items-center my-3 gap-2">

                <button @click="closePaymentModal()" class="btn btn-danger my-2">
                    <i class="fa-solid fa-xmark me-1"></i>&nbsp;
                    Cancelar
                </button>

                <button @click="confirmPaymentChange()" class="btn btn-primary my-2"
                :disabled="!newPaymentStatus || newPaymentStatus === paymentModalOrder?.payment?.status || changingPayment">
                    
                    <span x-show="!changingPayment">
                        <i class="fa-solid fa-check me-1"></i> Confirmar Cambio
                    </span>

                    <span x-show="changingPayment">
                        <i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...
                    </span>

                </button>

            </div>

        </div>

    </div>
</template>
