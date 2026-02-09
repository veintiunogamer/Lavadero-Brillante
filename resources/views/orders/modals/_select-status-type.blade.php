<!-- 
    Modal: Selector de tipo de cambio (Estado de orden vs Estado de pago)
-->

<template x-if="showStatusTypeModal">
    <div x-cloak @click.self="closeStatusTypeModal()" @keydown.escape.window="closeStatusTypeModal()"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
    style="background: rgba(0,0,0,0.5); z-index: 9999; display: none;" x-transition>

        <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 480px; width: 95%;">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 fw-bold">
                    <i class="fa-solid fa-sliders color-blue me-2"></i>
                    Cambiar estado
                </h4>
                <button @click="closeStatusTypeModal()" type="button" class="btn-close"></button>
            </div>

            <p class="text-muted mb-4">Selecciona el tipo de estado que deseas actualizar.</p>

            <div class="d-flex gap-3 flex-wrap">
                <button class="btn btn-outline-primary flex-fill" @click="openOrderStatusFromType()"
                        x-show="statusTypeOrder?.status !== 3">
                    <i class="fa-solid fa-clipboard-check me-1"></i>
                    Estado de la orden
                </button>
                <button class="btn btn-outline-success flex-fill" @click="openPaymentStatusFromType()"
                        x-show="statusTypeOrder?.payment?.status !== 3">
                    <i class="fa-solid fa-credit-card me-1"></i>
                    Estado de pago
                </button>
            </div>

        </div>
    </div>
</template>
