/**
 * validacion.js - DevFolio
 * Objeto Validador: validación de formularios en el cliente
 * mediante expresiones regulares y control del evento submit.
 * La validación del servidor (PHP) se mantiene siempre.
 */

"use strict";

class Validador {
    constructor() {
        // Expresiones regulares por tipo de campo (nombre, email, contraseña...)
        this.patrones = {
            nombre: /^[\p{L}' -]{2,100}$/u,
            email: /^(?=.{1,150}$)[^\s@]+@[^\s@]+\.[^\s@]+$/,
            password: /^(?=.*\p{L})(?=.*\d).{8,72}$/u,
            url: /^(?=.{1,255}$)https?:\/\/[^\s\/]+\.[^\s\/]+\S*$/i
        };

        // Mensajes de error asociados a cada tipo de campo
        this.mensajes = {
            nombre: "El nombre debe tener entre 2 y 100 letras (se admiten espacios, guiones y apóstrofos).",
            email: "Introduce un email válido, por ejemplo nombre@dominio.com (máximo 150 caracteres).",
            password: "La contraseña debe tener entre 8 y 72 caracteres, con al menos una letra y un número.",
            url: "Introduce una URL válida que empiece por http:// o https:// (máximo 255 caracteres)."
        };
    }

    /**
    * Comprueba un valor contra el patrón de su tipo de campo.
    * @param {string} tipo - Clave de this.patrones (nombre, email, password, url)
    * @param {string} valor - Texto a comprobar
    * @param {boolean} opcional - Si es true, un valor vacío se considera válido
    * @returns {boolean} true si el valor es válido
    */
    validar(tipo, valor, opcional = false) {
        // Conversión a texto; solo se recortan espacios si no es una contraseña
        const texto = tipo === "password" ? String(valor) : String(valor).trim();

        // Campo opcional (ej. url_repo) dejado en blanco: se acepta
        if (opcional && texto === "") {
            return true;
        }

        // Tipo de campo que no existe en this.patrones
        if (!(tipo in this.patrones)) {
            console.error("Validador: tipo de campo desconocido -> " + tipo);
            return false;
        }

        return this.patrones[tipo].test(texto);
    }

    /**
    * Devuelve el mensaje de error asociado a un tipo de campo.
    * @param {string} tipo - Clave de this.mensajes
    * @returns {string} Mensaje de error (genérico si el tipo no existe)
    */
    obtenerMensaje(tipo) {
        return this.mensajes[tipo] || "El valor introducido no es válido.";
    }

    /**
    * Valida una lista de campos y devuelve los errores encontrados.
    * @param {Array<{nombre: string, tipo: string, valor: string, opcional?: boolean}>} campos
    * @returns {Array<{nombre: string, mensaje: string}>} Lista de errores (vacía si todo es válido)
    */
    validarCampos(campos) {
        const errores = [];

        // Se recorre cada campo y se guarda el error de los que no pasan la validación
        for (const campo of campos) {
            if (!this.validar(campo.tipo, campo.valor, campo.opcional)) {
                errores.push({
                    nombre: campo.nombre,
                    mensaje: this.obtenerMensaje(campo.tipo)
                });
            }
        }

        return errores;
    }

    /**
    * Lee los valores de un formulario y los valida según unas reglas.
    * @param {HTMLFormElement} formulario - Formulario cuyos campos se leen
    * @param {Array<{nombre: string, tipo: string, opcional?: boolean}>} reglas
    *        nombre = atributo name del input; tipo = clave de this.patrones
    * @returns {Array<{nombre: string, mensaje: string}>} Lista de errores (vacía si todo es válido)
    */
    validarFormulario(formulario, reglas) {
        const campos = [];

        for (const regla of reglas) {
            // formulario.elements permite acceder a cada control por su atributo name
            const elemento = formulario.elements[regla.nombre];

            if (!elemento) {
                console.warn("Validador: no existe el campo '" + regla.nombre + "' en el formulario");
            }

            campos.push({
                nombre: regla.nombre,
                tipo: regla.tipo,
                valor: elemento ? elemento.value : "",
                opcional: regla.opcional
            });
        }

        return this.validarCampos(campos);
    }
    /**
    * Muestra cada mensaje de error justo después de su campo.
    * @param {HTMLFormElement} formulario - Formulario donde se muestran los errores
    * @param {Array<{nombre: string, mensaje: string}>} errores - Resultado de validarFormulario()
    */
    mostrarErrores(formulario, errores) {
        for (const error of errores) {
            const campo = formulario.elements[error.nombre];

            // Si el campo no existe en el formulario, no hay dónde mostrar el mensaje
            if (!campo) {
                continue;
            }

            // Marca el campo como inválido (útil para estilos y lectores de pantalla)
            campo.setAttribute("aria-invalid", "true");

            // textContent inserta el mensaje como texto, nunca como HTML
            const aviso = document.createElement("span");
            aviso.className = "error-validacion";
            aviso.textContent = error.mensaje;
            campo.insertAdjacentElement("afterend", aviso);
        }
    }

    /**
     * Elimina los mensajes de error y las marcas de campo inválido de un formulario.
     * @param {HTMLFormElement} formulario - Formulario que se limpia
     */
    limpiarErrores(formulario) {
        for (const aviso of formulario.querySelectorAll(".error-validacion")) {
            aviso.remove();
        }

        for (const campo of formulario.querySelectorAll("[aria-invalid]")) {
            campo.removeAttribute("aria-invalid");
        }
    }
    
    /**
    * Engancha la validación al evento submit de un formulario.
    * Si hay errores, los muestra y bloquea el envío.
    * @param {HTMLFormElement} formulario - Formulario que se controla
    * @param {Array<{nombre: string, tipo: string, opcional?: boolean}>} reglas
    */
    controlarSubmit(formulario, reglas) {
        // Función flecha: conserva el this de Validador dentro del evento
        formulario.addEventListener("submit", (evento) => {
            // Se parte siempre de un formulario sin errores de intentos anteriores
            this.limpiarErrores(formulario);

            const errores = this.validarFormulario(formulario, reglas);

            if (errores.length > 0) {
                // Se cancela el envío al servidor
                evento.preventDefault();
                this.mostrarErrores(formulario, errores);

                // Se lleva el foco al primer campo con error
                const primero = formulario.elements[errores[0].nombre];
                if (primero) {
                    primero.focus();
                }
            }
            // Si no hay errores, el formulario se envía con normalidad (GET/POST)
        });
    }

}