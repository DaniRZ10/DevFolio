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
}