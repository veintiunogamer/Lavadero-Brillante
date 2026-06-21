@extends('layouts.base')

@section('content')

<div id="orders-root" class="d-flex justify-content-center align-items-start" style="min-height: 80vh; padding-top: 2rem;"
    x-data="typeof orderFormApp === 'function' ? orderFormApp() : { showQuickViewModal: false, showStatusModal: false, showInvoiceModal: false }"
    x-init="typeof init === 'function' && init()">

    <div class="card shadow-lg rounded-4 bg-white p-4 w-100" style="max-width: 1400px;">

        <!-- Datos del Cliente y Vehículo -->
        <div class="my-4 p-4">

            <!-- Cabezera -->
            <div class="col-12 d-flex mb-4 flex-wrap">

                <div class="col-lg-10 col-md-6 col-sm-12 d-flex flex-column justify-content-center">
                    <h1 class="m-0 fw-bold">
                        <i class="fa-solid fa-list-check icon color-blue"></i>
                        FORMULARIO DE AGENDAMIENTO
                    </h1>
                    <span class="text-muted fw-bold">Aqui podra diligenciar su orden sin ningun problema.</span>
                </div>

                <div class="col-lg-2 col-md-6 col-sm-12 d-flex flex-column justify-content-center text-center">

                    <label class="fw-bold">
                        <i class="fa-solid fa-hashtag text-primary me-1"></i>
                        Consecutivo
                    </label>
                    <div class="d-flex justify-content-end" style="gap: 0.5rem;">
                        <input type="text" class="input float-right" name="consecutive_serial" readonly data-field-name="Serial" style="width: 120px;" value="{{ $consecutive['date_code'] ?? '' }}">
                        <input type="text" class="input float-right" name="consecutive_number"
                            id="consecutive_number" readonly value="{{ $consecutive['sequence'] ?? '' }}" data-field-name="Número" style="width: 70px;">
                    </div>

                </div>

            </div>

            <!-- Formulario Cliente -->
            <div class="d-flex flex-wrap p-4 border border-blue rounded-3 bg-light mt-4">

                <div class="col-12 mb-5 pb-3 border-bottom">
                    <h2 class="fw-bold mb-1">
                        <i class="fa-solid fa-car icon text-secondary"></i>
                        Datos del Cliente y Vehículo
                    </h2>
                    <span class="text-muted fw-bold">Información básica para agendar el servicio.</span>
                </div>

                <!-- Datos del cliente -->
                <div class="col-lg-6 col-md-6 col-sm-12 d-flex flex-wrap mb-5 pb-3">

                    <div class="col-md-6 mb-3 px-2">
                        <label class="fw-bold">Nombre Cliente<span class="required">*</span></label>
                        <input type="text" class="input form-control required-field" name="client_name" placeholder="Nombre completo" maxlength="30" data-field-name="Nombre del Cliente">
                    </div>

                    <div class="col-md-3 mb-3 px-2 d-flex flex-column align-items-start">
                        <label class="form-check-label fw-bold mb-3">Flota<span class="required">*</span></label>
                        <input class="ms-0" type="checkbox" role="switch" name="fleet" id="fleet">
                    </div>

                    <div class="col-md-3 mb-3 px-2 d-flex flex-column align-items-start">

                        <label class="form-check-label fw-bold mb-3" for="get-invoice">
                            Factura
                        </label>
                        <input class="ms-0" type="checkbox" role="switch" name="invoice_required" id="get-invoice">

                    </div>

                    <div class="col-md-6 mb-3 px-2">
                        <label class="fw-bold">Teléfono <span class="required">*</span></label>
                        <input type="text" id="telefono-whatsapp" class="input form-control required-field phone-field" name="client_phone" placeholder="Ej: +34 612 345 678" maxlength="12" data-phone="true" required data-field-name="Teléfono">
                    </div>

                    <div class="col-md-6 mb-3 px-2">
                        <label class="fw-bold d-flex justify-content-between align-items-center gap-2">
                            <span>Matrícula <span class="required">*</span></span>
                            <small id="license-plate-info" class="text-success text-nowrap" style="display: none;">
                                <i class="fa-solid fa-check-circle"></i> Cliente encontrado
                            </small>
                        </label>
                        <input type="text"
                            class="input form-control required-field license-plate-order border-primary"
                            name="license_plaque"
                            id="license-plaque-input"
                            placeholder="Ej: 1234 ABC"
                            data-field-name="Matrícula"
                            maxlength="7"
                            style="text-transform: uppercase;">
                    </div>

                    <div class="col-md-6 mb-3 px-2">
                        <label class="fw-bold">Modelo</label>
                        <input type="text"
                            class="input form-control"
                            name="client_brand"
                            id="client-brand-input"
                            placeholder="Ej: Toyota Corolla"
                            maxlength="50"
                            data-field-name="Modelo">
                    </div>

                    <div class="col-md-6 mb-3 px-2">
                        <label class="fw-bold">Tipo Vehículo <span class="required">*</span></label>
                        <select class="input form-control required-field vehicle-type" name="vehicle_type_id" data-field-name="Tipo de Vehículo">
                            <option value="">Selecciona un tipo</option>
                            @foreach($vehicleTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-lg-6 col-md-6 col-sm-12 px-2">
                        <label class="fw-bold">Suciedad <span class="required">*</span></label>
                        <select class="input form-control required-field service-dirt" name="dirt_level" data-field-name="Suciedad">
                            <option value="1">Bajo</option>
                            <option value="2">Medio</option>
                            <option value="3">Alto</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3 px-2">
                        <label class="fw-bold">Lava Coches <span class="required">*</span></label>
                        <select class="input form-control required-field" name="assigned_user" data-field-name="Detallador">
                            <option value="">Seleccionar</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-6 px-2">

                        <label class="fw-bold">Hora Entrada <span class="required">*</span></label>
                        <input type="text" class="input form-control required-field time-picker" id="hora-entrada" placeholder="Selecciona hora" readonly data-field-name="Hora de Entrada">

                        <!-- Fallback select (oculto por defecto) -->
                        <select class="input form-control required-field time-picker-fallback" name="hour_in" id="hora-entrada-fallback" style="display: none;" data-field-name="Hora de Entrada">
                            <option value="">Seleccionar</option>
                            @for($h = 8; $h <= 20; $h++)
                                @foreach(['00', '30' ] as $m)
                                <option value="{{ sprintf('%02d:%s:00', $h, $m) }}">{{ sprintf('%02d:%s', $h, $m) }}</option>
                                @endforeach
                                @endfor
                        </select>

                    </div>

                    <div class="col-6 px-2">

                        <label class="fw-bold">Hora Salida <span class="required">*</span></label>
                        <input type="text" class="input form-control required-field time-picker" id="hora-salida" placeholder="Selecciona hora" readonly data-field-name="Hora de Salida">

                        <!-- Fallback select (oculto por defecto) -->
                        <select class="input form-control required-field time-picker-fallback" name="hour_out" id="hora-salida-fallback" style="display: none;" data-field-name="Hora de Salida">
                            <option value="">Seleccionar</option>
                            @for($h = 8; $h <= 20; $h++)
                                @foreach(['00', '30' ] as $m)
                                <option value="{{ sprintf('%02d:%s:00', $h, $m) }}">{{ sprintf('%02d:%s', $h, $m) }}</option>
                                @endforeach
                                @endfor
                        </select>

                    </div>


                </div>

                <!-- Fecha y hora de reserva -->
                <div class="col-lg-6 col-md-6 col-sm-12 d-flex justify-content-center align-items-center">

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
                                    <th>Lu</th>
                                    <th>Ma</th>
                                    <th>Mi</th>
                                    <th>Ju</th>
                                    <th>Vi</th>
                                    <th>Sa</th>
                                    <th>Do</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="calendar-muted">1</td>
                                    <td class="calendar-muted">2</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>4</td>
                                    <td>5</td>
                                    <td>6</td>
                                    <td>7</td>
                                    <td>8</td>
                                    <td>9</td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td>11</td>
                                    <td class="calendar-active">12</td>
                                    <td>13</td>
                                    <td>14</td>
                                    <td>15</td>
                                    <td>16</td>
                                </tr>
                                <tr>
                                    <td>17</td>
                                    <td>18</td>
                                    <td>19</td>
                                    <td>20</td>
                                    <td>21</td>
                                    <td>22</td>
                                    <td>23</td>
                                </tr>
                                <tr>
                                    <td>24</td>
                                    <td>25</td>
                                    <td>26</td>
                                    <td>27</td>
                                    <td>28</td>
                                    <td>29</td>
                                    <td>30</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="calendar-footer">
                            <span class="calendar-tip">Selecciona una fecha para agendar</span>
                        </div>
                    </div>

                </div>

                <div class="col-12 px-2 my-4">
                    <div class="col-12" style="width: 100%;">
                        <label class="fw-bold">
                            <i class="fa fa-list text-primary"></i>&nbsp;
                            Observaciones
                        </label>
                        <textarea class="input form-control form-control-lg" name="vehicle_notes" rows="5" maxlength="250" placeholder="Anotaciones internas sobre el servicio, cliente o estado del vehículo..."></textarea>
                    </div>
                </div>

            </div>

            <!-- Datos de Facturación -->
            <div id="datos-facturacion" class="my-4 p-4 border border-blue rounded-3 bg-light"
                style="display: none;">

                <div class="d-flex flex-wrap">

                    <div class="col-12 mb-4 pb-3 border-bottom">
                        <h2 class="fw-bold mb-1">
                            <i class="fa-solid fa-file-invoice text-secondary"></i>
                            Datos de Facturación
                        </h2>
                        <small class="text-muted d-block fw-bold">Complete la información fiscal para emitir la factura</small>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3 px-2">
                        <label class="fw-bold mb-1">
                            Razón Social <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" name="invoice_business_name" id="razon-social" maxlength="40" placeholder="Nombre de la empresa" data-field-name="Razón Social">
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3 px-2">
                        <label class="fw-bold mb-1">
                            NIF / CIF <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" name="invoice_tax_id" id="nif-cif" placeholder="Ej: B12345678" maxlength="10" data-field-name="NIF/CIF">
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3 px-2">
                        <label class="fw-bold mb-1">
                            Email
                        </label>
                        <input type="email" class="form-control email-field" name="invoice_email" id="email-factura" maxlength="40" placeholder="email@ejemplo.com" data-field-name="Email de Factura">
                    </div>


                    <div class="col-lg-3 col-md-3 col-sm-12 mb-3 px-2">
                        <label class="fw-bold mb-1">
                            Telefono
                        </label>
                        <input type="text" class="form-control" name="invoice_phone" id="telefono-factura" maxlength="15" placeholder="Ej: 123456789" data-field-name="Telefono de Factura">
                    </div>

                    <hr>

                    <div class="col-12 mb-2 my-2">
                        <label class="fw-bold mb-1">
                            <i class="fa-solid fa-location-dot me-1 text-primary"></i> Dirección Fiscal <span class="required">*</span>
                        </label>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3 px-2 my-2">
                        <input type="text" class="form-control" name="invoice_address" id="direccion-calle" maxlength="40" placeholder="Calle, número, puerta" data-field-name="Dirección">
                    </div>

                    <div class="col-lg-4 col-md-3 col-sm-12 mb-3 px-2 my-2">
                        <input type="text" class="form-control" name="invoice_city" id="direccion-ciudad" maxlength="20" placeholder="Ciudad" data-field-name="Ciudad">
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3 px-2 my-2">
                        <input type="text" class="form-control" name="invoice_postal_code" id="direccion-cp" maxlength="7" placeholder="Código Postal" data-field-name="Código Postal">
                    </div>

                </div>

            </div>

            <!-- Servicios -->
            <div class="bg-light border border-green rounded-3 my-5 p-4">

                <div class="col-lg-12 col-md-12 col-sm-12 d-flex pb-3 border-bottom">

                    <div class="col-lg-8 col-md-8 col-sm-12 p-0">
                        <h2 class="fw-bold mb-1">
                            <i class="fa-solid fa-handshake icon text-secondary"></i>Servicios
                        </h2>
                        <b class="text-muted">Elige una categoría y luego el servicio. La lista es corta y filtrada por categoría.</b>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-12 d-flex justify-content-end align-items-center p-0">
                        <button class="btn btn-success add-service-btn btn-lg float-end">
                            <i class="fa-solid fa-plus icon"></i> Añadir Servicio
                        </button>
                    </div>

                </div>

                <!--  Nuevo servicio -->
                <div class="d-flex flex-wrap service-item p-4 border border-success border-2 rounded-3 bg-light mt-4">

                    <div class="col-lg-3 col-md-3 col-sm-12 px-2">
                        <label class="fw-bold mb-1">
                            Categoría <span class="required">*</span>
                        </label>
                        <select class="form-control input-tall required-field service-category" data-service-row="0" data-field-name="Categoría">
                            <option value="">Selecciona una categoría</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 px-2">
                        <label class="fw-bold mb-1">
                            Servicio <span class="required">*</span>
                        </label>
                        <select class="form-control input-tall required-field service-select" name="service_id" data-service-row="0" disabled data-field-name="Servicio">
                            <option value="">Seleccionar servicio</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-12 px-2">
                        <label class="fw-bold mb-1">
                            Cantidad <span class="required">*</span>
                        </label>
                        <input type="number" class="form-control required-field input-tall service-quantity" name="quantity" data-service-row="0" value="1" min="1" max="99" step="1" data-field-name="Cantidad">
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-12 px-2">

                        <label class="fw-bold mb-1 d-flex justify-content-between align-items-center">
                            <span class="price-label-text">
                                Precio <span class="required">*</span>
                            </span>

                            <button type="button" class="btn btn-outline-success btn-sm price-edit-btn" title="Editar precio" style="padding: 2px 6px;">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                        </label>

                        <input type="text" class="form-control required-field input-tall service-price price-input" name="price" data-service-row="0" value="0.00" readonly data-field-name="Precio">

                    </div>

                </div>

                <!-- Descripción de la cita y notas adicionales -->
                <div class="col-lg-12 col-md-12 col-sm-12 d-flex flex-wrap service-box p-4 mt-4">

                    <div class="col-lg-6 col-md-6 col-sm-12 px-2">

                        <label class="fw-bold mb-2">
                            <i class="fa-solid fa-message text-primary me-1"></i>
                            Descripción de la cita (se genera automáticamente)
                        </label>

                        <textarea class="form-control form-control-lg" name="order_notes" rows="4" maxlength="250">
                            Ninguno de nuestros precios incluye IVA.
                        </textarea>

                        <small class="badge bg-secondary my-2 text-left">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Se actualiza automáticamente según los servicios elegidos.
                        </small>

                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 px-2">
                        <label class="fw-bold mb-2">
                            <i class="fa-solid fa-plus-circle text-primary me-1"></i> Notas adicionales
                        </label>
                        <textarea class="form-control form-control-lg" name="extra_notes" rows="4" maxlength="100" placeholder="Ej.: cliente espera; promo aplicada; aclaraciones..."></textarea>
                    </div>

                </div>

            </div>

            <!-- Pago & métodos de pago -->
            <div class="mb-5 p-4 border border-yellow rounded-3 bg-light">

                <div class="col-12  pb-3 border-bottom">

                    <h2 class="fw-bold mb-1">
                        <i class="fa-solid fa-credit-card text-secondary"></i>
                        Pago & Métodos de Pago
                    </h2>
                    <small class="text-muted fw-bold">Selecciona la fecha y hora para agendar, luego elige el estado del pago.</small>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 d-flex flex-wrap mt-4">

                    <!-- Pagos Formulario -->
                    <div class="col-lg-6 col-md-6 col-sm-12 d-flex flex-wrap bg-light p-3 rounded-3">

                        <div class="col-12 my-5">
                            <label class="fw-bold px-3">Estado del Pago <span class="required">*</span></label>
                            <div class="pay-status-group mt-1 px-3 d-flex" style="gap: 1rem;">
                                <button type="button" class="btn btn-outline-warning pay-status-btn pay-status-active" data-value="1">Pendiente</button>
                                <button type="button" class="btn btn-outline-primary pay-status-btn" data-value="2">Parcial</button>
                                <button type="button" class="btn btn-outline-success pay-status-btn" data-value="3">Pagado</button>
                            </div>

                            <input type="hidden" name="payment_status" class="payment-status-input" value="1">
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 px-2 mt-2">
                            <label class="fw-bold">Período de Pago <span class="required">*</span></label>
                            <select class="input form-control required-field" name="payment_period" id="payment-period-select" style="font-size: 1.1rem; min-height: 42px;" data-field-name="Período de Pago">
                                <option value="1" selected>Único</option>
                                <option value="2">Mensual</option>
                            </select>
                            <small class="badge bg-secondary my-2">Al elegir Mensual, el calendario se desactiva.</small>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 px-2 mt-2">
                            <label class="fw-bold">Método de Pago <span class="required">*</span></label>
                            <select class="input form-control required-field" name="payment_method" style="font-size: 1.1rem; min-height: 42px;" data-field-name="Método de Pago">
                                <option value="1">Efectivo</option>
                                <option value="2">TPV</option>
                                <option value="3">Transferencia</option>
                            </select>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 px-2 mt-2">
                            <label class="fw-bold">Estado de la Cita <span class="required">*</span></label>
                            <select class="input form-control required-field" name="status" data-field-name="Estado de la Cita">
                                <option value="1">Pendiente</option>
                                <option value="2">En Proceso</option>
                                <option value="3">Terminada</option>
                            </select>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 px-2 mt-2" id="partial-payment-container" style="display: none;">
                            <label class="fw-bold">Abono Parcial <span class="required">*</span></label>
                            <input type="number" class="input form-control" name="partial_payment" id="partial-payment-input" placeholder="0.00" step="0.01" min="0" style="font-size: 1.1rem; min-height: 42px;" data-field-name="Abono Parcial">
                            <small class="badge bg-secondary my-2">Ingresa el monto del pago parcial</small>
                        </div>

                    </div>

                    <!-- Seccion de totales, descuentos e IVA -->
                    <div class="col-lg-6 col-md-6 col-sm-12 d-flex flex-wrap bg-light p-3 rounded-3">

                        <div class="col-lg-12 col-md-12 col-sm-12 px-2 mt-2">
                            <label class="fw-bold">Descuento</label>
                            <select class="form-control" name="discount" id="discount-select" style="font-size: 1.1rem; min-height: 40px;">
                                <option value="">Selecciona Descuento</option>
                                <option value="5">5%</option>
                                <option value="10">10%</option>
                                <option value="15">15%</option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 text-center">
                            <label class="fw-bold">Subtotal</label>
                            <div class="subtotal-section" style="font-size:1.3rem;font-weight:600;">0.00€</div>
                            <input type="hidden" class="subtotal-value" name="subtotal" value="0.00">
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 text-center">
                            <label class="fw-bold">Descuento</label>
                            <div class="discount-section" style="font-size:1.3rem;font-weight:600;color:#dc3545;">-0.00€</div>
                            <input type="hidden" class="discount-value" name="discount_value" value="0.00">
                        </div>


                        <div class="col-2 text-center">
                            <label class="fw-bold">IVA</label>
                            <div class="tax-section" style="font-size:1.3rem;font-weight:600;color:green;">0.00€</div>
                            <input type="hidden" class="tax-value" name="tax_value" value="0.00">
                        </div>

                        <div class="col-2 text-center">
                            <label class="fw-bold">Total</label>
                            <div class="total-section" style="font-size:1.3rem;font-weight:700;">0.00€</div>
                            <input type="hidden" class="total-value" name="total" value="0.00">
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

        </div>

        <!-- Listado de citas agendamiento -->
        <div class="card mt-4 p-4 rounded-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">
                <h2 class="citas-title mb-0">
                    <i class="fa-solid fa-list-check icon color-blue"></i>
                    Registro de Citas
                </h2>
            </div>

            <!-- Tabs para filtrar entre citas pendientes y historial -->
            <div class="citas-tabs">

                <button class="citas-tab"
                    :class="currentTab === 'pending' ? 'citas-tab-active' : ''"
                    @click="changeTab('pending')">
                    <i class="fa-solid fa-calendar icon"></i> Citas
                </button>

                <button class="citas-tab"
                    :class="currentTab === 'history' ? 'citas-tab-active' : ''"
                    @click="changeTab('history')">
                    <i class="fa-solid fa-clock icon"></i> Historial
                </button>

            </div>

            <!-- Filtro de búsqueda -->
            <div class="col-12 filter-section 
            d-flex flex-wrap align-items-center">

                <div class="col-12 border-bottom mb-1">
                    <label class="fw-bold mb-1 fs-5">
                        <i class="fa-solid fa-filter text-primary me-1"></i>
                        Filtros de búsqueda
                    </label>
                </div>

                <div class="col-3 p-1 mt-2">
                    <input type="text"
                        x-model="searchTerms[currentTab]"
                        @input="resetPagination(currentTab)"
                        class="form-control pe-5 border border-3"
                        placeholder="Buscar citas...">
                </div>

            </div>

            <!-- Loading spinner -->
            <div x-show="loadingOrders" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>

            <!-- Sin resultados -->
            <div x-show="!loadingOrders && getFilteredOrders().length === 0" class="citas-empty-state">

                <template x-if="searchTerms[currentTab]">
                    <div>
                        <i class="fa-solid fa-magnifying-glass citas-empty-icon" style="color:#93c5fd;"></i>
                        <p class="citas-empty-title">Sin resultados</p>
                        <p class="citas-empty-sub">No se encontraron citas para <strong x-text="'\"' + searchTerms[currentTab] + ' \"'"></strong>.<br>Intenta con otro término de búsqueda.</p>
                    </div>
                </template>

                <template x-if="!searchTerms[currentTab] && currentTab === 'pending'">
                    <div>
                        <i class="fa-solid fa-calendar-check citas-empty-icon" style="color:#86efac;"></i>
                        <p class="citas-empty-title">¡Todo al día!</p>
                        <p class="citas-empty-sub">No hay citas pendientes en este momento.<br>Usa el formulario superior para agendar una nueva cita.</p>
                    </div>
                </template>

                <template x-if="!searchTerms[currentTab] && currentTab === 'history'">
                    <div>
                        <i class="fa-solid fa-clock-rotate-left citas-empty-icon" style="color:#bfdbfe;"></i>
                        <p class="citas-empty-title">Sin historial aún</p>
                        <p class="citas-empty-sub">Las citas completadas y canceladas aparecerán aquí.</p>
                    </div>
                </template>

            </div>

            <!-- Tabla de citas -->
            <div x-show="!loadingOrders && getFilteredOrders().length > 0" class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Flota</th>
                            <th>Servicio</th>
                            <th>Detallador</th>
                            <th>Pago</th>
                            <th>Metodo</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <template x-for="order in getPaginatedOrders()" :key="order.id">
                            <tr>
                                <td x-html="
                                    `${formatDate(order.date)}
                                    <br>
                                    <span class='badge bg-success'>${formatTime(order.hour_in)}</span> -
                                    <span class='badge bg-danger'>${formatTime(order.hour_out)}</span>`
                                ">
                                </td>
                                <td x-html="order.client ? order.client.name + '<br>' + order.client.license_plaque : '--'"></td>
                                <td x-text="order.client && order.client.fleet ? 'Sí' : 'No'"></td>
                                <td>
                                    <template x-for="service in order.services" :key="service.id">
                                        <div x-text="service.name"></div>
                                    </template>
                                </td>
                                <td x-text="order.user ? order.user.name : '--'"></td>
                                <td>
                                    <span class="badge" :class="getPaymentStatusBadge(order.payment?.status)" x-text="getPaymentStatusText(order.payment?.status)"></span>
                                </td>
                                <td x-text="getPaymentMethodText(order.payment?.type)"></td>
                                <td>
                                    <span :class="getStatusBadge(order.status)" x-text="getStatusText(order.status)"></span>
                                </td>

                                <td x-text="formatCurrency(order.total)"></td>

                                <td class="text-center">

                                    <div class="btn-group btn-group-md">

                                        <button @click="openQuickView(order)" class="btn btn-info" title="Ver detalles">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <button @click="openStatusTypeModal(order)" class="btn btn-success" title="Cambiar estado"
                                            x-show="order.status !== 3 || order.payment?.status !== 3">
                                            <i class="fa-solid fa-exchange-alt"></i>
                                        </button>

                                        <a :href="'/orders/' + order.id + '/edit'" class="btn btn-primary" title="Editar Orden" x-show="order.status !== 3">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>

                                        <button @click="printOrder(order.id)" class="btn btn-warning" title="Imprimir Orden">
                                            <i class="fa-solid fa-print"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        </template>

                    </tbody>

                </table>

            </div>

            <!-- Paginador -->
            <div x-show="!loadingOrders && getTotalPages() > 1" class="d-flex justify-content-between align-items-center mt-3">

                <div class="badge bg-primary text-light p-2 col-1">
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
    @include('orders.modals._select-status-type')
    @include('orders.modals._change-payment')

    <!-- Modal Factura -->
    <div x-cloak x-show="showInvoiceModal" x-transition.opacity class="invoice-modal-backdrop" @click="closeInvoiceModal()" @keydown.escape.window="closeInvoiceModal()">
        <div class="invoice-modal" @click.stop>
            <h5 class="mb-2">¿Factura?</h5>
            <p class="text-muted mb-3">¿Deseas generar la factura en PDF?</p>
            <div class="invoice-modal-actions">
                <button class="btn btn-outline-secondary" type="button" @click="closeInvoiceModal()">No</button>
                <button class="btn btn-primary" type="button" @click="downloadInvoice()">Sí, generar</button>
            </div>
        </div>
    </div>

</div>

<!-- Datos de la orden a editar (si existe) -->
@if(isset($editOrder))
<script>
    window.editOrderData = @json($editOrder);
</script>
@endif

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Toggle de datos de facturación
        var toggleFactura = document.getElementById('get-invoice');
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