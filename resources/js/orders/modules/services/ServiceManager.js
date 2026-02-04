/**
 * ServiceManager - Gestión de filas de servicios
 * @module modules/services/ServiceManager
 */

import { fetchServicesByCategory } from '../../utils/api.js';
import PriceCalculator from './PriceCalculator.js';

export class ServiceManager {
    constructor() {
        this.calculator = new PriceCalculator();
        this.rowCounter = 1;
        this.originalRow = null;
        this.addBtn = null;
        this.initialized = false;
    }

    /**
     * Inicializa el gestor de servicios
     */
    init() {
        if (this.initialized) return;
        
        this.calculator.init();
        this.originalRow = document.querySelector('.service-item');
        this.addBtn = document.querySelector('.add-service-btn');

        if (this.originalRow) {
            this.bindRowEvents(this.originalRow);
        }

        if (this.addBtn && !this.addBtn.dataset.initialized) {
            this.addBtn.addEventListener('click', () => this.addRow());
            this.addBtn.dataset.initialized = 'true';
        }

        this.initialized = true;
    }

    /**
     * Vincula eventos a una fila de servicio
     * @param {HTMLElement} row 
     */
    bindRowEvents(row) {
        const categorySelect = row.querySelector('.service-category');
        const serviceSelect = row.querySelector('.service-select');
        const quantityInput = row.querySelector('.service-quantity');
        const priceInput = row.querySelector('.service-price');

        if (categorySelect && !categorySelect.dataset.initialized) {
            categorySelect.addEventListener('change', () => {
                this.onCategoryChange(categorySelect, serviceSelect, row);
            });
            categorySelect.dataset.initialized = 'true';
        }

        if (serviceSelect && !serviceSelect.dataset.initialized) {
            serviceSelect.addEventListener('change', () => {
                this.calculator.updateServicePrice(serviceSelect, priceInput);
            });
            serviceSelect.dataset.initialized = 'true';
        }

        if (quantityInput && !quantityInput.dataset.initialized) {
            quantityInput.addEventListener('input', () => {
                this.calculator.updateQuantityPrice(quantityInput, priceInput);
            });
            quantityInput.dataset.initialized = 'true';
        }
    }

