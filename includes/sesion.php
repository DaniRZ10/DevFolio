<?php
// archivo: includes/sesion.php
// Arranque de la sesión y control de acceso. Se incluye con require_once al principio
// de cada página que use $_SESSION (antes de enviar cualquier salida al navegador).

// La sesión se inicia una sola vez, aunque este archivo se incluya varias veces.
// La cookie de sesión no es accesible desde JavaScript ni se envía desde otros sitios.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Indica si hay un usuario con la sesión iniciada.
 */
function haySesion(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Exige que haya sesión iniciada. Si no la hay, guarda un aviso y redirige a login.php.
 * Uso: al principio de cada página privada, después del require_once de este archivo.
 */
function requerirSesion(): void
{
    if (!haySesion()) {
        $_SESSION['mensaje'] = [
            'tipo'  => 'warning',
            'texto' => 'Debes iniciar sesión para acceder a esta página.',
        ];
        header('Location: login.php');
        exit;
    }
}
