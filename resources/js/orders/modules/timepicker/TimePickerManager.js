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
                    this.syncFlatpickrMinRange();
                    this.validateTimeRange(instance);
                    document.dispatchEvent(new CustomEvent('formFieldChanged'));
                }
            });
        });

        this.syncFlatpickrMinRange();
    }

    /**
     * Sincroniza el rango mínimo de hora de salida según hora de entrada (Flatpickr)
     */
    syncFlatpickrMinRange() {
        const horaEntradaEl = document.getElementById('hora-entrada');
        const horaSalidaEl = document.getElementById('hora-salida');

        if (!horaEntradaEl?._flatpickr || !horaSalidaEl?._flatpickr) return;

        const entradaDate = horaEntradaEl._flatpickr.selectedDates[0] || null;
        const minTime = entradaDate
            ? `${String(entradaDate.getHours()).padStart(2, '0')}:${String(entradaDate.getMinutes()).padStart(2, '0')}`
            : '08:00';

        horaSalidaEl._flatpickr.set('minTime', minTime);

        const salidaDate = horaSalidaEl._flatpickr.selectedDates[0] || null;
        if (entradaDate && salidaDate && salidaDate < entradaDate) {
            horaSalidaEl._flatpickr.clear();
        }
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

        if (horaEntradaFallback && !horaEntradaFallback.dataset.initialized) {
            horaEntradaFallback.addEventListener('change', () => {
                this.syncFallbackMinRange();
            });
            horaEntradaFallback.dataset.initialized = 'true';
        }

        if (horaEntradaFallback && horaSalidaFallback && !horaSalidaFallback.dataset.initialized) {
            horaSalidaFallback.addEventListener('change', () => {
                this.syncFallbackMinRange();
                const entrada = horaEntradaFallback.value;
                const salida = horaSalidaFallback.value;

                if (entrada && salida && salida <= entrada) {
                    window.notyf?.error('La hora de salida debe ser posterior a la hora de entrada');
                    horaSalidaFallback.value = '';
                }
            });
            horaSalidaFallback.dataset.initialized = 'true';
        }

        this.syncFallbackMinRange();
    }

    /**
     * Restringe opciones de hora salida en fallback para que sean >= hora entrada
     */
    syncFallbackMinRange() {
        const horaEntradaFallback = document.getElementById('hora-entrada-fallback');
        const horaSalidaFallback = document.getElementById('hora-salida-fallback');

        if (!horaEntradaFallback || !horaSalidaFallback) return;

        const entrada = horaEntradaFallback.value || '';

        Array.from(horaSalidaFallback.options).forEach(option => {
            if (!option.value) {
                option.disabled = false;
                return;
            }

            option.disabled = entrada ? option.value < entrada : false;
        });

        if (horaSalidaFallback.value && entrada && horaSalidaFallback.value < entrada) {
            horaSalidaFallback.value = '';
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
            const entradaFallback = document.getElementById('hora-entrada-fallback');
            const salidaFallback = document.getElementById('hora-salida-fallback');

            const fallbackIn = normalizedIn ? `${normalizedIn}:00` : '';
            const fallbackOut = normalizedOut ? `${normalizedOut}:00` : '';

            if (entradaFallback) entradaFallback.value = fallbackIn;
            if (salidaFallback) salidaFallback.value = fallbackOut;
            this.syncFallbackMinRange();
            return;
        }

        const horaEntrada = document.getElementById('hora-entrada');
        const horaSalida = document.getElementById('hora-salida');

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

        this.syncFlatpickrMinRange();
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
