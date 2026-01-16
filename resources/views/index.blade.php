@extends('layouts.base')

@section('content')

    <div id="orders-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" 
         x-data="typeof orderFormApp === 'function' ? orderFormApp() : {}" 
         x-init="init()">
        
        <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

            <!-- Datos del Cliente y Vehículo -->
            <div class="my-4 p-4">

                <!-- Cabezera -->
                <div class="col-12 d-flex mb-4">

                    <div class="col-6">
                        <h2 class="card-title">
                            <i class="fa-solid fa-car icon color-blue"></i>
                            Datos del Cliente y Vehículo
                        </h2>
                        <span class="text-muted fw-bold">Información básica para agendar el servicio.</span>
                    </div>

                    <div class="col-6 text-end">

                        <label class="fw-bold">Nº Orden / Factura</label>
                        <div style="gap: 0.5rem;">
                            <input type="text" class="input float-right" readonly style="width: 120px;" value="{{ $consecutive['date_code'] ?? '' }}">
                            <input type="text" class="input float-right" readonly style="width: 70px;" value="{{ $consecutive['sequence'] ?? '' }}">
                        </div>

                    </div>

                </div>

                <hr>


                <div class="d-flex flex-wrap p-4 border rounded-3 bg-light mt-4" style="border-left: 4px solid #0d6efd !important;">

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Nombre Cliente <span class="required">*</span></label>
                        <input type="text" class="input form-control" placeholder="Nombre completo">
                    </div>

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Teléfono <span class="required">*</span></label>
                        <input type="text" id="telefono-whatsapp" class="input form-control" placeholder="Ej: +34 612 345 678" maxlength="12" data-phone="true" required>
                    </div>

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Matrícula <span class="required">*</span></label>
                        <input type="text" class="input form-control" placeholder="Ej: 1234 ABC">
                    </div>

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Asignar Detallador <span class="required">*</span></label>
                        <select class="input form-control required-field" data-field-name="Detallador">
                            <option value="">Seleccionar</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 px-2">
                        <div class="col-12" style="width: 100%;">
                            <label class="fw-bold">Observaciones</label>
                            <textarea class="input form-control" rows="3" placeholder="Anotaciones internas sobre el servicio, cliente o estado del vehículo..."></textarea>
                        </div>
                    </div>
                    
                </div>

                <hr>

                <div class="row align-items-center">
                    <div class="col-12">
                        <div class="form-check form-switch d-flex align-items-center" style="gap: 1rem;">
                            <input class="form-check-input m-0" type="checkbox" role="switch" id="solicitar-factura" style="cursor: pointer; width: 3.5rem; height: 1.75rem;">
                            <label class="form-check-label fw-bold m-0" for="solicitar-factura" style="cursor: pointer; font-size: 1.1rem;">
                                Solicitar Factura (Aplica 21% IVA)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Datos de Facturación -->
                <div id="datos-facturacion" class="mt-4 p-4 border rounded-3 bg-light" style="display: none; border-left: 4px solid #0d6efd !important;">
                    
                    <div class="d-flex flex-wrap">

                        <div class="col-12 mb-4">
                            <h5 class="fw-bold text-primary">
                                <i class="fa-solid fa-file-invoice me-2"></i> Datos de Facturación
                            </h5>
                            <small class="text-muted">Complete la información fiscal para emitir la factura</small>
                        </div>
                        
                        <div class="col-md-4 mb-3 px-2">
                            <label class="fw-bold mb-1">
                                <i class="fa-solid fa-building me-1 text-primary"></i> Razón Social <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="razon-social" placeholder="Nombre de la empresa">
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <label class="fw-bold mb-1">
                                <i class="fa-solid fa-hashtag me-1 text-primary"></i> NIF / CIF <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="nif-cif" placeholder="Ej: B12345678">
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <label class="fw-bold mb-1">
                                <i class="fa-solid fa-envelope me-1 text-primary"></i> Email para Factura
                            </label>
                            <input type="email" class="form-control" id="email-factura" placeholder="email@ejemplo.com">
                        </div>

                        <div class="col-12 mb-2">
                            <label class="fw-bold mb-1">
                                <i class="fa-solid fa-location-dot me-1 text-primary"></i> Dirección Fiscal <span class="required">*</span>
                            </label>
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <input type="text" class="form-control" id="direccion-calle" placeholder="Calle, número, puerta">
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <input type="text" class="form-control" id="direccion-cp" placeholder="Código Postal">
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <input type="text" class="form-control" id="direccion-ciudad" placeholder="Ciudad">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Servicios -->
            <div class="mb-5 p-4">

                <div class="col-lg-12 col-md-12 col-sm-12 d-flex mb-3">
                    
                    <div class="col-lg-8 col-md-8 col-sm-12 p-0">
                        <h2 class="card-title">
                            <i class="fa-solid fa-handshake icon color-blue"></i>Servicios
                        </h2>    
                        <b class="text-muted">Elige una categoría y luego el servicio. La lista es corta y filtrada por categoría.</b>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <button class="btn btn-success add-service-btn float-end">
                            <i class="fa-solid fa-plus icon"></i> Añadir Servicio
                        </button>
                    </div>

                </div>

                <hr>

                <!--  Nuevo servicio -->
                <div class="d-flex flex-wrap service-item p-4 border rounded-3 bg-light mt-4" style="border-left: 4px solid #198754 !important;">

                    <div class="col-lg-3 col-md-3 col-sm-12 px-2">
                        <label class="fw-bold mb-1">Categoría</label>
                        <select class="form-control input-tall service-category" data-service-row="0">
                            <option value="">Selecciona una categoría</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-3 col-sm-12 px-2">
                        <label class="fw-bold mb-1">Servicio</label>
                        <select class="form-control input-tall service-select" data-service-row="0" disabled>
                            <option value="">Primero categoría</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-12 px-2">
                        <label class="fw-bold mb-1">Tipo Vehículo</label>
                        <select class="form-control input-tall vehicle-type" data-service-row="0">
                            <option value="">Selecciona un tipo</option>
                            @foreach($vehicleTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-1 col-sm-12 px-2">
                        <label class="fw-bold mb-1">Suciedad</label>
                        <select class="form-control input-tall service-dirt" data-service-row="0">
                            <option value="1">Bajo</option>
                            <option value="2">Medio</option>
                            <option value="3">Alto</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-1 col-sm-12 px-2">
                        <label class="fw-bold mb-1">Cant.</label>
                        <input type="number" class="form-control input-tall service-quantity" data-service-row="0" value="1" min="1">
                    </div>

                    <div class="col-lg-1 col-md-1 col-sm-12 px-2">
                        <label class="fw-bold mb-1">€</label>
                        <input type="number" class="form-control input-tall service-price" data-service-row="0" value="0" step="0.01" min="0" readonly>
                    </div>

                    <div class="col-lg-1 col-md-1 col-sm-12 d-flex align-items-center px-2" style="padding-top: 1.7rem;">
                        <button class="remove-btn btn btn-sm btn-danger"><i class="fa-solid fa-times"></i></button>
                    </div>
                </div>

                <!-- Descripción de la cita y notas adicionales -->
                <div class="service-box p-4 border rounded-3 bg-light mt-4" style="border-left: 4px solid #025bb5 !important;">

                    <div class="mb-3">
                        <label class="fw-bold text-primary small mb-2">
                            <i class="fa-solid fa-file-lines me-1"></i> Descripción de la cita (se genera automáticamente)
                        </label>
                        <textarea class="form-control" rows="2">Ninguno de nuestros precios incluye IVA.</textarea>
                    </div>

                    <small class="text-muted d-block mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Se actualiza automáticamente según los servicios elegidos. Puedes añadir notas adicionales abajo; se incorporan a la descripción.
                    </small>
                    
                    <div class="mb-0">
                        <label class="fw-bold mb-2 text-success small">
                            <i class="fa-solid fa-pen me-1"></i> Notas adicionales
                        </label>
                        <textarea class="form-control" rows="2" placeholder="Ej.: cliente espera; promo aplicada; aclaraciones..."></textarea>
                    </div>

                </div>

                <!-- Resumen de precios -->
                <div class="row" style="align-items: center; margin-top:1.5rem;">

                    <div class="input-group">
                        <label>% Aplicar Descuento</label>
                        <select class="input form-control" id="discount-select">
                            <option value="">Selecciona Descuento</option>
                            <option value="5">5%</option>
                            <option value="10">10%</option>
                            <option value="15">15%</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Subtotal</label>
                        <div class="subtotal-section" style="font-size:1.3rem;font-weight:600;">0.00€</div>
                    </div>

                    <div class="input-group">
                        <label>Descuento</label>
                        <div class="discount-section" style="font-size:1.3rem;font-weight:600;color:#dc3545;">-0.00€</div>
                    </div>

                    <div class="input-group">
                        <label>Total</label>
                        <div class="total-section" style="font-size:1.3rem;font-weight:700;">0.00€</div>
                    </div>

                </div>

            </div>

            <!-- Pago & métodos de pago -->
            <div class="mb-5 p-4 border rounded-3 bg-light" style="border-left: 4px solid #0d6efd !important;">

                <h2 class="card-title">
                    <i class="fa-solid fa-calendar-check icon color-blue"></i> Fecha, Hora y Pago
                </h2>
                
                <b class="text-muted">Selecciona la fecha y hora para el agendamiento del servicio.</b>

                <hr>

                <div class="col-12 d-flex justify-content-center align-items-center">

                    <!-- Pagos Calendario -->
                    <div class="col-6 d-flex justify-content-center align-items-center">

                        <div class="calendar-box calendar-enhanced">

                            <div class="calendar-header col-12">
                                <button class="calendar-nav">&#60;</button>
                                &nbsp;&nbsp;
                                <span class="calendar-month">noviembre <span class="calendar-year">2026</span></span>
                                &nbsp;&nbsp;
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

                    </div>

                    <!-- Pagos Formulario -->
                    <div class="col-6">

                        <div class="col-12 form-side d-flex flex-wrap">

                            <div class="col-6 px-2">
                                <label class="fw-bold">Hora Entrada <span class="required">*</span></label>
                                <input type="text" class="input form-control time-picker" id="hora-entrada" placeholder="Selecciona hora" readonly>
                                <!-- Fallback select (oculto por defecto) -->
                                <select class="input form-control time-picker-fallback" id="hora-entrada-fallback" style="display: none;">
                                    <option value="">Seleccionar</option>
                                    @for($h = 8; $h <= 20; $h++)
                                        @foreach(['00', '30'] as $m)
                                            <option value="{{ sprintf('%02d:%s:00', $h, $m) }}">{{ sprintf('%02d:%s', $h, $m) }}</option>
                                        @endforeach
                                    @endfor
                                </select>
                            </div>

                            <div class="col-6 px-2">
                                <label class="fw-bold">Hora Entrega <span class="required">*</span></label>
                                <input type="text" class="input form-control time-picker" id="hora-salida" placeholder="Selecciona hora" readonly>
                                <!-- Fallback select (oculto por defecto) -->
                                <select class="input form-control time-picker-fallback" id="hora-salida-fallback" style="display: none;">
                                    <option value="">Seleccionar</option>
                                    @for($h = 8; $h <= 20; $h++)
                                        @foreach(['00', '30'] as $m)
                                            <option value="{{ sprintf('%02d:%s:00', $h, $m) }}">{{ sprintf('%02d:%s', $h, $m) }}</option>
                                        @endforeach
                                    @endfor
                                </select>
                            </div>

                            <div class="col-12 my-5">
                                <label class="fw-bold px-3">Estado del Pago <span class="required">*</span></label>
                                <div class="pay-status-group mt-1 px-3 d-flex" style="gap: 1rem;">
                                    <button type="button" class="btn btn-outline-warning pay-status-btn pay-status-active">Pendiente</button>
                                    <button type="button" class="btn btn-outline-primary pay-status-btn">Parcial</button>
                                    <button type="button" class="btn btn-outline-success pay-status-btn">Pagado</button>
                                </div>
                            </div>

                            <div class="col-6 px-2 mt-2">
                                <label class="fw-bold">Método de Pago <span class="required">*</span></label>
                                <select class="input form-control">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                            </div>

                            <div class="col-6 px-2 mt-2">
                                <label class="fw-bold">Estado de la Cita <span class="required">*</span></label>
                                <select class="input form-control">
                                    <option value="1">Pendiente</option>
                                    <option value="2">En Proceso</option>
                                    <option value="3">Terminada</option>
                                </select>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Boton de envio -->
            <div class="col-12 text-center my-5">

                <label class="text-dark my-3">

                    <input type="checkbox">
                    He leído y acepto los 
                    <a href="#" style="color:var(--color-amarillo-logo);text-decoration:underline;">
                        Términos y Condiciones
                    </a>

                </label>

                <br>

                <button class="confirm-btn col-6" disabled>
                    <i class="fa-solid fa-check icon"></i>
                    Confirmar Agendamiento
                </button>

            </div>

            <!-- Listado de citas agendamiento -->
            <div class="card mt-4 p-4 rounded-4">

                <h2 class="citas-title">
                    <i class="fa-solid fa-list-check icon color-blue"></i>
                    Registro de Citas
                </h2>

                <div class="citas-tabs">

                    <button class="citas-tab" 
                            :class="currentTab === 'pending' ? 'citas-tab-active' : ''" 
                            @click="changeTab('pending')">
                        <i class="fa-solid fa-calendar icon"></i> Citas Pendientes
                    </button>

                    <button class="citas-tab" 
                            :class="currentTab === 'history' ? 'citas-tab-active' : ''" 
                            @click="changeTab('history')">
                        <i class="fa-solid fa-clock icon"></i> Historial Completo
                    </button>

                </div>

                <hr>

                <!-- Loading spinner -->
                <div x-show="loadingOrders" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>

                <!-- Sin resultados -->
                <div x-show="!loadingOrders && orders.length === 0" class="citas-content">
                    <p class="citas-empty" x-text="currentTab === 'pending' ? 'No hay citas pendientes.' : 'No hay citas en el historial.'"></p>
                </div>

                <!-- Tabla de citas -->
                <div x-show="!loadingOrders && orders.length > 0" class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Matrícula</th>
                                <th>Fecha</th>
                                <th>Hora Entrada</th>
                                <th>Hora Salida</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Detallador</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="order in orders" :key="order.id">
                                <tr>
                                    <td x-text="order.client ? order.client.name : 'N/A'"></td>
                                    <td x-text="order.service ? order.service.name : 'N/A'"></td>
                                    <td x-text="order.client ? order.client.license_plaque : 'N/A'"></td>
                                    <td x-text="formatDate(order.creation_date)"></td>
                                    <td x-text="formatTime(order.hour_in)"></td>
                                    <td x-text="formatTime(order.hour_out)"></td>
                                    <td x-text="formatCurrency(order.total)"></td>
                                    <td>
                                        <span :class="getStatusBadge(order.status)" x-text="getStatusText(order.status)"></span>
                                    </td>
                                    <td x-text="order.user ? order.user.name : 'N/A'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>

@endsection

<script>

document.addEventListener('DOMContentLoaded', function () {

    // Toggle de datos de facturación
    var toggleFactura = document.getElementById('solicitar-factura');
    var datosFacturacion = document.getElementById('datos-facturacion');
    var fieldsFactura = ['razon-social', 'nif-cif', 'email-factura', 'direccion-calle', 'direccion-cp', 'direccion-ciudad'];

    toggleFactura.addEventListener('change', function() {

        if (this.checked) {

            datosFacturacion.style.display = 'block';

            // Agregar required a los campos obligatorios
            document.getElementById('razon-social').required = true;
            document.getElementById('nif-cif').required = true;
            document.getElementById('direccion-calle').required = true;
            document.getElementById('direccion-cp').required = true;
            document.getElementById('direccion-ciudad').required = true;
            
        } else {

            datosFacturacion.style.display = 'none';

            // Quitar required y limpiar valores
            fieldsFactura.forEach(function(fieldId) {
                var field = document.getElementById(fieldId);
                field.required = false;
                field.value = '';
            });

        }

    });

});

</script>