    /**
     * Maneja el cambio de categoría
     * @param {HTMLSelectElement} categorySelect 
     * @param {HTMLSelectElement} serviceSelect 
     * @param {HTMLElement} row 
     */
    async onCategoryChange(categorySelect, serviceSelect, row) {
        const categoryId = categorySelect.value;

        // Resetear servicio
        serviceSelect.innerHTML = '<option value="">Selecciona un servicio</option>';
        serviceSelect.disabled = true;

        // Resetear cantidad y precio
        const quantityInput = row.querySelector('.service-quantity');
        const priceInput = row.querySelector('.service-price');
        if (quantityInput) quantityInput.value = 1;
        if (priceInput) priceInput.value = '0.00';

        if (!categoryId) {
            this.calculator.recalculate();
            return;
        }

        try {
            const result = await fetchServicesByCategory(categoryId);

            if (result.success && result.data.length > 0) {
                result.data.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.name;
                    option.dataset.value = service.value;
                    serviceSelect.appendChild(option);
                });
                serviceSelect.disabled = false;
            } else {
                serviceSelect.innerHTML = '<option value="">No hay servicios disponibles</option>';
            }
        } catch (error) {
            console.error('Error cargando servicios:', error);
            serviceSelect.innerHTML = '<option value="">Error al cargar servicios</option>';
        }
    }

    /**
     * Agrega una nueva fila de servicio
     */
    addRow() {
        if (!this.originalRow) return;

        const clone = this.originalRow.cloneNode(true);

        // Limpiar valores y flags
        clone.querySelectorAll('input, select, textarea').forEach(el => {
            el.value = el.defaultValue || '';
            delete el.dataset.initialized;
        });

        // Actualizar data-service-row
        this.rowCounter++;
        clone.querySelectorAll('[data-service-row]').forEach(el => {
            el.dataset.serviceRow = this.rowCounter;
        });

        // Agregar botón eliminar
        this.addRemoveButton(clone);

        // Vincular eventos
        this.bindRowEvents(clone);

        // Insertar después del último service-item
        const serviceItems = document.querySelectorAll('.service-item');
        const lastItem = serviceItems[serviceItems.length - 1];
        lastItem.insertAdjacentElement('afterend', clone);
    }

    /**
     * Agrega botón de eliminar a una fila
     * @param {HTMLElement} row 
     */
    addRemoveButton(row) {
        let btnCol = row.querySelector('.col-lg-1.d-flex');
        
        if (!btnCol) {
            btnCol = document.createElement('div');
            btnCol.className = 'col-lg-1 d-flex align-items-center px-2';
            btnCol.style.paddingTop = '1.7rem';
            row.appendChild(btnCol);
        } else {
            btnCol.innerHTML = '';
        }

        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-btn btn btn-sm btn-danger';
        removeBtn.type = 'button';
        removeBtn.innerHTML = '<i class="fa-solid fa-times"></i>';
        removeBtn.addEventListener('click', () => this.removeRow(row));

        btnCol.appendChild(removeBtn);
    }

    /**
     * Elimina una fila de servicio
     * @param {HTMLElement} row 
     */
    removeRow(row) {
        row.remove();
        this.calculator.recalculate();
        this.calculator.updateOrderDescription();
    }

    /**
     * Recolecta datos de todos los servicios
     * @returns {Array}
     */
    collectServices() {
        const services = [];
        
        document.querySelectorAll('.service-item').forEach(item => {
            const serviceId = item.querySelector('.service-select')?.value;
            const quantity = item.querySelector('.service-quantity')?.value;
            const price = item.querySelector('.service-price')?.value;

            if (serviceId) {
                services.push({
                    service_id: serviceId,
                    quantity: parseInt(quantity),
                    price: parseFloat(price)
                });
            }
        });

        return services;
    }

    /**
     * Resetea todas las filas de servicio
     */
    reset() {

        // Eliminar filas extra
        const serviceItems = document.querySelectorAll('.service-item');

        for (let i = serviceItems.length - 1; i > 0; i--) {
            serviceItems[i].remove();
        }

        // Resetear primera fila
        const firstService = serviceItems[0];

        if (firstService) {

            firstService.querySelectorAll('select').forEach(select => {
                select.value = '';
            });

            const quantityInput = firstService.querySelector('.service-quantity');
            const priceInput = firstService.querySelector('.service-price');
            
            if (quantityInput) quantityInput.value = 1;
            if (priceInput) priceInput.value = '0.00';
        }

        this.rowCounter = 1;
        this.calculator.reset();
    }

    /**
     * Obtiene el calculador de precios
     * @returns {PriceCalculator}
     */
    getCalculator() {
        return this.calculator;
    }

    /**
     * Carga servicios existentes (para edición)
     * @param {Array} existingServices - Array de servicios de la orden
     */
    async loadExistingServices(existingServices) {
        if (!existingServices || existingServices.length === 0) return;

        // Limpiar filas existentes primero
        const serviceItems = document.querySelectorAll('.service-item');
        for (let i = serviceItems.length - 1; i > 0; i--) {
            serviceItems[i].remove();
        }

        // Cargar cada servicio
        for (let i = 0; i < existingServices.length; i++) {
            const service = existingServices[i];
            let row;

            if (i === 0) {
                // Usar la primera fila existente
                row = document.querySelector('.service-item');
            } else {
                // Agregar nueva fila
                this.addRow();
                const rows = document.querySelectorAll('.service-item');
                row = rows[rows.length - 1];
            }

            if (!row) continue;

            const categorySelect = row.querySelector('.service-category');
            const serviceSelect = row.querySelector('.service-select');
            const quantityInput = row.querySelector('.service-quantity');
            const priceInput = row.querySelector('.service-price');

            // Seleccionar categoría
            if (categorySelect && service.category_id) {
                categorySelect.value = service.category_id;
                
                // Cargar servicios de esa categoría
                await this.loadServicesForCategory(categorySelect, serviceSelect, service.id);
            }

            // Establecer cantidad
            if (quantityInput && service.pivot) {
                quantityInput.value = service.pivot.quantity || 1;
            }

            // Establecer precio
            if (priceInput && service.pivot) {
                priceInput.value = service.pivot.total || service.value || 0;
                priceInput.dataset.basePrice = service.value || 0;
            }
        }

        // Recalcular totales
        this.calculator.recalculate();
    }

    /**
     * Carga servicios de una categoría y selecciona uno
     * @param {HTMLSelectElement} categorySelect 
     * @param {HTMLSelectElement} serviceSelect 
     * @param {string} selectedServiceId - ID del servicio a seleccionar
     */
    async loadServicesForCategory(categorySelect, serviceSelect, selectedServiceId) {
        const categoryId = categorySelect.value;
        if (!categoryId) return;

        try {
            const result = await fetchServicesByCategory(categoryId);

            if (result.success && result.data.length > 0) {
                serviceSelect.innerHTML = '<option value="">Selecciona un servicio</option>';
                
                result.data.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.name;
                    option.dataset.value = service.value;
                    
                    if (service.id === selectedServiceId) {
                        option.selected = true;
                    }
                    
                    serviceSelect.appendChild(option);
                });
                
                serviceSelect.disabled = false;
            }
        } catch (error) {
            console.error('Error cargando servicios para edición:', error);
        }
    }
}

export default ServiceManager;
