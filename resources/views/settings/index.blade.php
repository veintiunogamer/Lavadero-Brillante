@extends('layouts.base')

@section('content')

    <div id="settings-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" 
    x-data="typeof settingsApp === 'function' ? settingsApp() : {}" 
    x-init='if (typeof settingsApp === "function") initData()'>
        
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

            <div class="col-12 d-flex justify-content-between align-items-center mb-3 p-4">
                <div class="col-6">
                    <h3 class="card-title mb-3">
                        <i class="fa-solid fa-cog icon color-blue"></i> 
                        Configuraciones
                    </h3>
                    <p class="fw-bold small text-muted">Configuraciones del sistema.</p>
                </div>
            </div>

            <!-- Tabs con Alpine.js -->
            <ul class="nav nav-tabs px-4" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link" :class="activeTab === 'categories' ? 'active' : ''" 
                            @click="changeTab('categories')" type="button" role="tab">
                        <i class="fa-solid fa-tags me-2"></i>Categorías
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" :class="activeTab === 'services' ? 'active' : ''" 
                            @click="changeTab('services')" type="button" role="tab">
                        <i class="fa-solid fa-tools me-2"></i>Servicios
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" :class="activeTab === 'vehicle-types' ? 'active' : ''" 
                            @click="changeTab('vehicle-types')" type="button" role="tab">
                        <i class="fa-solid fa-car me-2"></i>Tipos de Vehículo
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" :class="activeTab === 'clients' ? 'active' : ''" 
                            @click="changeTab('clients')" type="button" role="tab">
                        <i class="fa-solid fa-users me-2"></i>Clientes
                    </button>
                </li>

            </ul>

            <!-- ==================== CATEGORÍAS ==================== -->
            <div class="mt-4 p-4" x-show="activeTab === 'categories'">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button @click="openCategoryModal()" class="btn btn-success fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>
                        Crear Categoría
                    </button>

                    <div class="position-relative" style="max-width: 350px; width: 100%;">
                        <input type="text" 
                               x-model="searchTerms.categories" 
                               @input="resetPagination('categories')"
                               class="form-control pe-5" 
                               placeholder="Buscar categorías...">
                        <i class="fa-solid fa-search position-absolute" 
                           style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                    </div>
                </div>
                    <table class="table table-striped table-bordered align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th @click="sortData('categories', 'cat_name')" style="cursor: pointer;">
                                    Nombre&nbsp; <span x-html="getSortIcon('categories', 'cat_name')"></span>
                                </th>
                                <th @click="sortData('categories', 'status')" style="cursor: pointer;">
                                    Estado&nbsp; <span x-html="getSortIcon('categories', 'status')"></span>
                                </th>
                                <th @click="sortData('categories', 'creation_date')" style="cursor: pointer;">
                                    Fecha Creación&nbsp; <span x-html="getSortIcon('categories', 'creation_date')"></span>
                                </th>

                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <template x-for="category in getPaginatedData('categories')" :key="category.id">

                                <tr :class="category.status ? '' : 'table-secondary opacity-75'">
                                    <td x-text="category.cat_name"></td>
                                    <td>
                                        <span class="badge" :class="category.status ? 'bg-success' : 'bg-secondary'" 
                                              x-text="category.status ? 'Activo' : 'Inactivo'"></span>
                                    </td>
                                    <td x-text="formatDateTime(category.creation_date)"></td>
                                    <td class="text-center">
                                        <button @click="editCategory(category)" class="btn btn-sm btn-warning me-1">
                                            <i class="fa-solid fa-edit"></i> Editar
                                        </button>
                                        <button @click="deleteCategory(category.id)" class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>

                            </template>

                            <tr x-show="getFilteredData('categories').length === 0">
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    <span x-text="searchTerms.categories ? 'No se encontraron resultados' : 'No hay categorías registradas'"></span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                    <!-- Paginador -->
                    <div x-show="getTotalPages('categories') > 1" class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Página <span x-text="currentPage.categories"></span> de <span x-text="getTotalPages('categories')"></span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="currentPage.categories === 1 ? 'disabled' : ''">
                                    <button class="page-link" @click="goToPage('categories', currentPage.categories - 1)">«</button>
                                </li>
                                <template x-for="page in getTotalPages('categories')" :key="page">
                                    <li class="page-item" :class="page === currentPage.categories ? 'active' : ''">
                                        <button class="page-link" @click="goToPage('categories', page)" x-text="page"></button>
                                    </li>
                                </template>
                                <li class="page-item" :class="currentPage.categories === getTotalPages('categories') ? 'disabled' : ''">
                                    <button class="page-link" @click="goToPage('categories', currentPage.categories + 1)">»</button>
                                </li>
                            </ul>
                        </nav>
                    </div>

                </div>

            </div>

            <!-- ==================== SERVICIOS ==================== -->
            <div class="mt-4 p-4" x-show="activeTab === 'services'">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button @click="openServiceModal()" class="btn btn-success fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>
                        Crear Servicio
                    </button>

                    <div class="position-relative" style="max-width: 350px; width: 100%;">
                        <input type="text" 
                               x-model="searchTerms.services" 
                               @input="resetPagination('services')"
                               class="form-control pe-5" 
                               placeholder="Buscar servicios...">
                        <i class="fa-solid fa-search position-absolute" 
                           style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                    </div>
                </div>

                <div class="table-responsive">

                    <table class="table table-striped table-bordered align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th @click="sortData('services', 'name')" style="cursor: pointer;">
                                    Nombre <span x-html="getSortIcon('services', 'name')"></span>
                                </th>
                                
                                <th>Detalles</th>
                                <th>Precio</th>
                                <th>Duración</th>
                                <th @click="sortData('services', 'status')" style="cursor: pointer;">
                                    Estado <span x-html="getSortIcon('services', 'status')"></span>
                                </th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <template x-for="service in getPaginatedData('services')" :key="service.id">

                                <tr :class="service.status ? '' : 'table-secondary opacity-75'">
                                    <td x-html="service.name + '<br>' + '<span class=\'badge bg-secondary\'>' + getCategoryName(service.category_id) + '</span>'"></td>
                                    
                                    <td x-text="service.details"></td>
                                    <td x-text="formatCurrency(service.value)"></td>
                                    <td x-text="service.duration"></td>
                                    <td>
                                        <span class="badge" :class="service.status ? 'bg-success' : 'bg-secondary'" 
                                              x-text="service.status ? 'Activo' : 'Inactivo'"></span>
                                    </td>
                                    <td class="text-center">
                                        <button @click="editService(service)" class="btn btn-sm btn-warning me-1">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <template x-if="service.status">
                                            <button @click="deleteService(service.id)" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </template>
                                        <template x-if="!service.status">
                                            <button @click="activateItem(service.id, 'service')" class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </template>
                                    </td>
                                </tr>

                            </template>

                            <tr x-show="getFilteredData('services').length === 0">
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    <span x-text="searchTerms.services ? 'No se encontraron resultados' : 'No hay servicios registrados'"></span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                    <!-- Paginador -->
                    <div x-show="getTotalPages('services') > 1" class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Página <span x-text="currentPage.services"></span> de <span x-text="getTotalPages('services')"></span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="currentPage.services === 1 ? 'disabled' : ''">
                                    <button class="page-link" @click="goToPage('services', currentPage.services - 1)">«</button>
                                </li>
                                <template x-for="page in getTotalPages('services')" :key="page">
                                    <li class="page-item" :class="page === currentPage.services ? 'active' : ''">
                                        <button class="page-link" @click="goToPage('services', page)" x-text="page"></button>
                                    </li>
                                </template>
                                <li class="page-item" :class="currentPage.services === getTotalPages('services') ? 'disabled' : ''">
                                    <button class="page-link" @click="goToPage('services', currentPage.services + 1)">»</button>
                                </li>
                            </ul>
                        </nav>
                    </div>

                </div>

            </div>

            <!-- ==================== TIPOS DE VEHÍCULO ==================== -->
            <div class="mt-4 p-4" x-show="activeTab === 'vehicle-types'">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button @click="openVehicleTypeModal()" class="btn btn-success fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>
                        Crear Tipo de Vehículo
                    </button>

                    <div class="position-relative" style="max-width: 350px; width: 100%;">
                        <input type="text" 
                               x-model="searchTerms.vehicleTypes" 
                               @input="resetPagination('vehicleTypes')"
                               class="form-control pe-5" 
                               placeholder="Buscar tipos de vehículos...">
                        <i class="fa-solid fa-search position-absolute" 
                           style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                    </div>
                </div>

                <div class="table-responsive">

                    <table class="table table-striped table-bordered align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th @click="sortData('vehicleTypes', 'name')" style="cursor: pointer;">
                                    Nombre <span x-html="getSortIcon('vehicleTypes', 'name')"></span>
                                </th>
                                <th @click="sortData('vehicleTypes', 'status')" style="cursor: pointer;">
                                    Estado <span x-html="getSortIcon('vehicleTypes', 'status')"></span>
                                </th>
                                <th @click="sortData('vehicleTypes', 'creation_date')" style="cursor: pointer;">
                                    Fecha Creación <span x-html="getSortIcon('vehicleTypes', 'creation_date')"></span>
                                </th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <template x-for="vehicleType in getPaginatedData('vehicleTypes')" :key="vehicleType.id">

                                <tr :class="vehicleType.status ? '' : 'table-secondary opacity-75'">
                                    <td x-text="vehicleType.name"></td>
                                    <td>
                                        <span class="badge" :class="vehicleType.status ? 'bg-success' : 'bg-secondary'" 
                                              x-text="vehicleType.status ? 'Activo' : 'Inactivo'"></span>
                                    </td>
                                    <td x-text="formatDateTime(vehicleType.creation_date)"></td>
                                    <td class="text-center">
                                        <button @click="editVehicleType(vehicleType)" class="btn btn-sm btn-warning me-1">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <template x-if="vehicleType.status">
                                            <button @click="deleteVehicleType(vehicleType.id)" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </template>
                                        <template x-if="!vehicleType.status">
                                            <button @click="activateItem(vehicleType.id, 'vehicleType')" class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </template>
                                    </td>
                                </tr>

                            </template>

                            <tr x-show="getFilteredData('vehicleTypes').length === 0">
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    <span x-text="searchTerms.vehicleTypes ? 'No se encontraron resultados' : 'No hay tipos de vehículos registrados'"></span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                    <!-- Paginador -->
                    <div x-show="getTotalPages('vehicleTypes') > 1" class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Página <span x-text="currentPage.vehicleTypes"></span> de <span x-text="getTotalPages('vehicleTypes')"></span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="currentPage.vehicleTypes === 1 ? 'disabled' : ''">
                                    <button class="page-link" @click="goToPage('vehicleTypes', currentPage.vehicleTypes - 1)">«</button>
                                </li>
                                <template x-for="page in getTotalPages('vehicleTypes')" :key="page">
                                    <li class="page-item" :class="page === currentPage.vehicleTypes ? 'active' : ''">
                                        <button class="page-link" @click="goToPage('vehicleTypes', page)" x-text="page"></button>
                                    </li>
                                </template>
                                <li class="page-item" :class="currentPage.vehicleTypes === getTotalPages('vehicleTypes') ? 'disabled' : ''">
                                    <button class="page-link" @click="goToPage('vehicleTypes', currentPage.vehicleTypes + 1)">»</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                                    No hay tipos de vehículo registrados
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- ==================== CLIENTES ==================== -->
            <div class="mt-4 p-4" x-show="activeTab === 'clients'">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button @click="openClientModal()" class="btn btn-success fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>
                        Crear Cliente
                    </button>

                    <div class="position-relative" style="max-width: 350px; width: 100%;">
                        <input type="text" 
                               x-model="searchTerms.clients" 
                               @input="resetPagination('clients')"
                               class="form-control pe-5" 
                               placeholder="Buscar clientes...">
                        <i class="fa-solid fa-search position-absolute" 
                           style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                    </div>
                </div>

                <div class="table-responsive">

                    <table class="table table-striped table-bordered align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th @click="sortData('clients', 'name')" style="cursor: pointer;">
                                    Nombre <span x-html="getSortIcon('clients', 'name')"></span>
                                </th>
                                <th>Teléfono</th>
                                <th>Matrícula</th>
                                <th @click="sortData('clients', 'status')" style="cursor: pointer;">
                                    Estado <span x-html="getSortIcon('clients', 'status')"></span>
                                </th>
                                <th @click="sortData('clients', 'creation_date')" style="cursor: pointer;">
                                    Fecha Creación <span x-html="getSortIcon('clients', 'creation_date')"></span>
                                </th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <template x-for="client in getPaginatedData('clients')" :key="client.id">

                                <tr :class="client.status ? '' : 'table-secondary opacity-75'">
                                    <td x-text="client.name"></td>
                                    <td x-text="client.phone || 'N/A'"></td>
                                    <td x-text="client.license_plaque || 'N/A'"></td>
                                    <td>
                                        <span class="badge" :class="client.status ? 'bg-success' : 'bg-secondary'" 
                                              x-text="client.status ? 'Activo' : 'Inactivo'"></span>
                                    </td>
                                    <td x-text="formatDate(client.creation_date)"></td>
                                    <td class="text-center">
                                        <button @click="editClient(client)" class="btn btn-sm btn-warning me-1">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <template x-if="client.status">
                                            <button @click="deleteClient(client.id)" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </template>
                                        <template x-if="!client.status">
                                            <button @click="activateItem(client.id, 'client')" class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </template>
                                    </td>
                                </tr>

                            </template>

                            <tr x-show="getFilteredData('clients').length === 0">
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    <span x-text="searchTerms.clients ? 'No se encontraron resultados' : 'No hay clientes registrados'"></span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                    <!-- Paginador -->
                    <div x-show="getTotalPages('clients') > 1" class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Página <span x-text="currentPage.clients"></span> de <span x-text="getTotalPages('clients')"></span>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="currentPage.clients === 1 ? 'disabled' : ''">
                                    <button class="page-link" @click="goToPage('clients', currentPage.clients - 1)">«</button>
                                </li>
                                <template x-for="page in getTotalPages('clients')" :key="page">
                                    <li class="page-item" :class="page === currentPage.clients ? 'active' : ''">
                                        <button class="page-link" @click="goToPage('clients', page)" x-text="page"></button>
                                    </li>
                                </template>
                                <li class="page-item" :class="currentPage.clients === getTotalPages('clients') ? 'disabled' : ''">
                                    <button class="page-link" @click="goToPage('clients', currentPage.clients + 1)">»</button>
                                </li>
                            </ul>
                        </nav>
                    </div>

                </div>

            </div>

        </div>

        <!-- ==================== MODAL CATEGORÍA ==================== -->
        <div x-cloak @click.self="closeCategoryModal()" @keydown.escape.window="closeCategoryModal()" 
        :class="showCategoryModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" 
        style="background: rgba(0,0,0,0.5); z-index: 9999;" x-transition>
            
            <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 500px; width: 95%;">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold" x-text="isEditingCategory ? 'Editar Categoría' : 'Crear Categoría'"></h4>
                    <button @click="closeCategoryModal()" type="button" class="btn-close"></button>
                </div>

                <form @submit.prevent="saveCategory()">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" x-model="categoryForm.cat_name" class="form-control" required>
                        <span x-show="errors.category.cat_name" x-text="errors.category.cat_name?.[0]" class="text-danger small"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <select x-model="categoryForm.status" class="form-select">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                        <span x-show="errors.category.status" x-text="errors.category.status?.[0]" class="text-danger small"></span>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" @click="closeCategoryModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save me-2"></i>
                            <span x-text="isEditingCategory ? 'Actualizar' : 'Crear'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ==================== MODAL SERVICIO ==================== -->
        <div x-cloak @click.self="closeServiceModal()" @keydown.escape.window="closeServiceModal()" 
        :class="showServiceModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" 
        style="background: rgba(0,0,0,0.5); z-index: 9999;" x-transition>
            
            <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 700px; width: 95%;">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold" x-text="isEditingService ? 'Editar Servicio' : 'Crear Servicio'"></h4>
                    <button @click="closeServiceModal()" type="button" class="btn-close"></button>
                </div>

                <form @submit.prevent="saveService()">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" x-model="serviceForm.name" class="form-control" placeholder="Ej: Lavado Premium" required>
                            <span x-show="errors.service.name" x-text="errors.service.name?.[0]" class="text-danger small"></span>
                        </div>

                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label class="form-label fw-bold">Categoría <span class="text-danger">*</span></label>
                            <select x-model="serviceForm.category_id" class="form-select" required>
                                <option value="">Seleccionar categoría</option>
                                <template x-for="cat in categoriesForServices" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.cat_name"></option>
                                </template>
                            </select>
                            <span x-show="errors.service.category_id" x-text="errors.service.category_id?.[0]" class="text-danger small"></span>
                        </div>

                        <div class="col-12 mb-3 px-2">
                            <label class="form-label fw-bold">Detalles <span class="text-danger">*</span></label>
                            <textarea x-model="serviceForm.details" class="form-control" rows="3" placeholder="Descripción del servicio..." required></textarea>
                            <span x-show="errors.service.details" x-text="errors.service.details?.[0]" class="text-danger small"></span>
                        </div>

                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label class="form-label fw-bold">Precio (€) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" x-model="serviceForm.value" class="form-control" placeholder="0.00" required>
                            <span x-show="errors.service.value" x-text="errors.service.value?.[0]" class="text-danger small"></span>
                        </div>

                        <div class="col-12 col-md-6 mb-3 px-2">
                            <label class="form-label fw-bold">Duración (min) <span class="text-danger">*</span></label>
                            <input type="number" min="1" x-model="serviceForm.duration" class="form-control" placeholder="60" required>
                            <span x-show="errors.service.duration" x-text="errors.service.duration?.[0]" class="text-danger small"></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" @click="closeServiceModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save me-2"></i>
                            <span x-text="isEditingService ? 'Actualizar' : 'Crear'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ==================== MODAL TIPO DE VEHÍCULO ==================== -->
        <div x-cloak @click.self="closeVehicleTypeModal()" @keydown.escape.window="closeVehicleTypeModal()" 
        :class="showVehicleTypeModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" 
        style="background: rgba(0,0,0,0.5); z-index: 9999;" x-transition>
            
            <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 500px; width: 95%;">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold" x-text="isEditingVehicleType ? 'Editar Tipo de Vehículo' : 'Crear Tipo de Vehículo'"></h4>
                    <button @click="closeVehicleTypeModal()" type="button" class="btn-close"></button>
                </div>

                <form @submit.prevent="saveVehicleType()">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" x-model="vehicleTypeForm.name" class="form-control" required>
                        <span x-show="errors.vehicleType.name" x-text="errors.vehicleType.name?.[0]" class="text-danger small"></span>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" @click="closeVehicleTypeModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save me-2"></i>
                            <span x-text="isEditingVehicleType ? 'Actualizar' : 'Crear'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ==================== MODAL CLIENTE ==================== -->
        <div x-cloak @click.self="closeClientModal()" @keydown.escape.window="closeClientModal()" 
        :class="showClientModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" 
        style="background: rgba(0,0,0,0.5); z-index: 9999;" x-transition>
            
            <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 600px; width: 95%;">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0 fw-bold" x-text="isEditingClient ? 'Editar Cliente' : 'Crear Cliente'"></h4>
                    <button @click="closeClientModal()" type="button" class="btn-close"></button>
                </div>

                <form @submit.prevent="saveClient()">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" x-model="clientForm.name" class="form-control" required>
                            <span x-show="errors.client.name" x-text="errors.client.name?.[0]" class="text-danger small"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="tel" x-model="clientForm.phone" class="form-control" data-phone="true" placeholder="612 345 678" maxlength="11">
                            <span x-show="errors.client.phone" x-text="errors.client.phone?.[0]" class="text-danger small"></span>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Matrícula</label>
                            <input type="text" 
                                   x-model="clientForm.license_plaque" 
                                   @input.debounce.500ms="checkLicensePlate($event.target.value)"
                                   class="form-control license-plate-input" 
                                   placeholder="1234 ABC" 
                                   maxlength="10" 
                                   style="text-transform: uppercase;">
                            <span x-show="errors.client.license_plaque" x-text="errors.client.license_plaque?.[0]" class="text-danger small"></span>
                            <small x-show="licensePlateExists" class="text-danger">
                                <i class="fa-solid fa-exclamation-triangle"></i> Esta matrícula ya está registrada
                            </small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" @click="closeClientModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save me-2"></i>
                            <span x-text="isEditingClient ? 'Actualizar' : 'Crear'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ==================== MODAL DE CONFIRMACIÓN ELIMINAR ==================== -->
        <div x-cloak :class="showDeleteModal ? 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center' : 'd-none'" 
        style="background: rgba(0,0,0,0.5); z-index: 10000;" x-transition>
            
            <div class="bg-white rounded-4 p-4 shadow-lg" style="max-width: 400px; width: 95%;">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold text-danger">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button @click="cancelDelete()" type="button" class="btn-close"></button>
                </div>

                <p class="mb-4">¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.</p>

                <div class="d-flex justify-content-end gap-2">
                    <button @click="cancelDelete()" class="btn btn-secondary">Cancelar</button>
                    <button @click="confirmDelete()" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-2"></i>
                        Eliminar
                    </button>
                </div>

            </div>
        </div>

    </div>

@endsection
