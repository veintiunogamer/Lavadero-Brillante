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
        salesPeriodLabel: 'Resumen del mes actual',
        salesSummary: {
            total: 0,
            cash: 0,
            card: 0,
            transfer: 0,
            orders: 0
        },
        clients: [],
        searchTerms: {
            clients: ''
        },
        customStartDate: {
            sales: ''
        },
        paymentStatusFilter: {
            sales: ''
        },
        paymentMethodsFilter: {
            sales: ''
        },
        fleetFilter: {
            sales: '',
            clients: ''
        },
        currentPage: {
            sales: 1,
            clients: 1
        },
        perPage: 10,
        loadingSales: false,
        loadingClients: false,
        showExportModal: false,

        async init() {
            await this.loadSales();
            await this.loadClients();
        },

        async changeTab(tab) {
            this.activeTab = tab;
        },

        async changeSalesRange() {

            await this.refreshSales();

        },

        async refreshSales() {
            this.resetPagination('sales');
            await this.loadSales();
        },

        async refreshClients() {
            this.resetPagination('clients');
            await this.loadClients();
        },

        getSalesRequestParams() {
            const params = new URLSearchParams();

            if (this.customStartDate.sales) {
                params.set('date', this.customStartDate.sales);
            } else {
                params.set('range', this.salesRange);
            }

            if (this.fleetFilter.sales !== '') {
                params.set('fleet', this.fleetFilter.sales);
            }

            if (this.paymentStatusFilter.sales !== '') {
                params.set('payment_status', this.paymentStatusFilter.sales);
            }

            if (this.paymentMethodsFilter.sales !== '') {
                params.set('payment_method', this.paymentMethodsFilter.sales);
            }

            return params.toString();
        },

        getClientsRequestParams() {
            const params = new URLSearchParams();

            if (this.searchTerms.clients) {
                params.set('search', this.searchTerms.clients);
            }

            if (this.fleetFilter.clients !== '') {
                params.set('fleet', this.fleetFilter.clients);
            }

            return params.toString();
        },

        async loadSales() {

            this.loadingSales = true;
            try {

                const query = this.getSalesRequestParams();
                const response = await fetch(`/reports/sales?${query}`, {
                    headers: { 'Accept': 'application/json' }
                });

                const result = await response.json();

                if (response.ok && result.success) {

                    this.sales = result.data || [];
                    this.salesSummary = result.summary || { total: 0, cash: 0, card: 0, transfer: 0, orders: 0 };
                    this.salesPeriodLabel = result.periodLabel || this.getRangeLabel();
                    this.ensurePageInRange('sales');

                } else {

                    this.sales = [];
                    this.salesSummary = { total: 0, cash: 0, card: 0, transfer: 0, orders: 0 };
                    this.salesPeriodLabel = this.getRangeLabel();
                    window.notyf?.error(result.message || 'Error al cargar ventas');
                }
            } catch (error) {

                console.error('Error cargando ventas:', error);
                this.sales = [];
                this.salesSummary = { total: 0, cash: 0, card: 0, transfer: 0, orders: 0 };
                this.salesPeriodLabel = this.getRangeLabel();
                window.notyf?.error('Error al cargar ventas');

            } finally {
                this.loadingSales = false;
            }
        },

        async loadClients() {

            this.loadingClients = true;

            try {

                const query = this.getClientsRequestParams();
                const response = await fetch(`/reports/clients?${query}`, {
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
                return this.sales.filter(order =>
                    this.matchesFleet(order.client, this.fleetFilter.sales) && 
                    this.matchesDateSearch(order) &&
                    this.matchesPaymentStatusSearch(order) &&
                    this.matchesPaymentMethodSearch(order)
                );

            }

            if (type === 'clients') {

                return this.clients.filter(client =>
                    this.matchesClientSearch(client) &&
                    this.matchesFleet(client, this.fleetFilter.clients)
                );

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

        matchesClientSearch(client) {

            const searchTerm = (this.searchTerms.clients || '')
            .toLowerCase()
            .trim();

            if (!searchTerm) {
                return true;
            }

            const haystack = [
                client.name,
                client.phone,
                client.license_plaque
            ].map(value => String(value || '').toLowerCase());

            return haystack.some(value => value.includes(searchTerm));

        },

        matchesDateSearch(order) {

            if (!this.customStartDate.sales) return true;

            return order.date === this.customStartDate.sales;

        }, 

        matchesPaymentStatusSearch(order) {
            
            if (!this.paymentStatusFilter.sales) return true;

            return String(order.payment.status) === this.paymentStatusFilter.sales;

        }, 

        matchesPaymentMethodSearch(order) {

            if (!this.paymentMethodsFilter.sales) return true;

            return String(order.payment.type) === this.paymentMethodsFilter.sales;

        },

        matchesFleet(entity, filterValue) {

            if (filterValue === '') {
                return true;
            }

            return String(Number(entity.fleet)) === filterValue;

        },

        getSalesSummary() {

            const orders = this.getFilteredData('sales');

            const summary = {
                orders: orders.length,
                cash: 0,
                card: 0,
                transfer: 0,
                total: 0
            };

            orders.forEach(order => {

                const payment = order.payment;

                summary.total += Number(order.total || 0);

                if (!payment) return;

                switch (payment.type) {

                    case 1:
                        summary.cash += Number(payment.total || 0);
                        break;

                    case 2:
                        summary.card += Number(payment.total || 0);
                        break;

                    case 3:
                        summary.transfer += Number(payment.total || 0);
                        break;
                }
            });

            return summary;
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

        getDiscountPercent(order) {
            const subtotal = Number(order?.subtotal || 0);
            const discount = Number(order?.discount_value || 0);
            if (subtotal <= 0 || discount <= 0) return 0;
            return (discount / subtotal) * 100;
        },

        formatDate(date) {

            if (!date) return '--';

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

            return '--';
        },

        getRangeLabel() {

            if (this.customStartDate.sales) {
                return `Fecha: ${this.formatDate(this.customStartDate.sales)}`;
            }

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
                2: 'bg-info text-white',
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

            return map[status] || '--';

        },

        getPaymentMethodText(method) {
            
            const map = {
                1: 'Efectivo',
                2: 'TPV',
                3: 'Transferencia',
                4: 'Otro'
            };

            return map[method] || 'Desconocido';
        },

        getPaymentStatusBadge(status) {

            const map = {
                1: 'bg-warning text-dark',
                2: 'bg-info text-white',
                3: 'bg-success'
            };

            return map[status] || 'bg-secondary';

        },

        // ====================
        // EXPORT
        // ====================

        openExportModal() {
            this.showExportModal = true;
        },

        closeExportModal() {
            this.showExportModal = false;
        },

        getExportUrl(format) {

            const base = format === 'excel' ? '/reports/excel/current' : '/reports/pdf/current';
            const params = new URLSearchParams();

            params.set('tab', this.activeTab);

            if (this.activeTab === 'sales') {
                const salesParams = this.getSalesRequestParams();
                if (salesParams) {
                    new URLSearchParams(salesParams).forEach((value, key) => params.set(key, value));
                }
            }

            if (this.activeTab === 'clients') {
                const clientsParams = this.getClientsRequestParams();
                if (clientsParams) {
                    new URLSearchParams(clientsParams).forEach((value, key) => params.set(key, value));
                }
            }

            return `${base}?${params.toString()}`;

        },

        downloadCurrent(format) {
            const url = this.getExportUrl(format);
            window.open(url, '_blank');
            this.closeExportModal();
        },

    };
};
