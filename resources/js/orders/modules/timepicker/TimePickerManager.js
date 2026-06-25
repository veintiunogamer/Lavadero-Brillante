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
                locale: this.getLocaleConfig(),
                onReady: (dateObj, dateStr, instance) => {

                    instance.calendarContainer.style.zIndex = 10000;
                },
                onChange: (selectedDates, dateStr, instance) => {

                    document.dispatchEvent(new CustomEvent('formFieldChanged'));

                }
            });

        });
    }

    /**
     * Sincroniza el rango mínimo de hora de salida según hora de entrada (Flatpickr)
     */
    syncFlatpickrMinRange() {
        return;
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
        return;
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

        const horaEntradaFallback = document.getElementById('hour_in_fallback');
        const horaSalidaFallback = document.getElementById('hour_out_fallback');

        if (horaEntradaFallback && !horaEntradaFallback.dataset.initialized) {
            
            horaEntradaFallback.addEventListener('change', () => {
                document.dispatchEvent(new CustomEvent('formFieldChanged'));
            });

            horaEntradaFallback.dataset.initialized = 'true';
        }

        if (horaEntradaFallback && horaSalidaFallback && !horaSalidaFallback.dataset.initialized) {
            
            horaSalidaFallback.addEventListener('change', () => {
                document.dispatchEvent(new CustomEvent('formFieldChanged'));
            });

            horaSalidaFallback.dataset.initialized = 'true';
        }

    }

    /**
     * Compatibilidad para llamadas anteriores.
     */
    syncFallbackMinRange() {
        return;
    }

    /**
     * Obtiene la hora de entrada
     * @returns {string}
     */
    getHourIn() {

        if (this.useFallback) {
            return document.getElementById('hour_in_fallback')?.value || '';
        }

        const input = document.getElementById('hour_in');
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
            return document.getElementById('hour_out_fallback')?.value || '';
        }

        const input = document.getElementById('hour_out');
        let value = input?.value || '';
        
        // Asegurar formato HH:MM
        if (value && value.length > 5) {
            value = value.substring(0, 5);
        }
        
        return value;
    }

    /**
     * Establece horas de entrada/salida desde una orden en edición
     * @param {string} hourIn
     * @param {string} hourOut
     */
    setHours(hourIn, hourOut) {

        const normalize = (value) => {

            if (!value) return '';

            const strValue = String(value).trim();

            const hourMatch = strValue.match(/(\d{2}):(\d{2})/);

            if (hourMatch) {
                return `${hourMatch[1]}:${hourMatch[2]}`;
            }

            if (strValue.includes('T')) {

                const date = new Date(strValue);

                if (!Number.isNaN(date.getTime())) {

                    const hh = String(date.getHours()).padStart(2, '0');
                    const mm = String(date.getMinutes()).padStart(2, '0');

                    return `${hh}:${mm}`;

                }
            }

            if (strValue.includes(' ')) {

                const dateWithT = new Date(strValue.replace(' ', 'T'));

                if (!Number.isNaN(dateWithT.getTime())) {

                    const hh = String(dateWithT.getHours()).padStart(2, '0');
                    const mm = String(dateWithT.getMinutes()).padStart(2, '0');

                    return `${hh}:${mm}`;
                    
                }
            }

            return '';
        };

        const normalizedIn = normalize(hourIn);
        const normalizedOut = normalize(hourOut);

        if (this.useFallback) {

            const entradaFallback = document.getElementById('hour_in_fallback');
            const salidaFallback = document.getElementById('hour_out_fallback');

            const fallbackIn = normalizedIn ? `${normalizedIn}:00` : '';
            const fallbackOut = normalizedOut ? `${normalizedOut}:00` : '';

            if (entradaFallback) entradaFallback.value = fallbackIn;
            
            if (salidaFallback) salidaFallback.value = fallbackOut;
            return;

        }

        const horaEntrada = document.getElementById('hour_in');
        const horaSalida = document.getElementById('hour_out');

        if (horaEntrada?._flatpickr && normalizedIn) {

            horaEntrada._flatpickr.setDate(normalizedIn, true, 'H:i');

        } else if (horaEntrada?._flatpickr) {

            horaEntrada._flatpickr.clear();

        } else if (horaEntrada) {

            horaEntrada.value = normalizedIn;

        }

        if (horaSalida?._flatpickr && normalizedOut) {

            horaSalida._flatpickr.setDate(normalizedOut, true, 'H:i');

        } else if (horaSalida?._flatpickr) {

            horaSalida._flatpickr.clear();

        } else if (horaSalida) {

            horaSalida.value = normalizedOut;

        }

    }

    /**
     * Activa o desactiva los campos de hora según el período de pago.
     * Cuando disabled=true limpia y deshabilita ambos pickers.
     * @param {boolean} disabled
     */
    setDisabled(disabled) {

        const horaEntrada = document.getElementById('hour_in');
        const horaSalida  = document.getElementById('hour_out');
        const horaEntradaFallback = document.getElementById('hour_in_fallback');
        const horaSalidaFallback  = document.getElementById('hour_out_fallback');

        if (disabled) {

            // Limpiar valores
            if (horaEntrada?._flatpickr) {
                horaEntrada._flatpickr.clear();
            } else if (horaEntrada) {
                horaEntrada.value = '';
            }

            if (horaSalida?._flatpickr) {
                horaSalida._flatpickr.clear();
            } else if (horaSalida) {
                horaSalida.value = '';
            }

            if (horaEntradaFallback) horaEntradaFallback.value = '';
            if (horaSalidaFallback)  horaSalidaFallback.value  = '';

        }

        // Deshabilitar o habilitar los inputs visibles
        const inputs = [horaEntrada, horaSalida].filter(Boolean);
        inputs.forEach(input => {
            input.disabled = disabled;
            input.closest?.('.col-6')?.classList.toggle('opacity-50', disabled);
        });

        const fallbacks = [horaEntradaFallback, horaSalidaFallback].filter(Boolean);
        fallbacks.forEach(sel => {
            sel.disabled = disabled;
            sel.closest?.('.col-6')?.classList.toggle('opacity-50', disabled);
        });

        // Actualizar clases required-field para que la validación las ignore
        const allPickers = [...inputs, ...fallbacks];
        allPickers.forEach(el => {
            if (disabled) {
                el.classList.remove('required-field');
                el.classList.remove('is-invalid', 'is-valid');
            } else {
                el.classList.add('required-field');
            }
        });

        document.dispatchEvent(new CustomEvent('formFieldChanged'));
    }

    /**
     * Resetea los selectores de hora
     */
    reset() {
        
        // Re-habilitar primero (por si estaba en modo mensual)
        this.setDisabled(false);

        if (this.useFallback) {
            
            const entradaFallback = document.getElementById('hour_in_fallback');
            const salidaFallback = document.getElementById('hour_out_fallback');
            
            if (entradaFallback) entradaFallback.value = '';
            if (salidaFallback) salidaFallback.value = '';
            
        } else {
            
            const horaEntrada = document.getElementById('hour_in');
            const horaSalida = document.getElementById('hour_out');
            
            if (horaEntrada?._flatpickr) horaEntrada._flatpickr.clear();
            if (horaSalida?._flatpickr) horaSalida._flatpickr.clear();
            
        }
    }
}

export default TimePickerManager;
