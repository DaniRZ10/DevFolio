<?php
// archivo: mis-proyectos.php
// Listado de los proyectos del usuario de la sesión, públicos y privados.
require_once 'includes/sesion.php';
require_once 'includes/conexion.php';
requerirSesion();

$sql = 'SELECT id, titulo, visibilidad, fecha_creacion
        FROM proyectos
        WHERE usuario_id = :usuario_id
        ORDER BY fecha_creacion DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute([':usuario_id' => $_SESSION['usuario_id']]);
$proyectos = $stmt->fetchAll();

// Mensaje de la operación anterior (crear, editar o eliminar). Se muestra una sola vez.
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);

$titulo = 'Mis proyectos';
require 'includes/cabecera.php';
?>

<h1>Mis proyectos</h1>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $mensaje['tipo'] ?>"><?= htmlspecialchars($mensaje['texto']) ?></div>
<?php endif; ?>

<p><a href="proyecto-form.php" class="btn btn-primary">Nuevo proyecto</a></p>

<?php if (count($proyectos) === 0): ?>
    <p>Todavía no has subido ningún proyecto.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th scope="col">Título</th>
                    <th scope="col">Visibilidad</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proyectos as $proyecto): ?>
                    <tr>
                        <td>
                            <a href="proyecto.php?id=<?= $proyecto['id'] ?>"><?= htmlspecialchars($proyecto['titulo']) ?></a>
                        </td>
                        <td>
                            <?php if ($proyecto['visibilidad'] === 'publico'): ?>
                                <span class="badge text-bg-success">Público</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Privado</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($proyecto['fecha_creacion'])) ?></td>
                        <td>
                            <a href="proyecto-form.php?id=<?= $proyecto['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <!-- Se elimina por POST, nunca con un enlace GET -->
                            <form action="proyecto-eliminar.php" method="post" class="form-eliminar d-inline">
                                <input type="hidden" name="id" value="<?= $proyecto['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require 'includes/pie.php'; ?>
