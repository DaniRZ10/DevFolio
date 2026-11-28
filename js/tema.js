/**
 * Clase Tema: activa o desactiva la hoja alternativa css/oscuro.css
 * y guarda la preferencia (no sensible) en la cookie "tema".
 * PHP lee la misma cookie en includes/cabecera.php para cargar ya el tema correcto.
 *
 * Patrón Singleton: solo existe un gestor del tema en toda la página,
 * así todas las partes del código consultan y cambian el mismo estado.
 */

"use strict";

class Tema {
    // Única instancia de la clase
    static instancia;

    constructor() {
        this.nombreCookie = "tema";
        this.diasCaducidad = 365;
        this.enlace = document.getElementById("tema-oscuro"); // <link> de oscuro.css
        this.boton = document.getElementById("btn-tema");
    }

    /**
     * Devuelve la instancia única, la crea la primera vez.
     * @returns {Tema}
     */
    static getInstance() {
        if (!Tema.instancia) {
            Tema.instancia = new Tema();
        }
        return Tema.instancia;
    }

    /** Aplica el tema guardado y escucha el clic del botón. */
    iniciar() {
        // Si la página no tiene la plantilla común, no hay nada que hacer
        if (!this.enlace || !this.boton) {
            return;
        }

        this.aplicar(this.leerCookie() === "oscuro");
        this.boton.addEventListener("click", () => this.alternar());
    }

    /** Cambia al tema contrario y lo guarda. */
    alternar() {
        const oscuro = this.boton.getAttribute("aria-pressed") !== "true";
        this.aplicar(oscuro);
        this.guardarCookie(oscuro ? "oscuro" : "claro");
    }

    /**
     * Activa o desactiva la hoja oscura y actualiza el botón.
     * @param {boolean} oscuro - true para el tema oscuro
     */
    aplicar(oscuro) {
        this.enlace.disabled = !oscuro;
        this.boton.setAttribute("aria-pressed", String(oscuro));
        this.boton.textContent = oscuro ? "Modo claro" : "Modo oscuro";
    }

    /**
     * Devuelve el valor de la cookie del tema o null si no existe.
     * @returns {string|null}
     */
    leerCookie() {
        const pares = document.cookie.split("; ");

        for (const par of pares) {
            const [clave, valor] = par.split("=");
            if (clave === this.nombreCookie) {
                return decodeURIComponent(valor);
            }
        }
        return null;
    }

    /**
     * Guarda el tema elegido durante un año.
     * @param {string} valor - "oscuro" o "claro"
     */
    guardarCookie(valor) {
        const caducidad = new Date();
        caducidad.setDate(caducidad.getDate() + this.diasCaducidad);
        document.cookie = `${this.nombreCookie}=${encodeURIComponent(valor)}; expires=${caducidad.toUTCString()}; path=/; SameSite=Lax`;
    }
}

// El script se carga con defer, así que cuando se ejecuta, el HTML ya está analizado
Tema.getInstance().iniciar();