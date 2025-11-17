@extends('layouts.base')

@section('content')

    <div id="orders-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;">
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1200px;">

        <!-- Datos del Cliente y Vehículo -->
        <div class="mb-5">

            <h2 class="card-title">
                <i class="fa-solid fa-car icon color-blue"></i>
                Datos del Cliente y Vehículo
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

                <div class="input-group">
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
                    <b class="text-dark" style="color: black;">Solicitar Factura (Aplica 21% IVA)</b>
                </label>
            </div>

        </div>

        <!-- Servicios -->
        <div class="mb-5">

            <div class="row d-flex col-lg-12 col-md-12 col-sm-12">
                
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2 class="card-title">
                        <i class="fa-solid fa-handshake icon color-blue"></i>Servicios
                    </h2>    
                    <b class="text-primary">Elige una categoría y luego el servicio. La lista es corta y filtrada por categoría.</b>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-12">  
                    <button class="add-service-btn float-right"><i class="fa-solid fa-plus icon"></i> Añadir Servicio</button>
                </div>

            </div>

            <!--  Nuevo servicio -->
            <div class="row d-flex align-items-center">

                <div class="input-group" style="flex: 1;">
                    <label>Categoría</label>
                    <select class="input"><option>Categoría</option></select>
                </div>

                <div class="input-group" style="flex: 1;">
                    <label>Servicio</label>
                    <select class="input"><option>Primero categoría</option></select>
                </div>

                <div class="input-group" style="flex: 1;">
                    <label>Tipo Vehículo</label>
                    <select class="input"><option>Tipo</option></select>
                </div>

                <div class="input-group" style="flex: 1;">
                    <label>Nivel Suciedad</label>
                    <select class="input"><option>Nivel</option></select>
                </div>

                <div class="input-group" style="width: 100px;">
                    <label>Cantidad</label>
                    <input type="number" class="input" value="1">
                </div>
                
                <div class="input-group" style="width: 100px;">
                    <label>€ Total</label>
                    <input type="number" class="input" value="0">
                </div>

                <div class="input-group" style="width: auto;">
                    <button class="remove-btn"><i class="fa-solid fa-times"></i></button>
                </div>

            </div>

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

        <!-- Pago & métodos de pago -->
        <div class="mb-5">

            <h2 class="card-title">
                <i class="fa-solid fa-calendar-check icon color-blue"></i> Fecha, Hora y Pago
            </h2>

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
                <label class="text-dark">
                    <input type="checkbox">
                    He leído y acepto los <a href="#" style="color:var(--color-amarillo-logo);text-decoration:underline;">Términos y Condiciones</a>
                </label>
            </div>

        </div>

        <!-- Boton de envio -->
        <div class="row" style="justify-content:center; margin-top:2rem;">
            <button class="confirm-btn" disabled>Confirmar Agendamiento</button>
        </div>

        <!-- Listado de citas agendamiento -->
        <div class="card mt-4 p-4 rounded-4">

            <h2 class="citas-title">
                <i class="fa-solid fa-list-check icon color-blue"></i>
                Registro de Citas
            </h2>

            <div class="citas-tabs">
                <button class="citas-tab citas-tab-active"><i class="fa-solid fa-calendar icon"></i> Citas Pendientes</button>
                <button class="citas-tab"><i class="fa-solid fa-clock icon"></i> Historial Completo</button>
            </div>

            <hr>

            <div class="citas-content">
                <p class="citas-empty">No hay citas pendientes.</p>
            </div>

        </div>

        </div>
    </div>

@endsection
