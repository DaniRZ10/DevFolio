<?php
// archivo: logout.php
// Cierre de sesión: vacía $_SESSION, borra la cookie de sesión y destruye la sesión.
require_once 'includes/sesion.php';

// 1. Se vacían todos los datos de la sesión
$_SESSION = [];

// 2. Se borra la cookie de sesión del navegador (con los mismos parámetros con los que se creó)
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $parametros['path'],
        'domain'   => $parametros['domain'],
        'secure'   => $parametros['secure'],
        'httponly' => $parametros['httponly'],
        'samesite' => $parametros['samesite'] ?: 'Lax',
    ]);
}

// 3. Se destruye la sesión en el servidor
session_destroy();

// Redirección al login con un aviso (login.php muestra el texto fijo "Has cerrado sesión")
header('Location: login.php?sesion=cerrada');
exit;
