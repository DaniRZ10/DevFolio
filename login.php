<?php
// archivo: login.php
// Inicio de sesión: comprueba el email y la contraseña con password_verify()
// y guarda el usuario en $_SESSION.
require_once 'includes/sesion.php';
require_once 'includes/conexion.php';

// Quien ya tiene sesión no necesita iniciarla otra vez
if (haySesion()) {
    header('Location: mis-proyectos.php');
    exit;
}

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Consulta preparada: el email del usuario nunca se concatena en el SQL
    $stmt = $pdo->prepare('SELECT id, nombre, password_hash FROM usuarios WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Nuevo identificador de sesión al iniciar sesión (evita la fijación de sesión)
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];

        // Redirección tras el POST
        header('Location: mis-proyectos.php');
        exit;
    }

    // Mismo mensaje si falla el email o la contraseña: no se revela cuál de los dos es
    $error = 'Email o contraseña incorrectos.';
}

// Aviso dejado en la sesión por otra página (registro.php o requerirSesion())
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

// Aviso tras cerrar sesión (viene de logout.php; el texto es fijo, no se imprime nada recibido)
if (($_GET['sesion'] ?? '') === 'cerrada') {
    $mensaje = ['tipo' => 'info', 'texto' => 'Has cerrado sesión.'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - DevFolio</title>
    <!-- TEMPORAL: sustituir por includes/cabecera.php cuando esté la plantilla (#12) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <h1 class="h3 mb-4">Iniciar sesión</h1>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?= htmlspecialchars($mensaje['tipo'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($mensaje['texto'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- Aquí no se usa Validador: el login no debe mostrar las reglas de la contraseña -->
            <form id="form-login" method="post" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" maxlength="150" required
                           class="form-control"
                           value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" required
                           autocomplete="current-password" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>

            <p class="mt-3">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
        </div>
    </div>
</main>
</body>
</html>
