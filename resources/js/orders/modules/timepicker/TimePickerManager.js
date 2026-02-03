/**
 * TimePickerManager - Gestión de selectores de hora con Flatpickr
 * @module modules/timepicker/TimePickerManager
 */

import { WEEKDAYS, MONTH_NAMES, MONTH_NAMES_SHORT } from '../../utils/formatters.js';

export class TimePickerManager {
    constructor() {
        this.initialized = false;
        this.useFallback = false;
    }

    /**
     * Inicializa los selectores de hora
     */
    init() {
        if (this.initialized) return;

        if (typeof flatpickr === 'undefined') {
            console.warn('Flatpickr no disponible, activando fallback');
            this.activateFallback();
            this.initialized = true;
            return;
        }

        try {
            this.initFlatpickr();
        } catch (error) {
            console.error('Error inicializando Flatpickr:', error);
            this.activateFallback();
        }

        this.initialized = true;
    }

    /**
     * Inicializa Flatpickr en los inputs de hora
     */
    initFlatpickr() {
        document.querySelectorAll('.time-picker').forEach(input => {
            if (input._flatpickr) return; // Ya inicializado

            flatpickr(input, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 30,
                minTime: "08:00",
                maxTime: "20:30",
                locale: this.getLocaleConfig(),
                onReady: (dateObj, dateStr, instance) => {
                    instance.calendarContainer.style.zIndex = 10000;
                },
                onChange: (selectedDates, dateStr, instance) => {
                    this.validateTimeRange(instance);
                    document.dispatchEvent(new CustomEvent('formFieldChanged'));
                }
            });
        });
    }

    /**
     * Obtiene la configuración de locale para Flatpickr
     * @returns {Object}
     */
    getLocaleConfig() {
        return {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: WEEKDAYS.short,
                longhand: WEEKDAYS.long
            },
            months: {
                shorthand: MONTH_NAMES_SHORT,
                longhand: MONTH_NAMES
            }
        };
    }

    /**
     * Valida que la hora de salida sea posterior a la de entrada
     * @param {Object} instance - Instancia de Flatpickr
     */
    validateTimeRange(instance) {
        const horaEntradaEl = document.getElementById('hora-entrada');
        const horaSalidaEl = document.getElementById('hora-salida');

        if (!horaEntradaEl?._flatpickr || !horaSalidaEl?._flatpickr) return;

        const entrada = horaEntradaEl._flatpickr.selectedDates[0];
        const salida = horaSalidaEl._flatpickr.selectedDates[0];

        if (entrada && salida && salida <= entrada) {
            window.notyf?.error('La hora de salida debe ser posterior a la hora de entrada');
            
            if (instance.element.id === 'hora-salida') {
                instance.clear();
            } else {
                horaSalidaEl._flatpickr.clear();
            }
        }
    }

    /**
     * Activa el modo fallback con selects nativos
     */
    activateFallback() {
        this.useFallback = true;

        // Ocultar inputs de Flatpickr
        document.querySelectorAll('.time-picker').forEach(input => {
            input.style.display = 'none';
        });

        // Mostrar selects fallback
        document.querySelectorAll('.time-picker-fallback').forEach(select => {
            select.style.display = 'block';
        });

        // Vincular validación en fallback
        this.bindFallbackValidation();
    }

    /**
     * Vincula validación en modo fallback
     */
    bindFallbackValidation() {
        const horaEntradaFallback = document.getElementById('hora-entrada-fallback');
        const horaSalidaFallback = document.getElementById('hora-salida-fallback');

        if (horaEntradaFallback && horaSalidaFallback && !horaSalidaFallback.dataset.initialized) {
            horaSalidaFallback.addEventListener('change', () => {
                const entrada = horaEntradaFallback.value;
                const salida = horaSalidaFallback.value;

                if (entrada && salida && salida <= entrada) {
                    window.notyf?.error('La hora de salida debe ser posterior a la hora de entrada');
                    horaSalidaFallback.value = '';
                }
            });
            horaSalidaFallback.dataset.initialized = 'true';
        }
    }

    /**
     * Obtiene la hora de entrada
     * @returns {string}
     */
    getHourIn() {
        if (this.useFallback) {
            return document.getElementById('hora-entrada-fallback')?.value || '';
        }

        const input = document.getElementById('hora-entrada');
        let value = input?.value || '';
        
        // Asegurar formato HH:MM
        if (value && value.length > 5) {
            value = value.substring(0, 5);
        }
        
        return value;
    }

    /**
     * Obtiene la hora de salida
     * @returns {string}
     */
    getHourOut() {
        if (this.useFallback) {
            return document.getElementById('hora-salida-fallback')?.value || '';
        }

        const input = document.getElementById('hora-salida');
        let value = input?.value || '';
        
        // Asegurar formato HH:MM
        if (value && value.length > 5) {
            value = value.substring(0, 5);
        }
        
        return value;
    }

    /**
     * Resetea los selectores de hora
     */
    reset() {
        if (this.useFallback) {
            const entradaFallback = document.getElementById('hora-entrada-fallback');
            const salidaFallback = document.getElementById('hora-salida-fallback');
            if (entradaFallback) entradaFallback.value = '';
            if (salidaFallback) salidaFallback.value = '';
        } else {
            const horaEntrada = document.getElementById('hora-entrada');
            const horaSalida = document.getElementById('hora-salida');
            if (horaEntrada?._flatpickr) horaEntrada._flatpickr.clear();
            if (horaSalida?._flatpickr) horaSalida._flatpickr.clear();
        }
    }
}

export default TimePickerManager;
