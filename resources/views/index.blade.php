@extends('layouts.base')

@section('content')

    <div id="orders-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;" 
         x-data="typeof orderFormApp === 'function' ? orderFormApp() : { showQuickViewModal: false, showStatusModal: false }" 
         x-init="typeof init === 'function' && init()">
        
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
                            <input type="text" class="input float-right" name="consecutive_serial" readonly data-field-name="Serial" style="width: 120px;" value="{{ $consecutive['date_code'] ?? '' }}">
                            <input type="text" class="input float-right" name="consecutive_number" 
                            id="consecutive_number" readonly value="{{ $consecutive['sequence'] ?? '' }}" data-field-name="Número" style="width: 70px;">
                        </div>

                    </div>

                </div>

                <hr>
                <!-- Formulario Cliente -->
                <div class="d-flex flex-wrap p-4 border rounded-3 bg-light mt-4" style="border-left: 4px solid #0d6efd !important;">

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Nombre Cliente <span class="required">*</span></label>
                        <input type="text" class="input form-control required-field" name="client_name" placeholder="Nombre completo" data-field-name="Nombre del Cliente">
                    </div>

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Teléfono <span class="required">*</span></label>
                        <input type="text" id="telefono-whatsapp" class="input form-control required-field phone-field" name="client_phone" placeholder="Ej: +34 612 345 678" maxlength="12" data-phone="true" required data-field-name="Teléfono">
                    </div>

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Matrícula <span class="required">*</span></label>
                        <input type="text" 
                               class="input form-control required-field license-plate-order" 
                               name="license_plaque" 
                               id="license-plaque-input"
                               placeholder="Ej: 1234 ABC" 
                               data-field-name="Matrícula"
                               maxlength="7"
                               style="text-transform: uppercase;">
                        <small id="license-plate-info" class="text-success" style="display: none;">
                            <i class="fa-solid fa-check-circle"></i> Cliente encontrado
                        </small>
                    </div>

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Tipo Vehículo  <span class="required">*</span></label>
                        <select class="input form-control required-field vehicle-type" name="vehicle_type_id" data-field-name="Tipo de Vehículo">
                            <option value="">Selecciona un tipo</option>
                            @foreach($vehicleTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-lg-3 col-md-3 col-sm-12 px-2">
                        <label class="fw-bold">Suciedad  <span class="required">*</span></label>
                        <select class="input form-control required-field service-dirt" name="dirt_level" data-field-name="Suciedad">
                            <option value="1">Bajo</option>
                            <option value="2">Medio</option>
                            <option value="3">Alto</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 px-2">
                        <label class="fw-bold">Asignar Detallador <span class="required">*</span></label>
                        <select class="input form-control required-field" name="assigned_user" data-field-name="Detallador">
                            <option value="">Seleccionar</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 px-2">
                        <div class="col-12" style="width: 100%;">
                            <label class="fw-bold">Observaciones</label>
                            <textarea class="input form-control form-control-lg" name="vehicle_notes" rows="5" placeholder="Anotaciones internas sobre el servicio, cliente o estado del vehículo..."></textarea>
                        </div>
                    </div>
                    
                </div>

                <hr>

                <div class="row align-items-center">

                    <div class="col-12">

                        <div class="form-check form-switch d-flex align-items-center" style="gap: 1rem;">
                            <input class="form-check-input m-0" type="checkbox" role="switch" name="invoice_required" id="solicitar-factura" style="cursor: pointer; width: 3.5rem; height: 1.75rem;">
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
                            <input type="text" class="form-control" name="invoice_business_name" id="razon-social" placeholder="Nombre de la empresa" data-field-name="Razón Social">
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <label class="fw-bold mb-1">
                                <i class="fa-solid fa-hashtag me-1 text-primary"></i> NIF / CIF <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" name="invoice_tax_id" id="nif-cif" placeholder="Ej: B12345678" data-field-name="NIF/CIF">
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <label class="fw-bold mb-1">
                                <i class="fa-solid fa-envelope me-1 text-primary"></i> Email para Factura
                            </label>
                            <input type="email" class="form-control email-field" name="invoice_email" id="email-factura" placeholder="email@ejemplo.com" data-field-name="Email de Factura">
                        </div>

                        <div class="col-12 mb-2">
                            <label class="fw-bold mb-1">
                                <i class="fa-solid fa-location-dot me-1 text-primary"></i> Dirección Fiscal <span class="required">*</span>
                            </label>
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <input type="text" class="form-control" name="invoice_address" id="direccion-calle" placeholder="Calle, número, puerta" data-field-name="Dirección">
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <input type="text" class="form-control" name="invoice_postal_code" id="direccion-cp" placeholder="Código Postal" data-field-name="Código Postal">
                        </div>

                        <div class="col-md-4 mb-3 px-2">
                            <input type="text" class="form-control" name="invoice_city" id="direccion-ciudad" placeholder="Ciudad" data-field-name="Ciudad">
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
                        <select class="form-control input-tall required-field service-category" data-service-row="0" data-field-name="Categoría">
                            <option value="">Selecciona una categoría</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-lg-3 col-md-3 col-sm-12 px-2">
                        <label class="fw-bold mb-1">Servicio</label>
                        <select class="form-control input-tall required-field service-select" name="service_id" data-service-row="0" disabled data-field-name="Servicio">
                            <option value="">Seleccionar servicio</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 px-2">
                        <label class="fw-bold mb-1">Cant.</label>
                        <input type="number" class="form-control required-field input-tall service-quantity" name="quantity" data-service-row="0" value="1" min="1" data-field-name="Cantidad">
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-12 px-2">
                        <label class="fw-bold mb-1">€</label>
                        <input type="number" class="form-control required-field input-tall service-price" name="price" data-service-row="0" value="0" step="0.01" min="0" readonly data-field-name="Precio">
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
                        <textarea class="form-control form-control-lg" name="order_notes" rows="4">Ninguno de nuestros precios incluye IVA.</textarea>
                    </div>

                    <small class="text-muted d-block mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Se actualiza automáticamente según los servicios elegidos. Puedes añadir notas adicionales abajo; se incorporan a la descripción.
                    </small>
                    
                    <div class="mb-0">
                        <label class="fw-bold mb-2 text-success small">
                            <i class="fa-solid fa-pen me-1"></i> Notas adicionales
                        </label>
                        <textarea class="form-control form-control-lg" name="extra_notes" rows="4" placeholder="Ej.: cliente espera; promo aplicada; aclaraciones..."></textarea>
                    </div>

                </div>

                <!-- Resumen de precios -->
                <div class="row" style="align-items: center; margin-top:1.5rem;">

                    <div class="col-3">
                        <label class="fw-bold">% Aplicar Descuento</label>
                        <select class="input form-control" name="discount" id="discount-select" style="font-size: 1.1rem; min-height: 42px;">
                            <option value="">Selecciona Descuento</option>
                            <option value="5">5%</option>
                            <option value="10">10%</option>
                            <option value="15">15%</option>
                        </select>
                    </div>

                    <div class="col-2">
                        <label class="fw-bold">Subtotal</label>
                        <div class="subtotal-section" style="font-size:1.3rem;font-weight:600;">0.00€</div>
                        <input type="hidden" class="subtotal-value" name="subtotal" value="0.00">
                    </div>

                    <div class="col-2">
                        <labe class="fw-bold">Descuento</label>
                        <div class="discount-section" style="font-size:1.3rem;font-weight:600;color:#dc3545;">-0.00€</div>
                        <input type="hidden" class="discount-value" name="discount_value" value="0.00">
                    </div>

                    <div class="col-2">
                        <label class="fw-bold">Total</label>
                        <div class="total-section" style="font-size:1.3rem;font-weight:700;">0.00€</div>
                        <input type="hidden" class="total-value" name="total" value="0.00">
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
                                <input type="text" class="input form-control required-field time-picker" id="hora-entrada" placeholder="Selecciona hora" readonly data-field-name="Hora de Entrada">
                                
                                <!-- Fallback select (oculto por defecto) -->
                                <select class="input form-control required-field time-picker-fallback" name="hour_in" id="hora-entrada-fallback" style="display: none;" data-field-name="Hora de Entrada">
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
                                <input type="text" class="input form-control required-field time-picker" id="hora-salida" placeholder="Selecciona hora" readonly data-field-name="Hora de Salida">
                                
                                <!-- Fallback select (oculto por defecto) -->
                                <select class="input form-control required-field time-picker-fallback" name="hour_out" id="hora-salida-fallback" style="display: none;" data-field-name="Hora de Salida">
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
                                    <button type="button" class="btn btn-outline-warning pay-status-btn pay-status-active" data-value="1">Pendiente</button>
                                    <button type="button" class="btn btn-outline-primary pay-status-btn" data-value="2">Parcial</button>
                                    <button type="button" class="btn btn-outline-success pay-status-btn" data-value="3">Pagado</button>
                                </div>

                                <input type="hidden" name="payment_status" class="payment-status-input" value="1">
                            </div>

                            <div class="col-6 px-2 mt-2" id="partial-payment-container" style="display: none;">
                                <label class="fw-bold">Abono Parcial <span class="required">*</span></label>
                                <input type="number" class="input form-control" name="partial_payment" id="partial-payment-input" placeholder="0.00" step="0.01" min="0" style="font-size: 1.1rem; min-height: 42px;" data-field-name="Abono Parcial">
                                <small class="text-muted">Ingresa el monto del pago parcial</small>
                            </div>

                            <div class="col-6 px-2 mt-2">
                                <label class="fw-bold">Método de Pago <span class="required">*</span></label>
                                <select class="input form-control required-field" name="payment_method" style="font-size: 1.1rem; min-height: 42px;" data-field-name="Método de Pago">
                                    <option value="1">Efectivo</option>
                                    <option value="2">Tarjeta</option>
                                    <option value="3">Transferencia</option>
                                </select>
                            </div>

                            <div class="col-6 px-2 mt-2">
                                <label class="fw-bold">Estado de la Cita <span class="required">*</span></label>
                                <select class="input form-control required-field" name="status" data-field-name="Estado de la Cita">
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

                    <input type="checkbox" class="me-2" id="terms-checkbox" style="width: 1.2rem; height: 1.2rem; cursor: pointer;">
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

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <h2 class="citas-title mb-0">
                        <i class="fa-solid fa-list-check icon color-blue"></i>
                        Registro de Citas
                    </h2>
                    <div class="position-relative" style="max-width: 350px; width: 100%;">
                        <input type="text"
                               x-model="searchTerms[currentTab]"
                               @input="resetPagination(currentTab)"
                               class="form-control pe-5"
                               placeholder="Buscar citas...">
                        <i class="fa-solid fa-search position-absolute"
                           style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                    </div>
                </div>

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
                <div x-show="!loadingOrders && getFilteredOrders().length === 0" class="citas-content">
                    <p class="citas-empty" x-text="searchTerms[currentTab] ? 'No se encontraron resultados.' : (currentTab === 'pending' ? 'No hay citas pendientes.' : 'No hay citas en el historial.')"></p>
                </div>

                <!-- Tabla de citas -->
                <div x-show="!loadingOrders && getFilteredOrders().length > 0" class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>Cliente</th>
                                <th>Placa</th>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Detallador</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <template x-for="order in getPaginatedOrders()" :key="order.id">
                                <tr>
                                    <td x-text="order.client ? order.client.name : 'N/A'"></td>
                                    <td x-text="order.client ? order.client.license_plaque : 'N/A'"></td>
                                    <td>
                                        <template x-for="service in order.services" :key="service.id">
                                            <div x-text="service.name"></div>
                                        </template>
                                    </td>
                                    <td x-text="formatDate(order.creation_date)"></td>
                                    <td x-text="formatTime(order.hour_in)"></td>
                                    <td x-text="formatTime(order.hour_out)"></td>
                                    <td x-text="formatCurrency(order.total)"></td>
                                    <td>
                                        <span :class="getStatusBadge(order.status)" x-text="getStatusText(order.status)"></span>
                                    </td>
                                    <td x-text="order.user ? order.user.name : 'N/A'"></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button @click="openQuickView(order)" class="btn btn-info" title="Ver detalles">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button @click="openStatusModal(order)" class="btn btn-warning" title="Cambiar estado">
                                                <i class="fa-solid fa-exchange-alt"></i>
                                            </button>
                                            <a :href="'/orders/' + order.id + '/edit'" class="btn btn-primary" title="Editar">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                        </tbody>

                    </table>

                </div>

                <!-- Paginador -->
                <div x-show="!loadingOrders && getTotalPages() > 1" class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Página <span x-text="currentPage[currentTab]"></span> de <span x-text="getTotalPages()"></span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="currentPage[currentTab] === 1 ? 'disabled' : ''">
                                <button class="page-link" @click="goToPage(currentTab, currentPage[currentTab] - 1)">«</button>
                            </li>
                            <template x-for="page in getTotalPages()" :key="page">
                                <li class="page-item" :class="page === currentPage[currentTab] ? 'active' : ''">
                                    <button class="page-link" @click="goToPage(currentTab, page)" x-text="page"></button>
                                </li>
                            </template>
                            <li class="page-item" :class="currentPage[currentTab] === getTotalPages() ? 'disabled' : ''">
                                <button class="page-link" @click="goToPage(currentTab, currentPage[currentTab] + 1)">»</button>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>

        </div>

        <!-- Modales -->
        @include('orders.partials._quick-actions')
        @include('orders.modals._change-status')

    </div>

    <!-- Datos de la orden a editar (si existe) -->
    @if(isset($editOrder))
        <script>
            window.editOrderData = @json($editOrder);
        </script>
    @endif

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

                // Agregar required y clase de validación a los campos obligatorios
                document.getElementById('razon-social').required = true;
                document.getElementById('razon-social').classList.add('required-field');
                
                document.getElementById('nif-cif').required = true;
                document.getElementById('nif-cif').classList.add('required-field');
                
                document.getElementById('direccion-calle').required = true;
                document.getElementById('direccion-calle').classList.add('required-field');
                
                document.getElementById('direccion-cp').required = true;
                document.getElementById('direccion-cp').classList.add('required-field');
                
                document.getElementById('direccion-ciudad').required = true;
                document.getElementById('direccion-ciudad').classList.add('required-field');
                
            } else {

                datosFacturacion.style.display = 'none';

                // Quitar required, clase de validación y limpiar valores
                fieldsFactura.forEach(function(fieldId) {
                    var field = document.getElementById(fieldId);
                    field.required = false;
                    field.classList.remove('required-field', 'is-invalid', 'is-valid');
                    field.value = '';
                });

            }

        });

    });

</script>
