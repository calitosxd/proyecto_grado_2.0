/**
 * script.js - Utilidades JavaScript, Notificaciones y Navegacion
 * Semillero STEM - COTECNOVA
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 */

// ==========================================================================
// 1. SISTEMA DE NOTIFICACIONES Y ALERTAS AGRONÓMICAS (FAO 56 / TinyML)
// ==========================================================================
const CotecnovaNotify = {
    unreadCount: 3,

    toggleDropdown(forzar) {
        const dropdown = document.getElementById('notificationDropdown');
        const wrapper = dropdown ? dropdown.parentElement : null;
        if (!dropdown || !wrapper) return;

        const abrir = (forzar !== undefined) ? forzar : !dropdown.classList.contains('show-notif-dropdown');
        if (abrir) {
            dropdown.classList.add('show-notif-dropdown');

            // Detectar posición relativa al viewport para que NUNCA se corte ni a izquierda ni a derecha
            const wrapperRect = wrapper.getBoundingClientRect();
            const dropdownWidth = 360;

            if (wrapperRect.right < dropdownWidth + 24) {
                // El botón está en la mitad izquierda o la pantalla es angosta: abrir hacia la derecha
                dropdown.style.left = '0';
                dropdown.style.right = 'auto';
            } else {
                // El botón está en la mitad derecha con suficiente espacio: abrir hacia la izquierda
                dropdown.style.left = 'auto';
                dropdown.style.right = '0';
            }
        } else {
            dropdown.classList.remove('show-notif-dropdown');
        }
    },

    markAllAsRead() {
        const unreadItems = document.querySelectorAll('.notif-item.notif-unread');
        unreadItems.forEach(item => item.classList.remove('notif-unread'));
        this.unreadCount = 0;
        this.updateBadge();
        this.showToast('Notificaciones leídas', 'Todas las alertas fueron marcadas como vistas', 'info', 2000);
    },

    updateBadge() {
        const badge = document.getElementById('notifBadge');
        if (!badge) return;
        if (this.unreadCount > 0) {
            badge.textContent = this.unreadCount;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    },

    /**
     * Muestra una notificación flotante (Toast)
     * @param {string} title - Título de la notificación
     * @param {string} message - Descripción o detalle
     * @param {'info'|'success'|'warning'|'danger'} type - Tipo de notificación
     * @param {number} duration - Duración en milisegundos (defecto: 4500)
     */
    showToast(title, message, type = 'info', duration = 4500) {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        let iconSvg = '';
        if (type === 'danger') {
            iconSvg = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
        } else if (type === 'warning') {
            iconSvg = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
        } else if (type === 'success') {
            iconSvg = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;
        } else {
            iconSvg = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
        }

        toast.innerHTML = `
            <div class="toast-icon">${iconSvg}</div>
            <div class="toast-body">
                <strong class="toast-title">${title}</strong>
                <p class="toast-message">${message}</p>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Cerrar">&times;</button>
        `;

        container.appendChild(toast);

        // Auto eliminación
        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => {
                if (toast.parentElement) toast.remove();
            }, 300);
        }, duration);
    }
};

// ==========================================================================
// 2. CONTROL DEL MENÚ HAMBURGUESA LATERAL (Off-Canvas Drawer)
// ==========================================================================
function toggleMenuDrawer(forzar) {
    const drawer = document.getElementById('menuDrawer');
    const backdrop = document.getElementById('menuBackdrop');
    if (!drawer || !backdrop) return;
    
    const abrir = (forzar !== undefined) ? forzar : !drawer.classList.contains('drawer-open');
    if (abrir) {
        drawer.classList.add('drawer-open');
        backdrop.classList.add('backdrop-active');
        document.body.classList.add('menu-open-locked');
    } else {
        drawer.classList.remove('drawer-open');
        backdrop.classList.remove('backdrop-active');
        document.body.classList.remove('menu-open-locked');
    }
}

// Cierre automático con Escape y clic fuera del dropdown de notificaciones
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        toggleMenuDrawer(false);
        CotecnovaNotify.toggleDropdown(false);
    }
});

document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('notificationDropdown');
    const btnNotif = document.getElementById('btnNotificationToggle');
    if (dropdown && btnNotif && !dropdown.contains(e.target) && !btnNotif.contains(e.target)) {
        dropdown.classList.remove('show-notif-dropdown');
    }
});

// Limpieza de estado de sonido en localStorage
try {
    localStorage.removeItem('cotecnova_sound_enabled');
} catch(e) {}

// Inicializar al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    CotecnovaNotify.updateBadge();
});
