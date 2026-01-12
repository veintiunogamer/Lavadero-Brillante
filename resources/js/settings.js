// Solo mostrar logs si estamos en la vista de settings
function settingsModuleActive() {
    return !!document.getElementById('settings-root');
}

if (typeof window !== 'undefined' && settingsModuleActive()) {
    console.log('Settings JS cargado');
}

// Exponer la función globalmente para Alpine
window.settingsApp = function() {

    return {
        // Tab activo
        activeTab: 'categories',

        // Búsqueda
        searchService: '',

        // Sorting
        sortBy: {
            categories: 'status',
            services: 'status',
            vehicleTypes: 'status',
            clients: 'status'
        },
        sortDirection: {
            categories: 'desc',
            services: 'desc',
            vehicleTypes: 'desc',
            clients: 'desc'
        },

        // Datos
        categories: [],
        services: [],
        vehicleTypes: [],
        clients: [],
        categoriesForServices: [], // Para el select de categorías en servicios

        // Modales
        showCategoryModal: false,
        showServiceModal: false,
        showVehicleTypeModal: false,
        showClientModal: false,

        // Modales de confirmación de eliminación
        showDeleteModal: false,
        deleteItemId: null,
        deleteItemType: null,

        // Estados de edición
        isEditingCategory: false,
        isEditingService: false,
        isEditingVehicleType: false,
        isEditingClient: false,

        currentEditId: null,

        // Formularios
        categoryForm: {
            cat_name: '',
            status: 1
        },
        serviceForm: {
            category_id: '',
            name: '',
            details: '',
            value: '',
            duration: ''
        },
        vehicleTypeForm: {
            name: ''
        },
        clientForm: {
            name: '',
            phone: '',
            license_plaque: ''
        },

        // Errores de validación
        errors: {
            category: {},
            service: {},
            vehicleType: {},
            client: {}
        },

        // Inicialización
        async initData() {
            await this.loadCategories();
        },

        // ====================
        // CATEGORÍAS
        // ====================
        
        async loadCategories() {
            try {
                const response = await fetch('/categories', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.categories = data;
                this.categoriesForServices = data.filter(c => c.status === 1);
                this.applySorting('categories');
            } catch (error) {
                console.error('Error cargando categorías:', error);
                window.notyf.error('Error al cargar categorías');
            }
        },

        openCategoryModal() {
            this.showCategoryModal = true;
            this.isEditingCategory = false;
            this.currentEditId = null;
            this.resetCategoryForm();
            this.clearErrors('category');
        },

        async editCategory(category) {
            this.isEditingCategory = true;
            this.currentEditId = category.id;
            this.categoryForm = {
                cat_name: category.cat_name,
                status: category.status ? 1 : 0
            };
            this.showCategoryModal = true;
            this.clearErrors('category');
        },

        async saveCategory() {
            this.clearErrors('category');

            const url = this.isEditingCategory 
                ? `/categories/${this.currentEditId}` 
                : '/categories';
            const method = this.isEditingCategory ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.categoryForm)
                });

                if (response.status === 422) {
                    const result = await response.json();
                    this.errors.category = result.errors || {};
                    return;
                }

                const result = await response.json();

                if (response.ok) {
                    window.notyf.success(this.isEditingCategory ? 'Categoría actualizada' : 'Categoría creada');
                    this.closeCategoryModal();
                    await this.loadCategories();
                } else {
                    window.notyf.error(result.message || 'Error al guardar categoría');
                }
            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Error al guardar categoría');
            }
        },

        deleteCategory(id) {
            this.deleteItemId = id;
            this.deleteItemType = 'category';
            this.showDeleteModal = true;
        },

        closeCategoryModal() {
            this.showCategoryModal = false;
            this.resetCategoryForm();
            this.clearErrors('category');
        },

        resetCategoryForm() {
            this.categoryForm = {
                cat_name: '',
                status: 1
            };
        },

        // ====================
        // SERVICIOS
        // ====================

        async loadServices() {
            try {
                const response = await fetch('/api/services', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.services = data;
                this.applySorting('services');
            } catch (error) {
                console.error('Error cargando servicios:', error);
                window.notyf.error('Error al cargar servicios');
            }
        },

        openServiceModal() {
            this.showServiceModal = true;
            this.isEditingService = false;
            this.currentEditId = null;
            this.resetServiceForm();
            this.clearErrors('service');
        },

        async editService(service) {
            this.isEditingService = true;
            this.currentEditId = service.id;
            this.serviceForm = {
                category_id: service.category_id,
                name: service.name,
                details: service.details,
                value: service.value,
                duration: service.duration
            };
            this.showServiceModal = true;
            this.clearErrors('service');
        },

        async saveService() {
            this.clearErrors('service');

            const url = this.isEditingService 
                ? `/services/${this.currentEditId}` 
                : '/services';
            const method = this.isEditingService ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.serviceForm)
                });

                if (response.status === 422) {
                    const result = await response.json();
                    this.errors.service = result.errors || {};
                    return;
                }

                const result = await response.json();

                if (response.ok) {
                    window.notyf.success(this.isEditingService ? 'Servicio actualizado' : 'Servicio creado');
                    this.closeServiceModal();
                    await this.loadServices();
                } else {
                    window.notyf.error(result.message || 'Error al guardar servicio');
                }
            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Error al guardar servicio');
            }
        },

        deleteService(id) {
            this.deleteItemId = id;
            this.deleteItemType = 'service';
            this.showDeleteModal = true;
        },

        closeServiceModal() {
            this.showServiceModal = false;
            this.resetServiceForm();
            this.clearErrors('service');
        },

        resetServiceForm() {
            this.serviceForm = {
                category_id: '',
                name: '',
                details: '',
                value: '',
                duration: ''
            };
        },

        getCategoryName(categoryId) {
            const category = this.categoriesForServices.find(c => c.id === categoryId);
            return category ? category.cat_name : 'N/A';
        },

        // Filtrar servicios por búsqueda
        get filteredServices() {
            if (!this.searchService.trim()) {
                return this.services;
            }
            const search = this.searchService.toLowerCase();
            return this.services.filter(service => 
                service.name.toLowerCase().includes(search) ||
                this.getCategoryName(service.category_id).toLowerCase().includes(search)
            );
        },

        // ====================
        // TIPOS DE VEHÍCULO
        // ====================

        async loadVehicleTypes() {
            try {
                const response = await fetch('/vehicle-types', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.vehicleTypes = data;
                this.applySorting('vehicleTypes');
            } catch (error) {
                console.error('Error cargando tipos de vehículo:', error);
                window.notyf.error('Error al cargar tipos de vehículo');
            }
        },

        openVehicleTypeModal() {
            this.showVehicleTypeModal = true;
            this.isEditingVehicleType = false;
            this.currentEditId = null;
            this.resetVehicleTypeForm();
            this.clearErrors('vehicleType');
        },

        async editVehicleType(vehicleType) {
            this.isEditingVehicleType = true;
            this.currentEditId = vehicleType.id;
            this.vehicleTypeForm = {
                name: vehicleType.name
            };
            this.showVehicleTypeModal = true;
            this.clearErrors('vehicleType');
        },

        async saveVehicleType() {
            this.clearErrors('vehicleType');

            const url = this.isEditingVehicleType 
                ? `/vehicle-types/${this.currentEditId}` 
                : '/vehicle-types';
            const method = this.isEditingVehicleType ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.vehicleTypeForm)
                });

                if (response.status === 422) {
                    const result = await response.json();
                    this.errors.vehicleType = result.errors || {};
                    return;
                }

                const result = await response.json();

                if (response.ok) {
                    window.notyf.success(this.isEditingVehicleType ? 'Tipo de vehículo actualizado' : 'Tipo de vehículo creado');
                    this.closeVehicleTypeModal();
                    await this.loadVehicleTypes();
                } else {
                    window.notyf.error(result.message || 'Error al guardar tipo de vehículo');
                }
            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Error al guardar tipo de vehículo');
            }
        },

        deleteVehicleType(id) {
            this.deleteItemId = id;
            this.deleteItemType = 'vehicleType';
            this.showDeleteModal = true;
        },

        closeVehicleTypeModal() {
            this.showVehicleTypeModal = false;
            this.resetVehicleTypeForm();
            this.clearErrors('vehicleType');
        },

        resetVehicleTypeForm() {
            this.vehicleTypeForm = {
                name: ''
            };
        },

        // ====================
        // CLIENTES
        // ====================

        async loadClients() {
            try {
                const response = await fetch('/api/clients', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.clients = data;
                this.applySorting('clients');
            } catch (error) {
                console.error('Error cargando clientes:', error);
                window.notyf.error('Error al cargar clientes');
            }
        },

        openClientModal() {
            this.showClientModal = true;
            this.isEditingClient = false;
            this.currentEditId = null;
            this.resetClientForm();
            this.clearErrors('client');
        },

        async editClient(client) {
            this.isEditingClient = true;
            this.currentEditId = client.id;
            this.clientForm = {
                name: client.name,
                phone: client.phone || '',
                license_plaque: client.license_plaque || ''
            };
            this.showClientModal = true;
            this.clearErrors('client');
        },

        async saveClient() {
            this.clearErrors('client');

            const url = this.isEditingClient 
                ? `/clients/${this.currentEditId}` 
                : '/clients';
            const method = this.isEditingClient ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.clientForm)
                });

                if (response.status === 422) {
                    const result = await response.json();
                    this.errors.client = result.errors || {};
                    return;
                }

                const result = await response.json();

                if (response.ok) {
                    window.notyf.success(this.isEditingClient ? 'Cliente actualizado' : 'Cliente creado');
                    this.closeClientModal();
                    await this.loadClients();
                } else {
                    window.notyf.error(result.message || 'Error al guardar cliente');
                }
            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Error al guardar cliente');
            }
        },

        deleteClient(id) {
            this.deleteItemId = id;
            this.deleteItemType = 'client';
            this.showDeleteModal = true;
        },

        closeClientModal() {
            this.showClientModal = false;
            this.resetClientForm();
            this.clearErrors('client');
        },

        resetClientForm() {
            this.clientForm = {
                name: '',
                phone: '',
                license_plaque: ''
            };
        },

        // ====================
        // ELIMINACIÓN GENERAL
        // ====================

        async confirmDelete() {
            const id = this.deleteItemId;
            const type = this.deleteItemType;

            this.showDeleteModal = false;

            let url, reloadFn;

            switch (type) {
                case 'category':
                    url = `/categories/${id}`;
                    reloadFn = () => this.loadCategories();
                    break;
                case 'service':
                    url = `/services/${id}`;
                    reloadFn = () => this.loadServices();
                    break;
                case 'vehicleType':
                    url = `/vehicle-types/${id}`;
                    reloadFn = () => this.loadVehicleTypes();
                    break;
                case 'client':
                    url = `/clients/${id}`;
                    reloadFn = () => this.loadClients();
                    break;
            }

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    window.notyf.success(result.message || 'Eliminado exitosamente');
                    await reloadFn();
                } else {
                    window.notyf.error(result.message || 'Error al eliminar');
                }
            } catch (error) {
                console.error('Error:', error);
                window.notyf.error('Error al eliminar');
            }

            this.deleteItemId = null;
            this.deleteItemType = null;
        },

        cancelDelete() {
            this.showDeleteModal = false;
            this.deleteItemId = null;
            this.deleteItemType = null;
        },

        // ====================
        // CAMBIO DE TAB
        // ====================

        async changeTab(tab) {
            this.activeTab = tab;

            // Cargar datos según el tab
            switch (tab) {
                case 'categories':
                    await this.loadCategories();
                    break;
                case 'services':
                    await this.loadServices();
                    break;
                case 'vehicle-types':
                    await this.loadVehicleTypes();
                    break;
                case 'clients':
                    await this.loadClients();
                    break;
            }
        },

        // ====================
        // UTILIDADES
        // ====================

        // Ordenar datos
        sortData(type, field) {
            const currentSort = this.sortBy[type];
            const currentDirection = this.sortDirection[type];

            // Si es el mismo campo, invertir dirección
            if (currentSort === field) {
                this.sortDirection[type] = currentDirection === 'asc' ? 'desc' : 'asc';
            } else {
                // Si es otro campo, establecer como ascendente
                this.sortBy[type] = field;
                this.sortDirection[type] = 'asc';
            }

            // Aplicar el ordenamiento
            this.applySorting(type);
        },

        applySorting(type) {
            const field = this.sortBy[type];
            const direction = this.sortDirection[type];
            let dataArray;

            switch (type) {
                case 'categories':
                    dataArray = this.categories;
                    break;
                case 'services':
                    dataArray = this.services;
                    break;
                case 'vehicleTypes':
                    dataArray = this.vehicleTypes;
                    break;
                case 'clients':
                    dataArray = this.clients;
                    break;
            }

            dataArray.sort((a, b) => {
                let aVal = a[field];
                let bVal = b[field];

                // Para status, ordenar activos primero cuando es desc
                if (field === 'status') {
                    aVal = a.status ? 1 : 0;
                    bVal = b.status ? 1 : 0;
                }

                // Para nombres, usar comparación de strings
                if (field === 'name' || field === 'cat_name') {
                    aVal = (aVal || '').toLowerCase();
                    bVal = (bVal || '').toLowerCase();
                }

                // Para fechas, convertir a timestamp
                if (field === 'creation_date') {
                    aVal = new Date(aVal).getTime();
                    bVal = new Date(bVal).getTime();
                }

                if (direction === 'asc') {
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
        },

        getSortIcon(type, field) {
            if (this.sortBy[type] !== field) {
                return '<i class="fa-solid fa-sort"></i>';
            }
            return this.sortDirection[type] === 'asc' ? '<i class="fa-solid fa-sort-up"></i>' : '<i class="fa-solid fa-sort-down"></i>';
        },

        // Activar/Desactivar items
        async activateItem(id, type) {
            let url, reloadFn, itemName;

            switch (type) {
                case 'service':
                    url = `/services/activate/${id}`;
                    reloadFn = () => this.loadServices();
                    itemName = 'Servicio';
                    break;
                case 'vehicleType':
                    url = `/vehicle-types/activate/${id}`;
                    reloadFn = () => this.loadVehicleTypes();
                    itemName = 'Tipo de vehículo';
                    break;
                case 'client':
                    url = `/clients/activate/${id}`;
                    reloadFn = () => this.loadClients();
                    itemName = 'Cliente';
                    break;
            }

            try {
                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    window.notyf.success(result.message || `${itemName} activado`);
                    await reloadFn();
                } else {
                    window.notyf.error(result.message || `Error al activar ${itemName}`);
                }
            } catch (error) {
                console.error('Error:', error);
                window.notyf.error(`Error al activar ${itemName}`);
            }
        },

        clearErrors(type) {
            this.errors[type] = {};
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR'
            }).format(value);
        },

        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString('es-ES');
        },

        formatDateTime(date) {
            if (!date) return 'N/A';
            const d = new Date(date);
            return d.toLocaleDateString('es-ES') + ' ' + d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: false });
        }
    }
}
