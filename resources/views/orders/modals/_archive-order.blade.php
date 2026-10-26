<template x-if="showArchiveModal">
    <div x-cloak class="order-status-overlay" @click.self="closeArchiveModal()" @keydown.escape.window="closeArchiveModal()" x-transition.opacity>
        <section class="order-status-modal order-status-modal--selector" @click.stop>
            <header class="order-status-header">
                <div class="order-status-heading">
                    <span class="order-status-icon"><i class="fa-solid fa-box-archive"></i></span>
                    <div>
                        <h2>Archivar orden</h2>
                        <p>La orden se ocultará de la operación y podrás restaurarla después.</p>
                    </div>
                </div>
                <button @click="closeArchiveModal()" type="button" class="order-status-close" aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <div class="order-status-body">
                <section class="order-status-context order-status-context--compact">
                    <div class="order-status-context__client">
                        <label>Cliente</label>
                        <strong x-text="archiveModalOrder?.client?.name || '--'"></strong>
                    </div>
                    <div class="order-status-context__current">
                        <label>Total</label>
                        <strong x-text="formatCurrency(archiveModalOrder?.total || 0)"></strong>
                    </div>
                </section>

                <label for="order-archive-reason" class="form-label fw-semibold">Motivo del archivado</label>
                <textarea id="order-archive-reason" class="form-control" rows="3" maxlength="500"
                    x-model="archiveReason" placeholder="Describe por qué esta orden se creó por error..."></textarea>
                <small class="text-muted">Mínimo 5 caracteres. Los pagos se conservarán y no se incluirán en reportes mientras la orden esté archivada.</small>
            </div>

            <footer class="order-status-footer">
                <button @click="closeArchiveModal()" type="button" class="order-status-action order-status-action--secondary">
                    <i class="fa-solid fa-xmark"></i> Volver
                </button>
                <button @click="confirmArchive()" type="button" class="order-status-action order-status-action--primary"
                    :disabled="archiveReason.trim().length < 5 || archiving">
                    <span x-show="!archiving"><i class="fa-solid fa-box-archive"></i> Archivar orden</span>
                    <span x-show="archiving"><i class="fa-solid fa-spinner fa-spin"></i> Archivando...</span>
                </button>
            </footer>
        </section>
    </div>
</template>
