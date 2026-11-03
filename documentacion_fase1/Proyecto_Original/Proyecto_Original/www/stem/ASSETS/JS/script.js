function prueba(){
	 document.write('Prueba de JS');
}

function encriptar(){
	var plaintext = "Palabra"; 
    var encrypted = CryptoJS.AES.encrypt(plaintext, "Secret Passphrase"); 
    alert("El texto a encriptar es: " + plaintext + " y encriptado es: " + encrypted.toString()); 

}

function desencriptar(){
    var decrypted = CryptoJS.AES.decrypt(encrypted, "Secret Passphrase"); 
    var encrptedText2 = decrypted.toString(CryptoJS.enc.Utf8); 
    // var decrypted = CryptoJS.AES.decrypt(data, key).toString(CryptoJS.enc.Utf8); 
    alert("El texto encriptado es: " + encrypted.toString + " y desencriptado es: " + encrptedText2); 
}

/* ==========================================================================
   Apple-Style Page Transitions & Navigation Loader System
   Follows Emil Kowalski's guidelines and Apple WWDC design principles:
   - Hardware-accelerated CSS opacity and transform
   - Smooth dismiss on page load
   - Non-blocking failsafe
   - Strict zero-emoji policy
   ========================================================================== */

function ocultarPantallaCarga() {
    var loader = document.getElementById('apple-page-loader');
    var progress = document.getElementById('apple-top-progress');
    if (loader) {
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                loader.classList.add('apple-loader-hidden');
            });
        });
    }
    if (progress) {
        progress.classList.remove('active');
    }
}

// Ocultar loader cuando el contenido del DOM este listo
document.addEventListener('DOMContentLoaded', ocultarPantallaCarga);

// Manejar soporte bfcache (al volver atras o adelante en el navegador)
window.addEventListener('pageshow', ocultarPantallaCarga);

/**
 * Transición elegante estilo Apple al navegar entre módulos.
 * @param {string|HTMLFormElement} destino - ID del formulario a enviar o URL destino.
 * @param {string} [titulo] - Titulo a mostrar en la tarjeta de carga.
 * @param {string} [subtitulo] - Subtitulo descriptivo del modulo.
 */
function transicionNavegar(destino, titulo, subtitulo) {
    var loader = document.getElementById('apple-page-loader');
    var progress = document.getElementById('apple-top-progress');
    var titleEl = document.getElementById('appleLoaderTitle');
    var subEl = document.getElementById('appleLoaderSubtitle');

    if (titleEl && titulo) {
        titleEl.textContent = titulo;
    }
    if (subEl && subtitulo) {
        subEl.textContent = subtitulo;
    }
    if (progress) {
        progress.classList.add('active');
    }
    if (loader) {
        loader.classList.remove('apple-loader-hidden');
    }

    setTimeout(function() {
        var form = typeof destino === 'string' ? document.getElementById(destino) : destino;
        if (form && typeof form.submit === 'function') {
            form.submit();
        } else if (typeof destino === 'string') {
            window.location.href = destino;
        }
    }, 220);
}