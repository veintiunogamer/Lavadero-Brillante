<!-- Modal: Cambio de estado de una orden -->
<template x-if="showStatusModal">
    <div x-cloak @click.self="closeStatusModal()" @keydown.escape.window="closeStatusModal()"
        class="order-status-overlay" x-transition.opacity>

        <section class="order-status-modal order-status-modal--editor" @click.stop>
            <header class="order-status-header">
                <div class="order-status-heading">
                    <span class="order-status-icon"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
                    <div>
                        <h2>Cambiar estado de orden</h2>
                        <p>Selecciona el nuevo estado del servicio.</p>
                    </div>
                </div>
                <button @click="closeStatusModal()" type="button" class="order-status-close" aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <div class="order-status-body">
                <section class="order-status-context">
                    <div class="order-status-context__client">
                        <label>Cliente</label>
                        <strong x-text="statusModalOrder?.client?.name || '--'"></strong>
                    </div>
                    <div class="order-status-context__plate">
                        <label>Placa</label>
                        <div x-html="licensePlate(statusModalOrder?.client?.license_plaque)"></div>
                        <span x-show="!statusModalOrder?.client?.license_plaque && statusModalOrder?.client?.temp_registration" class="badge" style="background:#fff1a8;color:#17324d" x-text="'Registro: ' + statusModalOrder.client.temp_registration"></span>
                    </div>
                    <div class="order-status-context__current">
                        <label>Estado actual</label>
                        <span class="badge" :class="getStatusBadge(statusModalOrder?.status)"
                            x-text="getStatusText(statusModalOrder?.status)"></span>
                    </div>
                </section>

                <section class="order-status-selection">
                    <div class="order-status-section-heading">
                        <span class="order-status-section-icon"><i class="fa-solid fa-list-check"></i></span>
                        <div>
                            <h3>Nuevo estado</h3>
                            <p>El cambio se aplicará a esta orden.</p>
                        </div>
                    </div>

                    <div class="order-status-choices">
                        <button type="button" class="order-status-choice order-status-choice--pending"
                            @click="newStatus = 1" :aria-pressed="newStatus === 1"
                            :class="newStatus === 1 ? 'is-selected' : ''">
                            <i class="fa-regular fa-clock"></i><span>Pendiente</span>
                        </button>
                        <button type="button" class="order-status-choice order-status-choice--progress"
                            @click="newStatus = 2" :aria-pressed="newStatus === 2"
                            :class="newStatus === 2 ? 'is-selected' : ''">
                            <i class="fa-solid fa-spinner"></i><span>En proceso</span>
                        </button>
                        <button type="button" class="order-status-choice order-status-choice--done"
                            @click="newStatus = 3" :aria-pressed="newStatus === 3"
                            :class="newStatus === 3 ? 'is-selected' : ''">
                            <i class="fa-solid fa-check"></i><span>Terminado</span>
                        </button>
                        <button type="button" class="order-status-choice order-status-choice--cancelled"
                            @click="newStatus = 4" :aria-pressed="newStatus === 4"
                            :class="newStatus === 4 ? 'is-selected' : ''">
                            <i class="fa-solid fa-xmark"></i><span>Cancelado</span>
                        </button>
                    </div>
                </section>

                <section class="order-status-note" x-show="newStatus === 4" x-transition>
                    <label for="status-change-note">Motivo de cancelación <span>(opcional)</span></label>
                    <textarea id="status-change-note" class="form-control" x-model="statusChangeNote" rows="2"
                        placeholder="Escribe el motivo de la cancelación"></textarea>
                </section>
            </div>

            <footer class="order-status-footer">
                <button @click="closeStatusModal()" type="button" class="order-status-action order-status-action--secondary">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
                <button @click="confirmStatusChange()" type="button" class="order-status-action order-status-action--primary"
                    :disabled="!newStatus || newStatus === statusModalOrder?.status || changingStatus">
                    <span x-show="!changingStatus"><i class="fa-solid fa-check"></i> Confirmar cambio</span>
                    <span x-show="changingStatus"><i class="fa-solid fa-spinner fa-spin"></i> Guardando...</span>
                </button>
            </footer>
        </section>
    </div>
</template>
