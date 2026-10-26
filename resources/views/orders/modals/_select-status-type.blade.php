<!-- Modal: Selector del tipo de cambio de estado -->
<template x-if="showStatusTypeModal">
    <div x-cloak @click.self="closeStatusTypeModal()" @keydown.escape.window="closeStatusTypeModal()"
        class="order-status-overlay" x-transition.opacity>

        <section class="order-status-modal order-status-modal--selector" @click.stop>
            <header class="order-status-header">
                <div class="order-status-heading">
                    <span class="order-status-icon"><i class="fa-solid fa-sliders"></i></span>
                    <div>
                        <h2>Cambiar estado</h2>
                        <p>Selecciona qué parte de la orden quieres actualizar.</p>
                    </div>
                </div>
                <button @click="closeStatusTypeModal()" type="button" class="order-status-close" aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <div class="order-status-body">
                <section class="order-status-context order-status-context--compact">
                    <div class="order-status-context__client">
                        <label>Cliente</label>
                        <strong x-text="statusTypeOrder?.client?.name || '--'"></strong>
                    </div>
                    <div class="order-status-context__current">
                        <label>Estado de la orden</label>
                        <span class="badge" :class="getStatusBadge(statusTypeOrder?.status)"
                            x-text="getStatusText(statusTypeOrder?.status)"></span>
                    </div>
                </section>

                <div class="order-status-type-options">
                    <button type="button" class="order-status-type-option order-status-type-option--order"
                        @click="openOrderStatusFromType()" x-show="statusTypeOrder?.status !== 3">
                        <span class="order-status-type-option__icon"><i class="fa-solid fa-clipboard-check"></i></span>
                        <span class="order-status-type-option__copy">
                            <strong>Estado de la orden</strong>
                            <small>Gestiona si está pendiente, en proceso, terminada o cancelada.</small>
                        </span>
                        <i class="fa-solid fa-chevron-right order-status-type-option__arrow"></i>
                    </button>

                    <button type="button" class="order-status-type-option order-status-type-option--payment"
                        @click="openPaymentStatusFromType()" x-show="statusTypeOrder?.payment?.status !== 3">
                        <span class="order-status-type-option__icon"><i class="fa-regular fa-credit-card"></i></span>
                        <span class="order-status-type-option__copy">
                            <strong>Estado de pago</strong>
                            <small>Actualiza el pago como pendiente, parcial o pagado.</small>
                        </span>
                        <i class="fa-solid fa-chevron-right order-status-type-option__arrow"></i>
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
