const proveedoresTableBody = document.querySelector('#proveedoresTable tbody');
    const searchProveedor = document.getElementById('searchProveedor');
    const filterProveedorNombre = document.getElementById('filterProveedorNombre');
    const filterProveedorFecha = document.getElementById('filterProveedorFecha');

    function applyProveedoresFilters() {
        const term = (searchProveedor?.value || '').toLowerCase().trim();
        const rows = Array.from(proveedoresTableBody.querySelectorAll('tr:not(.no-data)'));

        rows.forEach((row) => {
            const rowText = row.textContent.toLowerCase();
            row.style.display = term === '' || rowText.includes(term) ? '' : 'none';
        });

        const nameOrder = filterProveedorNombre?.value || '';
        if (nameOrder === 'az' || nameOrder === 'za') {
            const sortedByName = rows.sort((a, b) => {
                const nameA = a.children[1].textContent.trim().toLowerCase();
                const nameB = b.children[1].textContent.trim().toLowerCase();
                return nameOrder === 'az' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
            });
            sortedByName.forEach((row) => proveedoresTableBody.appendChild(row));
        }

        const dateOrder = filterProveedorFecha?.value || '';
        if (dateOrder === 'nuevos' || dateOrder === 'antiguos') {
            const sortedByDate = rows.sort((a, b) => {
                const dateA = new Date(a.children[5].dataset.order || '1970-01-01').getTime();
                const dateB = new Date(b.children[5].dataset.order || '1970-01-01').getTime();
                return dateOrder === 'nuevos' ? dateB - dateA : dateA - dateB;
            });
            sortedByDate.forEach((row) => proveedoresTableBody.appendChild(row));
        }
    }

    function openProveedorModal() {
        document.getElementById('proveedorModal').classList.add('active');
    }

    function closeProveedorModal() {
        document.getElementById('proveedorModal').classList.remove('active');
        document.getElementById('proveedorForm').reset();
    }

    function saveProveedor() {
        const form = document.getElementById('proveedorForm');
        if(form.checkValidity()) {
            form.submit();
        } else {
            form.reportValidity();
        }
    }

    searchProveedor?.addEventListener('input', applyProveedoresFilters);
    filterProveedorNombre?.addEventListener('change', applyProveedoresFilters);
    filterProveedorFecha?.addEventListener('change', applyProveedoresFilters);
    
    document.getElementById('btnResetProveedorFiltros')?.addEventListener('click', function () {
        if (filterProveedorNombre) filterProveedorNombre.value = '';
        if (filterProveedorFecha)  filterProveedorFecha.value  = '';
        if (searchProveedor) searchProveedor.value = '';
        applyProveedoresFilters();
    });

    // ... (Tus funciones de filtro y saveProveedor se mantienen) ...

// ABRIR MODAL EDICIÓN Y LLENAR CAMPOS
    document.querySelectorAll('.btnEditarProveedor').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            document.getElementById('editarIdProveedor').value = d.id;
            document.getElementById('editarEmpresa').value = d.empresa;
            document.getElementById('editarContacto').value = d.contacto;
            document.getElementById('editarTelefono').value = d.tel;
            document.getElementById('editarEmail').value = d.email;
            
            document.getElementById('modalEditarProveedor').classList.add('active');
        });
    });

    function closeEditModal() {
        document.getElementById('modalEditarProveedor').classList.remove('active');
    }

    function updateProveedor() {
        const form = document.getElementById('formEditarProveedor');
        if(form.checkValidity()) {
            form.submit();
        } else {
            form.reportValidity();
        }
    }

    document.getElementById('btnNuevoProveedor')?.addEventListener('click', openProveedorModal);