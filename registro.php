<?php
// archivo: registro.php
// Formulario de registro: valida los datos en el servidor, comprueba que el email sea único
// y guarda la contraseña cifrada con password_hash().
require_once 'includes/sesion.php';
require_once 'includes/conexion.php';

// Quien ya tiene sesión no necesita registrarse
if (haySesion()) {
    header('Location: mis-proyectos.php');
    exit;
}

$errores = [];   // Mensajes de error, indexados por nombre de campo
$nombre = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Los datos llegan sin tratar: se recortan los espacios (menos en la contraseña)
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Mismas reglas que js/validacion.js: el cliente se puede saltar, el servidor no
    $longitudNombre = mb_strlen($nombre);
    if ($longitudNombre < 2 || $longitudNombre > 100
        || !preg_match("/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ' -]+$/u", $nombre)) {
        $errores['nombre'] = 'El nombre debe tener entre 2 y 100 letras (se admiten espacios, guiones y apóstrofos).';
    }

    if (mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'Introduce un email válido, por ejemplo nombre@dominio.com (máximo 150 caracteres).';
    }

    $longitudPassword = mb_strlen($password);
    if ($longitudPassword < 8 || $longitudPassword > 72
        || !preg_match('/[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]/u', $password)
        || !preg_match('/[0-9]/', $password)) {
        $errores['password'] = 'La contraseña debe tener entre 8 y 72 caracteres, con al menos una letra y un número.';
    }

    // Email único: se comprueba antes de insertar para dar un mensaje claro
    if (!isset($errores['email'])) {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errores['email'] = 'Ya existe una cuenta con ese email.';
        }
    }

    // Si todo es correcto se guarda el usuario
    if (!$errores) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombre, email, password_hash)
                 VALUES (:nombre, :email, :password_hash)'
            );
            $stmt->execute([
                ':nombre'        => $nombre,
                ':email'         => $email,
                // La contraseña nunca se guarda en texto plano
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            // Redirección tras el POST: evita reenviar el formulario al recargar la página
            $_SESSION['mensaje'] = [
                'tipo'  => 'success',
                'texto' => 'Cuenta creada correctamente. Ya puedes iniciar sesión.',
            ];
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            // 23000 = clave única duplicada (dos registros a la vez con el mismo email)
            if ($e->getCode() === '23000') {
                $errores['email'] = 'Ya existe una cuenta con ese email.';
            } else {
                error_log('Error al registrar usuario: ' . $e->getMessage());
                $errores['general'] = 'No se ha podido crear la cuenta. Inténtalo de nuevo más tarde.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta - DevFolio</title>
    <!-- TEMPORAL: sustituir por includes/cabecera.php cuando esté la plantilla (#12) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <h1 class="h3 mb-4">Crear cuenta</h1>

            <?php if (isset($errores['general'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errores['general'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- id="form-registro" y los id de los campos los usa js/validacion.js -->
            <form id="form-registro" method="post" action="registro.php" novalidate>
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" maxlength="100" required
                           class="form-control<?= isset($errores['nombre']) ? ' is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errores['nombre'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" maxlength="150" required
                           class="form-control<?= isset($errores['email']) ? ' is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errores['email'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" maxlength="72" required
                           autocomplete="new-password"
                           class="form-control<?= isset($errores['password']) ? ' is-invalid' : '' ?>">
                    <?php if (isset($errores['password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errores['password'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Crear cuenta</button>
            </form>

            <p class="mt-3">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        </div>
    </div>
</main>
<!-- TEMPORAL: este script debe cargarse desde includes/pie.php cuando exista (#12); no cargarlo dos veces -->
<script src="js/validacion.js"></script>
</body>
</html>
