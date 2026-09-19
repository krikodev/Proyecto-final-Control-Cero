document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('user-search');
    const tableBody = document.querySelector('table tbody');
    const paginationInfo = document.querySelector('.panel-description');
    const paginationNav = document.querySelector('.pagination');
    let debounceTimer;

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchUsers(searchInput.value.trim());
        }, 350);
    });

    function fetchUsers(query) {
        const url = '/usuarios/buscar?q=' + encodeURIComponent(query);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (res) {
                if (!res.ok) throw new Error('Error en la busqueda');
                return res.json();
            })
            .then(function (data) {
                renderTable(data.data);
                renderPaginationInfo(data.total);
                renderPagination(data);
            })
            .catch(function () {
                tableBody.innerHTML =
                    '<tr><td colspan="6" class="muted" style="padding:24px;text-align:center;">' +
                    'Ocurrio un error al buscar.</td></tr>';
            });
    }

    function renderTable(users) {
        if (users.length === 0) {
            tableBody.innerHTML =
                '<tr><td colspan="6" class="muted" style="padding:24px;text-align:center;">' +
                'No se encontraron usuarios.</td></tr>';
            return;
        }

        let html = '';
        const currentUserId = parseInt(
            document.body.dataset.authId || '0',
            10
        );

        users.forEach(function (user) {
            const isMe = user.id === currentUserId;
            const hasActions = user.can_edit || user.can_activate;

            html += '<tr>';
            html += '<td>' + escapeHtml(user.name) + ' ' + escapeHtml(user.last_name);
            if (isMe) {
                html += ' <span class="badge">Tu</span>';
            }
            html += '</td>';

            html += '<td>' + escapeHtml(user.dni || 'Sin registrar') + '</td>';
            html += '<td>' + escapeHtml(user.email) + '</td>';
            html += '<td>' + escapeHtml(user.role ? user.role.name : 'Sin asignar') + '</td>';

            html += '<td><span class="badge ' + (user.is_active ? 'active' : '') + '">';
            html += user.is_active ? 'Activo' : 'Inactivo';
            html += '</span></td>';

            if (hasActions) {
                html += '<td><div class="row-actions">';
                if (user.can_edit) {
                    html += '<a href="/usuarios/' + user.id + '/editar" class="pagination-link">Editar</a>';
                }
                if (user.can_activate && !isMe) {
                    const action = user.is_active ? 'Desactivar' : 'Activar';
                    const dangerClass = user.is_active ? ' action-danger' : '';
                    html += '<form method="POST" action="/usuarios/' + user.id + '/estado">';
                    html += '<input type="hidden" name="_token" value="' + getCsrfToken() + '">';
                    html += '<input type="hidden" name="_method" value="PATCH">';
                    html += '<input type="hidden" name="is_active" value="' + (user.is_active ? '0' : '1') + '">';
                    html += '<button type="submit" class="pagination-link' + dangerClass + '">' + action + '</button>';
                    html += '</form>';
                }
                html += '</div></td>';
            }

            html += '</tr>';
        });

        tableBody.innerHTML = html;
    }

    function renderPaginationInfo(total) {
        if (paginationInfo) {
            const label = total === 1 ? 'usuario registrado' : 'usuarios registrados';
            paginationInfo.textContent = total + ' ' + label + '.';
        }
    }

    function renderPagination(data) {
        if (!paginationNav) return;

        if (data.last_page <= 1) {
            paginationNav.style.display = 'none';
            return;
        }

        paginationNav.style.display = 'flex';

        let html = '';

        if (data.current_page === 1) {
            html += '<span class="pagination-link disabled" aria-disabled="true">Anterior</span>';
        } else {
            html += '<a class="pagination-link" href="#" data-page="' + (data.current_page - 1) + '">Anterior</a>';
        }

        html += '<span class="muted">Pagina ' + data.current_page + ' de ' + data.last_page + '</span>';

        if (data.current_page < data.last_page) {
            html += '<a class="pagination-link" href="#" data-page="' + (data.current_page + 1) + '">Siguiente</a>';
        } else {
            html += '<span class="pagination-link disabled" aria-disabled="true">Siguiente</span>';
        }

        paginationNav.innerHTML = html;

        paginationNav.querySelectorAll('a[data-page]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const page = this.dataset.page;
                const query = searchInput.value.trim();
                fetchUsersPaginated(query, page);
            });
        });
    }

    function fetchUsersPaginated(query, page) {
        const url = '/usuarios/buscar?q=' + encodeURIComponent(query) + '&page=' + encodeURIComponent(page);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                renderTable(data.data);
                renderPaginationInfo(data.total);
                renderPagination(data);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
