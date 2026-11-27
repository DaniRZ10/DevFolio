<?php
// archivo: includes/funciones.php
// Funciones reutilizables para las imágenes subidas (portadas de proyectos y avatares).

// Tamaño máximo de una imagen: 2 MB
const TAM_MAX_IMAGEN = 2 * 1024 * 1024;

// Tipos MIME permitidos y extensión con la que se guarda cada uno
const TIPOS_IMAGEN = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

/**
 * Valida y guarda en uploads/ una imagen recibida en $_FILES.
 * Devuelve ['archivo' => nombre guardado o null, 'error' => mensaje o null].
 * Si no se ha elegido ningún archivo no es un error: la imagen es opcional.
 */
function subirImagen(array $archivo, string $prefijo): array
{
    if ($archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['archivo' => null, 'error' => null];
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['archivo' => null, 'error' => 'No se ha podido subir la imagen.'];
    }
    if ($archivo['size'] > TAM_MAX_IMAGEN) {
        return ['archivo' => null, 'error' => 'La imagen no puede superar los 2 MB.'];
    }

    // Se comprueba el tipo real del contenido, no la extensión del nombre
    $tipo = mime_content_type($archivo['tmp_name']);
    if (!isset(TIPOS_IMAGEN[$tipo])) {
        return ['archivo' => null, 'error' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.'];
    }

    // Nombre nuevo y único: no se sobrescriben archivos ni se usa el nombre que envía el usuario
    $nombre = $prefijo . '_' . uniqid() . '.' . TIPOS_IMAGEN[$tipo];
    if (!move_uploaded_file($archivo['tmp_name'], __DIR__ . '/../uploads/' . $nombre)) {
        return ['archivo' => null, 'error' => 'No se ha podido guardar la imagen.'];
    }

    return ['archivo' => $nombre, 'error' => null];
}

/**
 * Borra una imagen de uploads/ si existe.
 */
function borrarImagen(?string $nombre): void
{
    if ($nombre && is_file(__DIR__ . '/../uploads/' . $nombre)) {
        unlink(__DIR__ . '/../uploads/' . $nombre);
    }
}
