<?php

// La sesión puede venir ya iniciada (includes/sesion.php) pero si no, se inicia aquí para saber si hay usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$hayUsuario  = isset($_SESSION['usuario_id']);
$paginaActual = basename($_SERVER['SCRIPT_NAME']);

// Tema guardado en la cookie (la crea js/tema.js)
// Leerlo aquí evita parpadeo al cargar la página
$temaOscuro = ($_COOKIE['tema'] ?? '') === 'oscuro';

// Enlaces del menú: archivo => texto
// Los de la derecha cambian según haya sesión o no
$enlacesMenu = [
    'index.php'      => 'Inicio',
    'portfolios.php' => 'Portfolios',
    'acerca.php'     => 'Cómo funciona',
];

if ($hayUsuario) {
    $enlacesUsuario = [
        'mis-proyectos.php' => 'Mis proyectos',
        'perfil.php'        => 'Mi perfil',
        'logout.php'        => 'Cerrar sesión',
    ];
} else {
    $enlacesUsuario = [
        'login.php'    => 'Iniciar sesión',
        'registro.php' => 'Registrarse',
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'DevFolio') ?> · DevFolio</title>

    <!-- Tipografías de la guía de estilo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Space+Grotesk:wght@500;700&display=swap">

    <!-- Bootstrap 5.3.3 por CDN (maquetación responsive y componentes) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Estilos propios (compilados desde scss/). Van después de Bootstrap para poder sobrescribirlo -->
    <link rel="stylesheet" href="css/estilos.css">

    <!-- Tema oscurp: solo se activa si la cookie dice "oscuro" o al pulsar el botón -->
    <link rel="<?= $temaOscuro ? 'stylesheet' : 'alternate stylesheet' ?>" href="css/oscuro.css"
          id="tema-oscuro" title="Modo oscuro">

    <!-- Scripts en el <head> con defer: se descargan sin bloquear y se ejecutan con el HTML ya analizado -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script defer src="js/tema.js"></script>
    <?php foreach ($scripts ?? [] as $script): ?>
        <script defer src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
</head>
<body class="d-flex flex-column min-vh-100">

<a href="#contenido" class="saltar-contenido">Saltar al contenido</a>

<header>
    <nav class="navbar navbar-expand-md barra-nav">
        <div class="container">
            <!-- Aquí hay que poner el logo SVG inline (issue #14) -->
            <a class="navbar-brand marca" href="index.php">DevFolio</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-principal"
                    aria-controls="menu-principal" aria-expanded="false" aria-label="Abrir el menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menu-principal">
                <ul class="navbar-nav me-auto">
                    <?php foreach ($enlacesMenu as $archivo => $texto): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $archivo === $paginaActual ? ' active' : '' ?>" href="<?= $archivo ?>"
                               <?= $archivo === $paginaActual ? 'aria-current="page"' : '' ?>><?= $texto ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <ul class="navbar-nav align-items-md-center">
                    <?php foreach ($enlacesUsuario as $archivo => $texto): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $archivo === $paginaActual ? ' active' : '' ?>" href="<?= $archivo ?>"
                               <?= $archivo === $paginaActual ? 'aria-current="page"' : '' ?>><?= $texto ?></a>
                        </li>
                    <?php endforeach; ?>
                    <li class="nav-item ms-md-2 mt-2 mt-md-0">
                        <button type="button" id="btn-tema" class="btn btn-outline-primary boton-tema"
                                aria-pressed="<?= $temaOscuro ? 'true' : 'false' ?>">
                            <?= $temaOscuro ? 'Modo claro' : 'Modo oscuro' ?>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="contenido" class="container flex-grow-1 py-4">