/**
 * script.js - Utilidades JavaScript Generales del Sistema de Monitoreo
 * Semillero STEM - COTECNOVA
 */

/**
 * Control del Menú Hamburguesa Lateral (Off-Canvas Drawer)
 * @param {boolean} [forzar] - true para abrir, false para cerrar, omitir para alternar
 */
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

// Cierre automático al pulsar la tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        toggleMenuDrawer(false);
    }
});
