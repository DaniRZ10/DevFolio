<?php
// archivo: proyecto-eliminar.php
// Elimina un proyecto propio. Solo acepta POST (el formulario está en mis-proyectos.php).
require_once 'includes/sesion.php';
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';
requerirSesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mis-proyectos.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$usuarioId = $_SESSION['usuario_id'];
$borrado = false;

if ($id) {
    try {
        // Se lee la portada antes de borrar para eliminar también el archivo
        $stmt = $pdo->prepare('SELECT portada FROM proyectos WHERE id = :id AND usuario_id = :usuario_id');
        $stmt->execute([':id' => $id, ':usuario_id' => $usuarioId]);
        $portada = $stmt->fetchColumn();

        // El WHERE con usuario_id impide borrar proyectos de otros usuarios.
        // Sus filas de proyecto_etiqueta se borran solas por ON DELETE CASCADE.
        $stmt = $pdo->prepare('DELETE FROM proyectos WHERE id = :id AND usuario_id = :usuario_id');
        $stmt->execute([':id' => $id, ':usuario_id' => $usuarioId]);

        // rowCount() indica cuántas filas se han borrado: 0 si no existe o no es del usuario
        if ($stmt->rowCount() > 0) {
            $borrado = true;
            borrarImagen($portada ?: null);
        }
    } catch (PDOException $e) {
        error_log('Error al eliminar el proyecto: ' . $e->getMessage());
    }
}

if ($borrado) {
    $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Proyecto eliminado.'];
} else {
    $_SESSION['mensaje'] = ['tipo' => 'danger', 'texto' => 'No se ha podido eliminar el proyecto.'];
}

// Redirección después del POST: recargar la página no repite el borrado
header('Location: mis-proyectos.php');
exit;
