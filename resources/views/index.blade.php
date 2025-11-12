@extends('layouts.base')

@section('content')
<div class="card-form">
    <h2 class="card-title">
        <span class="icon">👤</span>
        1. Datos del Cliente y Vehículo
    </h2>
    <div class="row">
        <div class="input-group">
            <label>Nº Orden / Factura</label>
            <div style="display: flex; gap: 0.5rem;">
                <input type="text" class="input" value="12112025" readonly style="width: 120px;">
                <input type="text" class="input" value="001" readonly style="width: 60px;">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="input-group">
            <label>Nombre Cliente <span class="required">*</span></label>
            <input type="text" class="input" placeholder="Nombre completo">
        </div>
        <div class="input-group">
            <label>Teléfono (para WhatsApp) <span class="required">*</span></label>
            <input type="text" class="input" placeholder="Número de contacto">
        </div>
        <div class="input-group">
            <label>Matrícula <span class="required">*</span></label>
            <input type="text" class="input" placeholder="Ej: 1234 ABC">
        </div>
    </div>
    <div class="row">
        <div class="input-group" style="min-width: 300px;">
            <label>Asignar Detallador <span class="required">*</span></label>
            <select class="input">
                <option>Seleccionar</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="input-group" style="width: 100%;">
            <label>Observaciones</label>
            <textarea class="input" rows="3" placeholder="Anotaciones internas sobre el servicio, cliente o estado del vehículo..."></textarea>
        </div>
    </div>
    <hr>
    <div class="row" style="align-items: center;">
        <label style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" style="accent-color: var(--color-azul-logo);">
            Solicitar Factura (Aplica 21% IVA)
        </label>
    </div>
</div>
<div class="card-form">
    <h2 class="card-title"><span class="icon">🧼</span> 2. Servicios</h2>
    <div class="row" style="margin-bottom: 0.5rem;">
        <span class="icon" style="font-size:1.2rem;">🧼</span>
        <span style="font-size:1.2rem;font-weight:500;">2. Servicios</span>
    </div>
    <p>Elige una <a href="#" style="color:var(--color-azul-logo);text-decoration:underline;">categoría</a> y luego el servicio. La lista es corta y filtrada por categoría.</p>
    <div class="service-box">
        <div class="row">
            <div class="input-group">
                <label>Categoría</label>
                <select class="input"><option>Categoría</option></select>
            </div>
            <div class="input-group">
                <label>Servicio</label>
                <select class="input"><option>Primero categoría</option></select>
            </div>
            <div class="input-group">
                <label>Tipo Vehículo</label>
                <select class="input"><option>Tipo</option></select>
            </div>
            <div class="input-group">
                <label>Nivel Suciedad</label>
                <select class="input"><option>Nivel</option></select>
            </div>
            <div class="input-group">
                <label>Cant.</label>
                <input type="number" class="input" value="1">
            </div>
            <div class="input-group">
                <label>€</label>
                <input type="number" class="input" value="0">
            </div>
            <div class="input-group" style="align-items: flex-end;">
                <button class="remove-btn">✕</button>
            </div>
        </div>
    </div>
    <button class="add-service-btn"><span class="icon">➕</span> Añadir Servicio</button>
    <div class="service-box">
        <div class="input-group" style="width:100%;">
            <label>Descripción de la cita (se genera automáticamente)</label>
            <textarea class="input" rows="2">Ninguno de nuestros precios incluye IVA.</textarea>
        </div>
        <small>Se actualiza automáticamente según los servicios elegidos. Puedes añadir notas adicionales abajo; se incorporan a la descripción.</small>
        <div class="input-group" style="width:100%;margin-top:1rem;">
            <label>Notas adicionales</label>
            <textarea class="input" rows="2" placeholder="Ej.: cliente espera; promo aplicada; aclaraciones..."></textarea>
        </div>
    </div>
    <div class="row" style="align-items: center; margin-top:1.5rem;">
        <div class="input-group" style="max-width:180px;">
            <label>% Aplicar Descuento</label>
            <select class="input"><option>0%</option></select>
        </div>
        <div class="input-group" style="max-width:180px;">
            <label>Subtotal</label>
            <div style="font-size:1.3rem;font-weight:600;">0.00€</div>
        </div>
        <div class="input-group" style="max-width:180px;">
            <label>Total</label>
            <div style="font-size:1.3rem;font-weight:700;">0.00€</div>
        </div>
    </div>
</div>
<div class="card-form">
    <h2 class="card-title"><span class="icon">✔️</span> 3. Fecha, Hora y Pago</h2>
    <div class="row" style="align-items: flex-start;">
        <div class="calendar-box calendar-enhanced">
            <div class="calendar-header">
                <button class="calendar-nav">&#60;</button>
                <span class="calendar-month">noviembre <span class="calendar-year">2025</span></span>
                <button class="calendar-nav">&#62;</button>
            </div>
            <table class="calendar-table">
                <thead>
                    <tr>
                        <th>Lu</th><th>Ma</th><th>Mi</th><th>Ju</th><th>Vi</th><th>Sa</th><th>Do</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td></td><td></td><td></td><td></td><td></td><td class="calendar-muted">1</td><td class="calendar-muted">2</td></tr>
                    <tr><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>
                    <tr><td>10</td><td>11</td><td class="calendar-active">12</td><td>13</td><td>14</td><td>15</td><td>16</td></tr>
                    <tr><td>17</td><td>18</td><td>19</td><td>20</td><td>21</td><td>22</td><td>23</td></tr>
                    <tr><td>24</td><td>25</td><td>26</td><td>27</td><td>28</td><td>29</td><td>30</td></tr>
                </tbody>
            </table>
            <div class="calendar-footer">
                <span class="calendar-tip">Selecciona una fecha para agendar</span>
            </div>
        </div>
        <div class="form-side">
            <div class="row">
                <div class="input-group">
                    <label>Hora Entrada <span class="required">*</span></label>
                    <select class="input"><option>Seleccionar</option></select>
                </div>
                <div class="input-group">
                    <label>Hora Entrega</label>
                    <select class="input"><option>Seleccionar</option></select>
                </div>
            </div>
            <div class="row">
                <div class="input-group" style="flex:2;">
                    <label>Estado del Pago</label>
                    <div class="pay-status-group">
                        <button class="pay-status-btn pay-status-active">Pendiente</button>
                        <button class="pay-status-btn">Parcial</button>
                        <button class="pay-status-btn">Pagado</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="input-group">
                    <label>Método de Pago</label>
                    <select class="input"><option>Efectivo</option></select>
                </div>
                <div class="input-group">
                    <label>Estado de la Cita</label>
                    <select class="input"><option>Confirmada</option></select>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top:1.5rem; align-items:center;">
        <label style="display:flex;align-items:center;gap:0.5rem;">
            <input type="checkbox">
            He leído y acepto los <a href="#" style="color:var(--color-amarillo-logo);text-decoration:underline;">Términos y Condiciones</a>
        </label>
    </div>
</div>
<div class="row" style="justify-content:center; margin-top:2rem;">
    <button class="confirm-btn" disabled>Confirmar Agendamiento</button>
</div>
<div class="card-citas">
    <h2 class="citas-title">Registro de Citas</h2>
    <div class="citas-tabs">
        <button class="citas-tab citas-tab-active"><span class="icon">📅</span> Citas Pendientes</button>
        <button class="citas-tab"><span class="icon">⏲️</span> Historial Completo</button>
    </div>
    <div class="citas-content">
        <p class="citas-empty">No hay citas pendientes.</p>
    </div>
</div>
@endsection
