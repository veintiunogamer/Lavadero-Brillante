// Reports Module - Alpine.js component for reports view

function reportsModuleActive() {
    return !!document.getElementById('reports-root');
}

if (typeof window !== 'undefined' && reportsModuleActive()) {
    console.log('Reports JS cargado');
}

window.reportsApp = function() {

    return {

        activeTab: 'sales',
        salesRange: 'month',
        sales: [],
        salesSummary: {
            total: 0,
            orders: 0
        },
        clients: [],
        searchTerms: {
            clients: ''
        },
        currentPage: {
            sales: 1,
            clients: 1
        },
        perPage: 10,
        loadingSales: false,
        loadingClients: false,

        async init() {
            await this.loadSales();
            await this.loadClients();
        },

        async changeTab(tab) {
            this.activeTab = tab;
        },

        async changeSalesRange() {

            this.resetPagination('sales');
            await this.loadSales();

        },

        async loadSales() {

            this.loadingSales = true;
            try {

                const response = await fetch(`/reports/sales?range=${this.salesRange}`, {
                    headers: { 'Accept': 'application/json' }
                });

                const result = await response.json();

                if (response.ok && result.success) {

                    this.sales = result.data || [];
                    this.salesSummary = result.summary || { total: 0, orders: 0 };
                    this.ensurePageInRange('sales');

                } else {

                    this.sales = [];
                    this.salesSummary = { total: 0, orders: 0 };
                    window.notyf?.error(result.message || 'Error al cargar ventas');
                }
            } catch (error) {

                console.error('Error cargando ventas:', error);
                this.sales = [];
                this.salesSummary = { total: 0, orders: 0 };
                window.notyf?.error('Error al cargar ventas');

            } finally {
                this.loadingSales = false;
            }
        },

        async loadClients() {

            this.loadingClients = true;

            try {

                const response = await fetch('/reports/clients', {
                    headers: { 'Accept': 'application/json' }
                });

                const result = await response.json();

                if (response.ok && result.success) {

                    this.clients = result.data || [];
                    this.ensurePageInRange('clients');

                } else {

                    this.clients = [];
                    window.notyf?.error(result.message || 'Error al cargar clientes');

                }

            } catch (error) {

                console.error('Error cargando clientes:', error);
                this.clients = [];

                window.notyf?.error('Error al cargar clientes');

            } finally {
                this.loadingClients = false;
            }
        },

        // ====================
        // BÚSQUEDA Y PAGINACIÓN
        // ====================

        getFilteredData(type) {

            if (type === 'sales') {
                return this.sales;
            }

            if (type === 'clients') {

                const searchTerm = this.searchTerms.clients.toLowerCase().trim();
                if (!searchTerm) return this.clients;

                return this.clients.filter(client => {

                    const haystack = [
                        client.name,
                        client.phone,
                        client.license_plaque
                    ].map(value => String(value || '').toLowerCase());

                    return haystack.some(value => value.includes(searchTerm));

                });
            }

            return [];
        },

        getPaginatedData(type) {

            const filteredData = this.getFilteredData(type);
            const start = (this.currentPage[type] - 1) * this.perPage;
            const end = start + this.perPage;

            return filteredData.slice(start, end);

        },

        getTotalPages(type) {
            const filteredData = this.getFilteredData(type);
            return Math.ceil(filteredData.length / this.perPage);
        },

        goToPage(type, page) {

            const totalPages = this.getTotalPages(type);

            if (page >= 1 && page <= totalPages) {
                this.currentPage[type] = page;
            }

        },

        resetPagination(type) {
            if (this.currentPage[type] !== undefined) {
                this.currentPage[type] = 1;
            }
        },

        ensurePageInRange(type) {

            const totalPages = this.getTotalPages(type);

            if (!totalPages) {
                this.currentPage[type] = 1;
                return;
            }

            if (this.currentPage[type] > totalPages) {
                this.currentPage[type] = totalPages;
            }

            if (this.currentPage[type] < 1) {
                this.currentPage[type] = 1;
            }
        },

        // ====================
        // HELPERS
        // ====================

        formatCurrency(amount) {

            const value = Number(amount || 0);

            if (window.formatEuroJS) {
                return window.formatEuroJS(value);
            }

            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR'
            }).format(value);

        },

        formatPercent(value) {
            const percent = Number(value || 0);
            return `${percent.toFixed(0)}%`;
        },

        formatDate(date) {

            if (!date) return 'N/A';

            return new Date(date).toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

        },

        formatOrderNumber(order) {

            if (order?.consecutive_serial && order?.consecutive_number) {
                return `${order.consecutive_serial}-${order.consecutive_number}`;
            }

            if (order?.id) {
                return String(order.id).slice(0, 8).toUpperCase();
            }

            return 'N/A';
        },

        getRangeLabel() {

            if (this.salesRange === 'today') {
                return 'Resumen de hoy';
            }

            return this.salesRange === 'week'
            ? 'Resumen de esta semana'
            : 'Resumen del mes actual';

        },

        getOrderStatusText(status) {

            const map = {
                1: 'Pendiente',
                2: 'En Proceso',
                3: 'Terminado',
                4: 'Cancelado'
            };

            return map[status] || 'Desconocido';

        },

        getOrderStatusBadge(status) {

            const map = {
                1: 'bg-warning text-dark',
                2: 'bg-info text-dark',
                3: 'bg-success',
                4: 'bg-danger'
            };

            return map[status] || 'bg-secondary';

        },

        getPaymentStatusText(status) {

            const map = {
                1: 'Pendiente',
                2: 'Parcial',
                3: 'Pagado'
            };

            return map[status] || 'N/A';

        },

        getPaymentStatusBadge(status) {

            const map = {
                1: 'bg-warning text-dark',
                2: 'bg-info text-dark',
                3: 'bg-success'
            };

            return map[status] || 'bg-secondary';

        },

        // ====================
        // PDF
        // ====================

        downloadDailyPdf() {
            const url = '/reports/pdf/daily';
            window.open(url, '_blank');
        },

        downloadCurrentPdf() {

            let url = `/reports/pdf/current?tab=${this.activeTab}`;

            if (this.activeTab === 'sales') {
                url += `&range=${this.salesRange}`;
            }

            if (this.activeTab === 'clients' && this.searchTerms.clients) {
                url += `&search=${encodeURIComponent(this.searchTerms.clients)}`;
            }

            window.open(url, '_blank');

        }
    };
};
