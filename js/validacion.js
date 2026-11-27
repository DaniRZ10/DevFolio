/**
 * validacion.js - DevFolio
 * Objeto Validador: validación de formularios en el cliente
 * mediante expresiones regulares y control del evento submit.
 * La validación del servidor (PHP) se mantiene siempre.
 */

class Validador {
    constructor() {
        // Longitud mínima y máxima de cada tipo de campo (las mismas que comprueba el servidor)
        this.longitudes = {
            nombre: { min: 2, max: 100 },
            email: { min: 1, max: 150 },
            password: { min: 8, max: 72 },
            url: { min: 1, max: 255 },
            titulo: { min: 1, max: 150 },
            descripcion: { min: 1, max: 2000 }
        };

        // Expresiones regulares de cada tipo de campo
        this.patrones = {
            // Letras (con tildes y ñ), espacios, guiones y apóstrofos
            nombre: /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ' -]+$/,
            // texto@dominio.extensión, sin espacios
            email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            // Empieza por http:// o https:// y no tiene espacios
            url: /^https?:\/\/[^\s\/]+\.[^\s]+$/i,
            // Al menos un carácter que no sea un espacio
            titulo: /\S/,
            descripcion: /\S/,
            // Para la contraseña se usan dos comprobaciones: una letra y un número
            letra: /[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]/,
            numero: /[0-9]/
        };

        // Mensajes de error de cada tipo de campo
        this.mensajes = {
            nombre: "El nombre debe tener entre 2 y 100 letras (se admiten espacios, guiones y apóstrofos).",
            email: "Introduce un email válido, por ejemplo nombre@dominio.com (máximo 150 caracteres).",
            password: "La contraseña debe tener entre 8 y 72 caracteres, con al menos una letra y un número.",
            url: "Introduce una URL válida que empiece por http:// o https:// (máximo 255 caracteres).",
            titulo: "El título es obligatorio y no puede superar los 150 caracteres.",
            descripcion: "La descripción es obligatoria y no puede superar los 2000 caracteres."
        };

        // Avisos de error que hay en pantalla, para poder quitarlos después
        this.avisos = [];
    }

    /**
    * Comprueba un valor según su tipo de campo.
    * @param {string} tipo - Tipo de campo (nombre, email, password, url, titulo, descripcion)
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

        // Tipo de campo que no está definido
        const longitud = this.longitudes[tipo];
        if (longitud === undefined) {
            console.error("Validador: tipo de campo desconocido -> " + tipo);
            return false;
        }

        // Primero la longitud
        if (texto.length < longitud.min || texto.length > longitud.max) {
            return false;
        }

        // La contraseña necesita una letra y un número
        if (tipo === "password") {
            return this.patrones.letra.test(texto) && this.patrones.numero.test(texto);
        }

        // El resto de tipos se comprueba con su expresión regular
        return this.patrones[tipo].test(texto);
    }

    /**
    * Devuelve el mensaje de error de un tipo de campo.
    * @param {string} tipo - Tipo de campo
    * @returns {string} Mensaje de error (genérico si el tipo no existe)
    */
    obtenerMensaje(tipo) {
        return this.mensajes[tipo] || "El valor introducido no es válido.";
    }

    /**
    * Valida una lista de campos y devuelve los errores encontrados.
    * @param {Array<{id: string, tipo: string, valor: string, opcional?: boolean}>} campos
    * @returns {Array<{id: string, mensaje: string}>} Lista de errores (vacía si todo es válido)
    */
    validarCampos(campos) {
        const errores = [];

        // Se recorre cada campo y se guarda el error de los que no pasan la validación
        for (const campo of campos) {
            if (!this.validar(campo.tipo, campo.valor, campo.opcional)) {
                errores.push({
                    id: campo.id,
                    mensaje: this.obtenerMensaje(campo.tipo)
                });
            }
        }

        return errores;
    }

    /**
    * Lee los valores de los campos indicados en las reglas y los valida.
    * @param {Array<{id: string, tipo: string, opcional?: boolean}>} reglas
    *        id = atributo id del campo; tipo = tipo de campo
    * @returns {Array<{id: string, mensaje: string}>} Lista de errores (vacía si todo es válido)
    */
    validarFormulario(reglas) {
        const campos = [];

        for (const regla of reglas) {
            const elemento = document.getElementById(regla.id);

            if (!elemento) {
                console.warn("Validador: no existe el campo '" + regla.id + "' en la página");
            }

            campos.push({
                id: regla.id,
                tipo: regla.tipo,
                valor: elemento ? elemento.value : "",
                opcional: regla.opcional
            });
        }

        return this.validarCampos(campos);
    }

    /**
    * Muestra cada mensaje de error debajo de su campo.
    * @param {Array<{id: string, mensaje: string}>} errores - Resultado de validarFormulario()
    */
    mostrarErrores(errores) {
        for (const error of errores) {
            const campo = document.getElementById(error.id);

            // Si el campo no existe, no hay dónde mostrar el mensaje
            if (!campo) {
                continue;
            }

            // Clase de Bootstrap que marca el campo en rojo
            campo.classList.add("is-invalid");

            // Crear, configurar e insertar el aviso (textContent: se inserta como texto, nunca como HTML)
            const aviso = document.createElement("div");
            aviso.classList.add("invalid-feedback", "d-block");
            aviso.textContent = error.mensaje;
            campo.parentElement.appendChild(aviso);

            this.avisos.push({ campo: campo, aviso: aviso });
        }
    }

    /**
     * Quita los mensajes de error y las marcas de campo inválido que se mostraron.
     */
    limpiarErrores() {
        for (const elemento of this.avisos) {
            elemento.campo.classList.remove("is-invalid");
            elemento.aviso.remove();
        }

        this.avisos = [];
    }

    /**
    * Engancha la validación al evento submit de un formulario.
    * Si hay errores, los muestra y bloquea el envío.
    * @param {string} idFormulario - Atributo id del formulario
    * @param {Array<{id: string, tipo: string, opcional?: boolean}>} reglas
    */
    controlarSubmit(idFormulario, reglas) {
        const formulario = document.getElementById(idFormulario);

        // En las páginas que no tienen este formulario no se hace nada
        if (!formulario) {
            return;
        }

        // Función flecha: conserva el this de Validador dentro del evento
        formulario.addEventListener("submit", (evento) => {
            // Se parte siempre de un formulario sin errores de intentos anteriores
            this.limpiarErrores();

            const errores = this.validarFormulario(reglas);

            if (errores.length > 0) {
                // Se cancela el envío al servidor
                evento.preventDefault();
                this.mostrarErrores(errores);
            }
            // Si no hay errores, el formulario se envía con normalidad (POST)
        });
    }
}
