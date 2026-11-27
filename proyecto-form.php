<?php
// archivo: proyecto-form.php
// Formulario para crear un proyecto nuevo o editar uno propio (proyecto-form.php?id=...).
require_once 'includes/sesion.php';
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';
requerirSesion();

$usuarioId = $_SESSION['usuario_id'];
$errores = [];

// Catálogo de etiquetas para los checkboxes
$etiquetas = $pdo->query('SELECT id, nombre FROM etiquetas ORDER BY nombre')->fetchAll();
$idsEtiquetas = array_column($etiquetas, 'id');

// Valores iniciales del formulario (proyecto nuevo)
$proyecto = [
    'titulo'      => '',
    'descripcion' => '',
    'url_repo'    => '',
    'visibilidad' => 'publico',
    'portada'     => null,
];
$marcadas = [];

// Id del proyecto a editar: llega por GET al abrir el formulario y por POST al enviarlo
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$esEdicion = (bool) $id;

if ($esEdicion) {
    // Solo se puede editar un proyecto propio
    $stmt = $pdo->prepare('SELECT * FROM proyectos WHERE id = :id AND usuario_id = :usuario_id');
    $stmt->execute([':id' => $id, ':usuario_id' => $usuarioId]);
    $encontrado = $stmt->fetch();

    if (!$encontrado) {
        $_SESSION['mensaje'] = ['tipo' => 'danger', 'texto' => 'El proyecto no existe o no es tuyo.'];
        header('Location: mis-proyectos.php');
        exit;
    }
    $proyecto = $encontrado;

    $stmt = $pdo->prepare('SELECT etiqueta_id FROM proyecto_etiqueta WHERE proyecto_id = :id');
    $stmt->execute([':id' => $id]);
    $marcadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los datos. Se guardan tal cual y se escapan con htmlspecialchars al mostrarlos.
    $proyecto['titulo']      = trim(filter_input(INPUT_POST, 'titulo') ?? '');
    $proyecto['descripcion'] = trim(filter_input(INPUT_POST, 'descripcion') ?? '');
    $proyecto['url_repo']    = trim(filter_input(INPUT_POST, 'url_repo') ?? '');
    $proyecto['visibilidad'] = filter_input(INPUT_POST, 'visibilidad') ?? '';
    $marcadas = filter_input(INPUT_POST, 'etiquetas', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY) ?? [];

    // Validación en servidor (se hace siempre, aunque el navegador ya valide con JavaScript)
    if ($proyecto['titulo'] === '' || mb_strlen($proyecto['titulo']) > 150) {
        $errores[] = 'El título es obligatorio y no puede superar los 150 caracteres.';
    }
    if ($proyecto['descripcion'] === '' || mb_strlen($proyecto['descripcion']) > 2000) {
        $errores[] = 'La descripción es obligatoria y no puede superar los 2000 caracteres.';
    }
    if ($proyecto['url_repo'] !== '' && (!filter_var($proyecto['url_repo'], FILTER_VALIDATE_URL) || mb_strlen($proyecto['url_repo']) > 255)) {
        $errores[] = 'La URL del repositorio no es válida.';
    }
    if (!in_array($proyecto['visibilidad'], ['publico', 'privado'], true)) {
        $errores[] = 'Elige si el proyecto es público o privado.';
    }

    // Solo se aceptan etiquetas que existen en la base de datos, y como máximo 5
    $marcadas = array_values(array_intersect($marcadas, $idsEtiquetas));
    if (count($marcadas) > 5) {
        $errores[] = 'Puedes marcar como máximo 5 etiquetas.';
    }

    // La portada solo se sube si el resto de datos es correcto, para no dejar archivos sueltos
    $portadaNueva = null;
    if (!$errores) {
        $subida = subirImagen($_FILES['portada'] ?? ['error' => UPLOAD_ERR_NO_FILE], 'proyecto');
        if ($subida['error']) {
            $errores[] = $subida['error'];
        } else {
            $portadaNueva = $subida['archivo'];
        }
    }

    if (!$errores) {
        $portadaAnterior = $proyecto['portada'];
        if ($portadaNueva) {
            $proyecto['portada'] = $portadaNueva;
        }

        $datos = [
            ':titulo'      => $proyecto['titulo'],
            ':descripcion' => $proyecto['descripcion'],
            ':url_repo'    => $proyecto['url_repo'] !== '' ? $proyecto['url_repo'] : null,
            ':portada'     => $proyecto['portada'],
            ':visibilidad' => $proyecto['visibilidad'],
            ':usuario_id'  => $usuarioId,
        ];

        try {
            // El proyecto y sus etiquetas se guardan juntos: o se guarda todo o no se guarda nada
            $pdo->beginTransaction();

            if ($esEdicion) {
                $sql = 'UPDATE proyectos
                        SET titulo = :titulo, descripcion = :descripcion, url_repo = :url_repo,
                            portada = :portada, visibilidad = :visibilidad
                        WHERE id = :id AND usuario_id = :usuario_id';
                $datos[':id'] = $id;
                $pdo->prepare($sql)->execute($datos);

                // Se quitan las etiquetas anteriores y se insertan las marcadas ahora
                $stmt = $pdo->prepare('DELETE FROM proyecto_etiqueta WHERE proyecto_id = :id');
                $stmt->execute([':id' => $id]);
                $proyectoId = $id;
            } else {
                $sql = 'INSERT INTO proyectos (usuario_id, titulo, descripcion, url_repo, portada, visibilidad)
                        VALUES (:usuario_id, :titulo, :descripcion, :url_repo, :portada, :visibilidad)';
                $pdo->prepare($sql)->execute($datos);
                $proyectoId = (int) $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare('INSERT INTO proyecto_etiqueta (proyecto_id, etiqueta_id) VALUES (:proyecto_id, :etiqueta_id)');
            foreach ($marcadas as $etiquetaId) {
                $stmt->execute([':proyecto_id' => $proyectoId, ':etiqueta_id' => $etiquetaId]);
            }

            $pdo->commit();

            // Si se ha cambiado la portada, se borra el archivo de la anterior
            if ($portadaNueva && $portadaAnterior) {
                borrarImagen($portadaAnterior);
            }

            $_SESSION['mensaje'] = [
                'tipo'  => 'success',
                'texto' => $esEdicion ? 'Proyecto actualizado.' : 'Proyecto creado.',
            ];
            // Redirección después del POST: recargar la página no vuelve a enviar el formulario
            header('Location: mis-proyectos.php');
            exit;
        } catch (PDOException $e) {
            // Si algo falla se deshace todo y se borra la imagen recién subida
            $pdo->rollBack();
            borrarImagen($portadaNueva);
            $proyecto['portada'] = $portadaAnterior;
            error_log('Error al guardar el proyecto: ' . $e->getMessage());
            $errores[] = 'No se ha podido guardar el proyecto. Inténtalo de nuevo.';
        }
    }
}

$titulo = $esEdicion ? 'Editar proyecto' : 'Nuevo proyecto';
require 'includes/cabecera.php';
?>

<h1><?= $titulo ?></h1>

<?php if ($errores): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- novalidate: la validación en el navegador la hace js/validacion.js y, siempre, el servidor -->
<form id="form-proyecto" action="proyecto-form.php" method="post" enctype="multipart/form-data" novalidate>
    <?php if ($esEdicion): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="titulo" class="form-label">Título</label>
        <input type="text" id="titulo" name="titulo" class="form-control" maxlength="150" required
               value="<?= htmlspecialchars($proyecto['titulo']) ?>">
    </div>

    <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea id="descripcion" name="descripcion" class="form-control" rows="5" maxlength="2000" required><?= htmlspecialchars($proyecto['descripcion']) ?></textarea>
    </div>

    <div class="mb-3">
        <label for="url_repo" class="form-label">URL del repositorio (opcional)</label>
        <input type="url" id="url_repo" name="url_repo" class="form-control" maxlength="255"
               placeholder="https://github.com/usuario/proyecto"
               value="<?= htmlspecialchars($proyecto['url_repo'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label for="visibilidad" class="form-label">Visibilidad</label>
        <select id="visibilidad" name="visibilidad" class="form-select">
            <option value="publico" <?= $proyecto['visibilidad'] === 'publico' ? 'selected' : '' ?>>Público: lo ve cualquiera</option>
            <option value="privado" <?= $proyecto['visibilidad'] === 'privado' ? 'selected' : '' ?>>Privado: solo lo ves tú</option>
        </select>
    </div>

    <fieldset class="mb-3">
        <legend class="form-label fs-6">Etiquetas (máximo 5)</legend>
        <?php foreach ($etiquetas as $etiqueta): ?>
            <div class="form-check form-check-inline">
                <input type="checkbox" id="etiqueta-<?= $etiqueta['id'] ?>" name="etiquetas[]" class="form-check-input"
                       value="<?= $etiqueta['id'] ?>" <?= in_array($etiqueta['id'], $marcadas) ? 'checked' : '' ?>>
                <label for="etiqueta-<?= $etiqueta['id'] ?>" class="form-check-label"><?= htmlspecialchars($etiqueta['nombre']) ?></label>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <div class="mb-3">
        <label for="portada" class="form-label">Imagen de portada (opcional, JPG, PNG, WEBP o GIF, máximo 2 MB)</label>
        <?php if ($proyecto['portada']): ?>
            <p><img src="uploads/<?= htmlspecialchars($proyecto['portada']) ?>" alt="Portada actual" class="img-thumbnail" width="200"></p>
        <?php endif; ?>
        <input type="file" id="portada" name="portada" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="mis-proyectos.php" class="btn btn-link">Cancelar</a>
</form>

<?php require 'includes/pie.php'; ?>
