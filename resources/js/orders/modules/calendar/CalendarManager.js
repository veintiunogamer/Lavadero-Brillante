/**
 * CalendarManager - Gestión del calendario de selección de fecha
 * @module modules/calendar/CalendarManager
 */

import { MONTH_NAMES, formatDateLong } from '../../utils/formatters.js';

export class CalendarManager {

    constructor() {

        this.currentDate = new Date();
        this.selectedDate = new Date();
        this.initialized = false;
        
        // Elementos del DOM
        this.calendarBox = null;
        this.monthSpan = null;
        this.yearSpan = null;
        this.tbody = null;
        this.prevBtn = null;
        this.nextBtn = null;
        this.footerTip = null;
    }

    /**
     * Inicializa el calendario
     */
    init() {

        this.calendarBox = document.querySelector('.calendar-box');

        if (!this.calendarBox || this.initialized) return;

        this.setupElements();
        this.bindEvents();
        this.render();
        
        // Exponer fecha seleccionada globalmente
        window.selectedOrderDate = this.selectedDate;
        
        this.initialized = true;
        this.calendarBox.dataset.initialized = 'true';
    }

    /**
     * Configura referencias a elementos del DOM
     */
    setupElements() {

        this.monthSpan = this.calendarBox.querySelector('.calendar-month');
        this.yearSpan = this.calendarBox.querySelector('.calendar-year');
        this.tbody = this.calendarBox.querySelector('tbody');
        this.footerTip = this.calendarBox.querySelector('.calendar-footer .calendar-tip');
        
        // Seleccionamos la fecha actual por defecto
        const formattedDate = formatDateLong(this.currentDate);
        this.footerTip.textContent = 'Fecha seleccionada: ' + formattedDate;

        const navBtns = this.calendarBox.querySelectorAll('.calendar-nav');
        this.prevBtn = navBtns[0];
        this.nextBtn = navBtns[1];

    }

    /**
     * Vincula eventos de navegación
     */
    bindEvents() {

        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prevMonth());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.nextMonth());
        }

    }

    /**
     * Navega al mes anterior
     */
    prevMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        this.render();
    }

    /**
     * Navega al mes siguiente
     */
    nextMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        this.render();
    }

    /**
     * Renderiza el calendario
     */
    render() {

        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();

        // Actualizar encabezado
        if (this.monthSpan) {
            this.monthSpan.childNodes[0].textContent = MONTH_NAMES[month] + ' ';
        }
        if (this.yearSpan) {
            this.yearSpan.textContent = year;
        }

        // Calcular días del mes
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();

        // Ajustar para que lunes sea el primer día (0)
        let startDayOfWeek = firstDay.getDay();
        startDayOfWeek = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;

        // Limpiar tbody
        if (this.tbody) {
            this.tbody.innerHTML = '';
        }

        let day = 1;

        for (let week = 0; week < 6; week++) {

            const row = document.createElement('tr');

            for (let dayOfWeek = 0; dayOfWeek < 7; dayOfWeek++) {

                const cell = document.createElement('td');

                if (week === 0 && dayOfWeek < startDayOfWeek) {
                    cell.textContent = '';
                } else if (day > daysInMonth) {
                    cell.textContent = '';
                } else {
                    cell.textContent = day;
                    cell.style.cursor = 'pointer';

                    const currentDay = day;

                    // Marcar día seleccionado
                    if (this.isSelectedDay(day, month, year)) {
                        cell.classList.add('calendar-active');
                    }

                    // Evento de selección
                    cell.addEventListener('click', () => {
                        this.selectDate(year, month, currentDay);
                    });

                    day++;
                }

                row.appendChild(cell);
            }

            this.tbody.appendChild(row);
            if (day > daysInMonth) break;
        }
    }

    /**
     * Verifica si un día es el seleccionado
     * @param {number} day 
     * @param {number} month 
     * @param {number} year 
     * @returns {boolean}
     */
    isSelectedDay(day, month, year) {

        return this.selectedDate &&
        this.selectedDate.getDate() === day &&
        this.selectedDate.getMonth() === month &&
        this.selectedDate.getFullYear() === year;
    }

    /**
     * Selecciona una fecha
     * @param {number} year 
     * @param {number} month 
     * @param {number} day 
     */
    selectDate(year, month, day) {

        // Remover clase activa de todas las celdas
        this.tbody.querySelectorAll('td').forEach(td => {
            td.classList.remove('calendar-active');
        });

        // Actualizar fecha seleccionada
        this.selectedDate = new Date(year, month, day);
        window.selectedOrderDate = this.selectedDate;

        // Re-renderizar para marcar la nueva selección
        this.render();

        // Actualizar footer
        this.updateFooter();
    }

    /**
     * Actualiza el texto del footer con la fecha seleccionada
     */
    updateFooter() {

        if (this.footerTip) {

            const formattedDate = formatDateLong(this.selectedDate);
            this.footerTip.textContent = 'Fecha seleccionada: ' + formattedDate;

        } 

    }

    /**
     * Obtiene la fecha seleccionada
     * @returns {Date}
     */
    getSelectedDate() {
        return this.selectedDate;
    }

    /**
     * Establece la fecha seleccionada
     * @param {Date} date 
     */
    setSelectedDate(date) {
        this.selectedDate = date;
        this.currentDate = new Date(date);
        window.selectedOrderDate = this.selectedDate;
        this.render();
        this.updateFooter();
    }

    /**
     * Resetea el calendario a la fecha actual
     */
    reset() {
        this.currentDate = new Date();
        this.selectedDate = new Date();
        window.selectedOrderDate = this.selectedDate;
        this.render();
    }
}

export default CalendarManager;
