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
}