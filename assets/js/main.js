/**
 * JavaScript Principal del Dashboard
 * Sistema de Dashboard PHP
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    initSidebar();
    initToggleSidebar();
    initDeleteConfirmation();
    initFormValidation();
    initSearch();
});

// Toggle del Sidebar
function initToggleSidebar() {
    const toggleBtn = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
}

// Sidebar activo
function initSidebar() {
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    
    menuLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
}

// Confirmación de eliminación
function initDeleteConfirmation() {
    const deleteBtns = document.querySelectorAll('.btn-delete');
    
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message') || '¿Estás seguro de que deseas eliminar este elemento?';
            
            if (confirm(message)) {
                window.location.href = this.getAttribute('href');
            }
        });
    });
}

// Validación de formularios
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showError(field, 'Este campo es requerido');
                } else {
                    hideError(field);
                }
            });
            
            // Validar email
            const emailFields = form.querySelectorAll('[type="email"]');
            emailFields.forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    showError(field, 'Por favor ingresa un email válido');
                }
            });
            
            // Validar contraseñas
            const passwordFields = form.querySelectorAll('[data-password-match]');
            passwordFields.forEach(field => {
                const matchField = document.querySelector(field.getAttribute('data-password-match'));
                if (field.value !== matchField.value) {
                    isValid = false;
                    showError(field, 'Las contraseñas no coinciden');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

// Función de búsqueda
function initSearch() {
    const searchInput = document.querySelector('#search-input');
    const searchForm = document.querySelector('#search-form');
    
    if (searchInput && searchForm) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            if (searchTerm.length > 2 || searchTerm.length === 0) {
                searchTimeout = setTimeout(function() {
                    searchForm.submit();
                }, 500);
            }
        });
    }
}

// Mostrar error en campo
function showError(field, message) {
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('error-message')) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    } else {
        const newErrorDiv = document.createElement('div');
        newErrorDiv.className = 'error-message';
        newErrorDiv.style.color = '#e74a3b';
        newErrorDiv.style.fontSize = '0.875rem';
        newErrorDiv.style.marginTop = '5px';
        newErrorDiv.textContent = message;
        field.parentNode.insertBefore(newErrorDiv, field.nextSibling);
    }
    
    field.style.borderColor = '#e74a3b';
}

// Ocultar error en campo
function hideError(field) {
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('error-message')) {
        errorDiv.style.display = 'none';
    }
    field.style.borderColor = '#d1d3e2';
}

// Validar email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Mostrar notificación
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} fade-in`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Función AJAX para cargas de datos
function loadData(url, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                callback(response);
            } catch (e) {
                console.error('Error parsing JSON:', e);
            }
        }
    };
    
    xhr.onerror = function() {
        console.error('Error loading data');
    };
    
    xhr.send();
}

// Formatear fecha
function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('es-ES', options);
}

// Formatear número
function formatNumber(number) {
    return new Intl.NumberFormat('es-MX').format(number);
}

// Copiar al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification('Copiado al portapapeles', 'success');
    }).catch(function(err) {
        console.error('Error al copiar:', err);
        showNotification('Error al copiar', 'danger');
    });
}

// Exportar funciones globales
window.showNotification = showNotification;
window.loadData = loadData;
window.formatDate = formatDate;
window.formatNumber = formatNumber;
window.copyToClipboard = copyToClipboard;

// Esto hará que CUALQUIER $.ajax del proyecto muestre el loader automáticamente
$(document).ajaxStart(function() {
    Loader.show();
});

$(document).ajaxStop(function() {
    Loader.hide();
});