<!-- 
    Modal: Cambio Rápido de Estado
    Se usa con Alpine.js para cambiar el estado de una orden
-->

<template x-if="showStatusModal">

    <div x-cloak @click.self="closeStatusModal()" @keydown.escape.window="closeStatusModal()"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
    style="background: rgba(0,0,0,0.5); z-index: 9999; display: none;" x-transition>
        
        <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 500px; width: 95%;">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold">
                    <i class="fa-solid fa-exchange-alt color-blue me-2"></i>
                    Cambiar Estado
                </h4>
                <button @click="closeStatusModal()" type="button" class="btn-close"></button>
            </div>

            <!-- Info actual -->
            <div class="bg-light rounded-3 p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Cliente:</span>
                        <strong x-text="statusModalOrder?.client?.name || 'N/A'"></strong>
                    </div>

                    <div>
                        <span class="text-muted">Placa:</span>
                        <strong x-text="statusModalOrder?.client?.license_plaque || 'N/A'"></strong>
                    </div>
                </div>

                <div class="mt-2">
                    <span class="text-muted">Estado actual:</span>
                    <span class="badge ms-2" :class="getStatusBadge(statusModalOrder?.status)" 
                    x-text="getStatusText(statusModalOrder?.status)"></span>
                </div>

            </div>

            <!-- Selector de nuevo estado -->
            <div class="mb-4">

                <label class="form-label fw-bold">Nuevo Estado</label>

                <div class="d-flex flex-wrap gap-2">

                    <button type="button"class="btn flex-fill" @click="newStatus = 1"
                    :class="newStatus === 1 ? 'btn-warning' : 'btn-outline-warning'">
                        <i class="fa-solid fa-clock me-1"></i> Pendiente
                    </button>

                    <button type="button" class="btn flex-fill" @click="newStatus = 2"
                    :class="newStatus === 2 ? 'btn-info' : 'btn-outline-info'">
                        <i class="fa-solid fa-spinner me-1"></i> En Proceso
                    </button>

                    <button type="button" class="btn flex-fill" @click="newStatus = 3"
                    :class="newStatus === 3 ? 'btn-success' : 'btn-outline-success'">
                        <i class="fa-solid fa-check me-1"></i> Terminado
                    </button>

                    <button type="button" class="btn flex-fill" @click="newStatus = 4"
                    :class="newStatus === 4 ? 'btn-danger' : 'btn-outline-danger'">
                        <i class="fa-solid fa-times me-1"></i> Cancelado
                    </button>

                </div>

            </div>

            <!-- Nota opcional -->
            <div class="mb-4" x-show="newStatus === 4">

                <label class="form-label fw-bold">Motivo de Cancelación</label>
                <textarea class="form-control" x-model="statusChangeNote" rows="2" 
                placeholder="Opcional: indica el motivo..."></textarea>

            </div>

            <!-- Footer -->
            <div class="d-flex justify-content-end gap-2">

                <button @click="closeStatusModal()" class="btn btn-secondary">
                    Cancelar
                </button>

                <button @click="confirmStatusChange()" class="btn btn-primary"
                :disabled="!newStatus || newStatus === statusModalOrder?.status || changingStatus">
                    
                    <span x-show="!changingStatus">
                        <i class="fa-solid fa-check me-1"></i> Confirmar Cambio
                    </span>

                    <span x-show="changingStatus">
                        <i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...
                    </span>

                </button>

            </div>

        </div>

    </div>

</template>