const Loader = {
    instance: null,

    show: function(mensaje = "Cargando...") {
        if (this.instance) return; // Evita duplicados

        // Crear el elemento overlay
        this.instance = document.createElement("div");
        this.instance.className = "loader-overlay";
        
        // Estructura interna
        this.instance.innerHTML = `
            <div class="spinner"></div>
            <div class="loader-text">${mensaje}</div>
        `;

        document.body.appendChild(this.instance);
    },

    hide: function() {
        if (this.instance) {
            this.instance.remove();
            this.instance = null;
        }
    }
};



/**
 * Función swal personalizada
 * @param {string} titulo - Título de la alerta
 * @param {string} mensaje - Cuerpo del mensaje
 * @param {string} tipo - 'success', 'error', 'warning'
 */
window.swal = function(titulo, mensaje, tipo) {
    // 1. Crear el overlay
    const overlay = document.createElement("div");
    overlay.className = "swal-overlay";

    // 2. Definir color según el tipo
    let colorTit = "var(--primary-color)"; // Azul por defecto
    if(tipo === 'warning') colorTit = "#f8bb86"; // Naranja
    if(tipo === 'error')   colorTit = "#f27474"; // Rojo

    // 3. Estructura de la alerta
    overlay.innerHTML = `
        <div class="swal-modal">
            <span class="swal-title" style="color: ${colorTit}">${titulo}</span>
            <span class="swal-text">${mensaje}</span>
            <button class="swal-button" id="swal-close">Aceptar</button>
        </div>
    `;

    document.body.appendChild(overlay);

    // 4. Cerrar al dar clic en el botón
    const btn = overlay.querySelector("#swal-close");
    btn.focus(); // Permite cerrar presionando 'Enter'
    
    btn.onclick = function() {
        overlay.remove();
    };

    // 5. Cerrar si hacen clic fuera de la caja blanca
    overlay.onclick = function(e) {
        if(e.target === overlay) overlay.remove();
    };
};