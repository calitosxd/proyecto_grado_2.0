/**
 * script.js - Utilidades JavaScript, Gestor de Sonido y Sistema de Notificaciones
 * Semillero STEM - COTECNOVA
 * Proyecto de Grado: Monitoreo Microambiental y Riego Inteligente
 */

// ==========================================================================
// 1. GESTOR DE AUDIO E INTERACCIÓN AUDITIVA (Audio UX)
// ==========================================================================
const CotecnovaAudio = {
    beepAudio: null,
    alarmAudio: null,
    soundEnabled: true,

    init() {
        // Cargar preferencia guardada en localStorage (por defecto activo)
        const stored = localStorage.getItem('cotecnova_sound_enabled');
        this.soundEnabled = (stored === null) ? true : (stored === 'true');

        // Inicializar elementos de audio
        try {
            this.beepAudio = new Audio('ASSETS/SOUND/beep.mp3');
            this.beepAudio.volume = 0.35; // Volumen moderado para no aturdir

            this.alarmAudio = new Audio('ASSETS/SOUND/alarm.mp3');
            this.alarmAudio.volume = 0.5;
        } catch (e) {
            console.warn('Audio no soportado o bloqueado por el navegador:', e);
        }

        this.updateToggleButtonUI();
        this.bindClickSounds();
    },

    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        localStorage.setItem('cotecnova_sound_enabled', this.soundEnabled);
        this.updateToggleButtonUI();

        if (this.soundEnabled) {
            this.playBeep();
            if (window.CotecnovaNotify) {
                CotecnovaNotify.showToast('Sonido activado', 'Efectos y alertas audibles habilitados', 'success', 2500);
            }
        } else {
            if (window.CotecnovaNotify) {
                CotecnovaNotify.showToast('Sonido silenciado', 'Efectos audibles desactivados', 'info', 2500);
            }
        }
    },

    updateToggleButtonUI() {
        const btn = document.getElementById('btnSoundToggle');
        if (!btn) return;

        if (this.soundEnabled) {
            btn.classList.add('sound-active');
            btn.classList.remove('sound-muted');
            btn.setAttribute('title', 'Efectos de sonido: ACTIVADOS (Clic para silenciar)');
            btn.innerHTML = `
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                </svg>
                <span class="btn-action-label">Audio ON</span>
            `;
        } else {
            btn.classList.remove('sound-active');
            btn.classList.add('sound-muted');
            btn.setAttribute('title', 'Efectos de sonido: SILENCIADOS (Clic para activar)');
            btn.innerHTML = `
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <line x1="23" y1="9" x2="17" y2="15"></line>
                    <line x1="17" y1="9" x2="23" y2="15"></line>
                </svg>
                <span class="btn-action-label">Mute</span>
            `;
        }
    },

    playBeep() {
        if (!this.soundEnabled || !this.beepAudio) return;
        try {
            this.beepAudio.currentTime = 0;
            const playPromise = this.beepAudio.play();
            if (playPromise !== undefined) {
                playPromise.catch(e => { /* Bloqueo de autoplay esperado */ });
            }
        } catch (err) {
            console.error('Error al reproducir beep:', err);
        }
    },

    playAlarm() {
        if (!this.soundEnabled || !this.alarmAudio) return;
        try {
            this.alarmAudio.currentTime = 0;
            const playPromise = this.alarmAudio.play();
            if (playPromise !== undefined) {
                playPromise.catch(e => { /* Bloqueo de autoplay esperado */ });
            }
        } catch (err) {
            console.error('Error al reproducir alarma:', err);
        }
    },

    bindClickSounds() {
        document.addEventListener('click', (e) => {
            const target = e.target.closest('button, .btn, .nav-btn, .tab-btn, a.btn');
            if (target && !target.dataset.noSound && target.id !== 'btnSoundToggle') {
                this.playBeep();
            }
        });
    }
};

// ==========================================================================
// 2. SISTEMA DE NOTIFICACIONES Y ALERTAS AGRONÓMICAS (FAO 56 / TinyML)
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
            CotecnovaAudio.playBeep();

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
        CotecnovaAudio.playBeep();
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
            CotecnovaAudio.playAlarm();
        } else if (type === 'warning') {
            iconSvg = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
            CotecnovaAudio.playAlarm();
        } else if (type === 'success') {
            iconSvg = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;
            CotecnovaAudio.playBeep();
        } else {
            iconSvg = `<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
            CotecnovaAudio.playBeep();
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
// 3. CONTROL DEL MENÚ HAMBURGUESA LATERAL (Off-Canvas Drawer)
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
        CotecnovaAudio.playBeep();
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

// Inicializar al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    CotecnovaAudio.init();
    CotecnovaNotify.updateBadge();
});
