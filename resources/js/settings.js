function settingsModuleActive() {
	return !!document.getElementById('settings-root');
}

if (typeof window !== 'undefined' && settingsModuleActive()) {

    document.addEventListener('DOMContentLoaded', function () {

        const tabs = document.querySelectorAll('#settingsTabs .nav-link');

        /*
        * Función para cargar contenido de un tab
        * @param {string} tabId - ID del tab (categories, services, vehicle-types, clients)
        * @param {string} url - URL para obtener los datos
        * @returns {void}
        */
        function loadTabContent(tabId, url) {

            const contentDiv = document.getElementById(tabId + '-content');

            fetch(url).then(response => response.json()).then(data => {

                let html = '<button class="btn btn-success mb-3" onclick="showCreateForm(\'' + tabId + '\')">Crear Nuevo</button>';
                function settingsModuleActive() {
                    return !!document.getElementById('settings-root');
                }

                // Reestructuramos el módulo para exponer un objeto `window.settings` con métodos async
                // y mantener compatibilidad con handlers inline (exponiendo también funciones globales).
                if (typeof window !== 'undefined' && settingsModuleActive()) {

                    console.log('Settings JS cargado');

                    // Cargar datos para un tab (async)
                    async function loadTabContent(tabId, url) {
                        const contentDiv = document.getElementById(tabId + '-content');

                        try {
                            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                            const data = await response.json();

                            let html = '<button class="btn btn-success mb-3" onclick="window.showCreateForm(\'' + tabId + '\')">Crear Nuevo</button>';

                            if (!data || data.length === 0) {

                                let message = '';

                                switch (tabId) {
                                    case 'categories':
                                        message = 'No hay categorías registradas';
                                        break;
                                    case 'services':
                                        message = 'No hay servicios registrados';
                                        break;
                                    case 'vehicle-types':
                                        message = 'No hay tipos de vehículo registrados';
                                        break;
                                    case 'clients':
                                        message = 'No hay clientes registrados';
                                        break;
                                }

                                html += '<div class="text-center py-5 p-4">';
                                html += '<i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>';
                                html += '<p class="text-muted">' + message + '</p>';
                                html += '</div>';

                            } else {

                                html += '<table class="table table-striped">';
                                html += '<thead><tr>';

                                Object.keys(data[0]).forEach(key => {
                                    if (key !== 'id' && key !== 'created_at' && key !== 'updated_at') {
                                        html += '<th>' + key.charAt(0).toUpperCase() + key.slice(1) + '</th>';
                                    }
                                });

                                html += '<th>Acciones</th>';
                                html += '</tr></thead><tbody>';

                                data.forEach(item => {
                                    html += '<tr>';

                                    Object.keys(item).forEach(key => {
                                        if (key !== 'id' && key !== 'created_at' && key !== 'updated_at') {
                                            html += '<td>' + item[key] + '</td>';
                                        }
                                    });

                                    html += '<td>';
                                    html += '<button class="btn btn-sm btn-warning" onclick="window.editItem(\'' + tabId + '\', ' + item.id + ')">Editar</button> ';
                                    html += '<button class="btn btn-sm btn-danger" onclick="window.deleteItem(\'' + tabId + '\', ' + item.id + ')">Eliminar</button>';
                                    html += '</td></tr>';
                                });

                                html += '</tbody></table>';
                            }

                            contentDiv.innerHTML = html;

                        } catch (error) {
                            contentDiv.innerHTML = '<p>Error al cargar los datos.</p>';
                            console.error('Error:', error);
                        }
                    }

                    // Funciones CRUD (async)
                    async function showCreateForm(tabId) {
                        if (tabId !== 'categories') {
                            alert('Crear nuevo no implementado para: ' + tabId);
                            return;
                        }

                        const form = document.getElementById('categoryForm');
                        if (form) form.reset();
                        const idEl = document.getElementById('category_id'); if (idEl) idEl.value = '';
                        const nameErr = document.getElementById('category_name_error'); if (nameErr) nameErr.textContent = '';
                        const statusErr = document.getElementById('category_status_error'); if (statusErr) statusErr.textContent = '';
                        const genErr = document.getElementById('category_general_error'); if (genErr) genErr.classList.add('d-none');
                        const label = document.getElementById('categoryModalLabel'); if (label) label.textContent = 'Crear categoría';

                        const modalEl = document.getElementById('categoryModal');
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }

                    async function editItem(tabId, id) {
                        if (tabId !== 'categories') {
                            alert('Editar no implementado para: ' + tabId);
                            return;
                        }

                        try {
                            const response = await fetch('/categories/' + id, { headers: { 'Accept': 'application/json' } });
                            if (!response.ok) throw new Error('Error al obtener categoría');
                            const data = await response.json();

                            const form = document.getElementById('categoryForm');
                            if (form) form.reset();
                            const idEl = document.getElementById('category_id'); if (idEl) idEl.value = data.id || '';
                            const nameEl = document.getElementById('category_name'); if (nameEl) nameEl.value = data.name || '';
                            const statusEl = document.getElementById('category_status'); if (statusEl) statusEl.value = data.status ?? 1;

                            const nameErr = document.getElementById('category_name_error'); if (nameErr) nameErr.textContent = '';
                            const statusErr = document.getElementById('category_status_error'); if (statusErr) statusErr.textContent = '';
                            const genErr = document.getElementById('category_general_error'); if (genErr) genErr.classList.add('d-none');
                            const label = document.getElementById('categoryModalLabel'); if (label) label.textContent = 'Editar categoría';

                            const modalEl = document.getElementById('categoryModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                        } catch (err) {
                            console.error(err);
                            alert('No se pudo cargar la categoría.');
                        }
                    }

                    async function deleteItem(tabId, id) {
                        if (!confirm('¿Estás seguro de eliminar este item?')) return;

                        let url;
                        switch (tabId) {
                            case 'categories': url = '/categories/' + id; break;
                            case 'services': url = '/services/' + id; break;
                            case 'vehicle-types': url = '/vehicle-types/' + id; break;
                            case 'clients': url = '/clients/' + id; break;
                        }

                        try {
                            const response = await fetch(url, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                            });

                            const data = await response.json();
                            alert(data.message || 'Eliminado');

                            const activeTab = document.querySelector('#settingsTabs .nav-link.active');
                            if (activeTab) activeTab.click();

                        } catch (error) {
                            alert('Error al eliminar');
                            console.error('Error:', error);
                        }
                    }

                    // Manejo del formulario de categoría (submit)
                    async function setupCategoryForm() {
                        const catForm = document.getElementById('categoryForm');
                        if (!catForm) return;

                        catForm.addEventListener('submit', async function (e) {
                            e.preventDefault();
                            const submitBtn = document.getElementById('categorySubmitBtn');
                            if (submitBtn) submitBtn.disabled = true;

                            const id = document.getElementById('category_id').value;

                            const payload = {
                                name: document.getElementById('category_name').value.trim(),
                                status: document.getElementById('category_status').value
                            };

                            const url = id ? '/categories/' + id : '/categories';
                            const method = id ? 'PUT' : 'POST';

                            try {
                                const res = await fetch(url, {
                                    method: method,
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify(payload)
                                });

                                if (submitBtn) submitBtn.disabled = false;

                                if (res.status === 422) {
                                    const json = await res.json();
                                    const nameErr = document.getElementById('category_name_error'); if (nameErr) nameErr.textContent = '';
                                    const statusErr = document.getElementById('category_status_error'); if (statusErr) statusErr.textContent = '';
                                    if (json.errors) {
                                        if (json.errors.name) nameErr.textContent = json.errors.name.join(', ');
                                        if (json.errors.status) statusErr.textContent = json.errors.status.join(', ');
                                    }
                                    return;
                                }

                                if (!res.ok) throw new Error('Error en servidor');

                                const data = await res.json();

                                const modalEl = document.getElementById('categoryModal');
                                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                modal.hide();

                                const categoriesTab = document.querySelector('#settingsTabs button[data-bs-target="#categories"]');
                                if (categoriesTab) categoriesTab.click();

                            } catch (err) {
                                if (submitBtn) submitBtn.disabled = false;
                                console.error(err);
                                const gen = document.getElementById('category_general_error');
                                if (gen) {
                                    gen.textContent = 'Error de servidor, inténtalo nuevamente.';
                                    gen.classList.remove('d-none');
                                }
                            }

                        });
                    }

                    // Inicialización: asigna listeners y carga el tab inicial
                    async function init() {
                        const tabs = document.querySelectorAll('#settingsTabs .nav-link');

                        // cargar inicial
                        await loadTabContent('categories', '/categories');

                        // listeners de tabs
                        tabs.forEach(tab => {
                            tab.addEventListener('shown.bs.tab', async function (event) {
                                const target = event.target.getAttribute('data-bs-target').substring(1);
                                let url;
                                switch (target) {
                                    case 'categories': url = '/categories'; break;
                                    case 'services': url = '/api/services'; break;
                                    case 'vehicle-types': url = '/vehicle-types'; break;
                                    case 'clients': url = '/api/clients'; break;
                                }
                                await loadTabContent(target, url);
                            });
                        });

                        // configurar formulario
                        await setupCategoryForm();
                    }

                    // Ejecutar init una vez cargado el DOM
                    document.addEventListener('DOMContentLoaded', function () { init().catch(console.error); });

                    // Exponer API bajo window.settings y mantener compatibilidad global
                    if (typeof window !== 'undefined') {
                        window.settings = {
                            init,
                            loadTabContent,
                            showCreateForm,
                            editItem,
                            deleteItem
                        };

                        // Compatibilidad con handlers inline previos
                        window.showCreateForm = async function (tabId) { return window.settings.showCreateForm(tabId); };
                        window.editItem = async function (tabId, id) { return window.settings.editItem(tabId, id); };
                        window.deleteItem = async function (tabId, id) { return window.settings.deleteItem(tabId, id); };
                    }

                }

                // recargar tab categories
                const categoriesTab = document.querySelector('#settingsTabs button[data-bs-target="#categories"]');
                if (categoriesTab) categoriesTab.click();

            }).catch(err => {

                submitBtn.disabled = false;
                console.error(err);

                const gen = document.getElementById('category_general_error');
                gen.textContent = 'Error de servidor, inténtalo nuevamente.';
                gen.classList.remove('d-none');

            });

        }


        // Exponer funciones globales para que los handlers inline (onclick="...")
        // funcionen cuando el archivo se sirve como módulo (ej. Vite).
        if (typeof window !== 'undefined') {
            window.showCreateForm = showCreateForm;
            window.editItem = editItem;
            window.deleteItem = deleteItem;
        }

    });
    
}