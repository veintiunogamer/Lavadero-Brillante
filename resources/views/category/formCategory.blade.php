<!-- Modal para crear/editar categoría -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <form id="categoryForm" class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Crear categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body">

                <input type="hidden" id="category_id" name="id" value="">
                <div class="mb-3">
                    <label for="category_name" class="form-label">Nombre</label>
                    <input type="text" id="category_name" name="name" class="form-control" required>
                    <div class="invalid-feedback" id="category_name_error"></div>
                </div>

                <div class="mb-3">
                    <label for="category_status" class="form-label">Estado</label>
                    <select id="category_status" name="status" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <div class="invalid-feedback" id="category_status_error"></div>
                </div>
                <div class="alert alert-danger d-none" id="category_general_error"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="categorySubmitBtn">Guardar</button>
            </div>

        </form>

    </div>

</div>
