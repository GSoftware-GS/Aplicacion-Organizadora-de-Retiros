<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

// Verificar permisos y obtener evento
$evento_id = $_GET['id'] ?? null;

// Obtener datos básicos del evento
$stmt = $pdo->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->execute([$evento_id]);
$evento = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener usuarios y asignaciones (CORRECCIÓN AQUÍ)
$usuarios = $pdo->query("SELECT id, nombre FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
$asignados = $pdo->prepare("SELECT usuario_id, rol_asociado FROM eventos_usuarios WHERE evento_id = ?");
$asignados->execute([$evento_id]);
$asignaciones = $asignados->fetchAll(PDO::FETCH_KEY_PAIR); // Ahora funciona correctamente

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar datos básicos
    $stmt = $pdo->prepare("UPDATE eventos SET titulo=?, descripcion=?, fecha_inicio=?, color=? WHERE id=?");
    $stmt->execute([
        $_POST['titulo'],
        $_POST['descripcion'],
        $_POST['fecha_inicio'],
        $_POST['color'],
        $evento_id
    ]);

    // Actualizar asignaciones
    $pdo->prepare("DELETE FROM eventos_usuarios WHERE evento_id = ?")->execute([$evento_id]);
    foreach ($_POST['usuarios'] ?? [] as $usuario_id) {
        $pdo->prepare("INSERT INTO eventos_usuarios (evento_id, usuario_id, rol_asociado) VALUES (?, ?, ?)")
            ->execute([$evento_id, $usuario_id, $_POST['roles'][$usuario_id]]);
    }

    header("Location: calendario.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Editar Evento</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <?php include '../components/header.php'; ?>

    <div class="container">
        <h1>Editar Evento</h1>

        <form method="post" class="event-form">
            <div class="form-group">
                <label>Título:</label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($evento['titulo']) ?>" required>
            </div>

            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion"><?= htmlspecialchars($evento['descripcion']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Fecha y Hora:</label>
                <input type="datetime-local" name="fecha_inicio"
                    value="<?= date('Y-m-d\TH:i', strtotime($evento['fecha_inicio'])) ?>" required>
            </div>

            <div class="form-group">
                <label>Color:</label>
                <input type="color" name="color" value="<?= $evento['color'] ?>">
            </div>

            <div class="form-group">
                <fieldset>
                    <legend>Asignar usuarios:</legend>
                    <div class="user-assignments">
                        <?php foreach ($usuarios as $u): ?>
                            <div class="user-checkbox">
                                <label>
                                    <input type="checkbox" name="usuarios[]" value="<?= $u['id'] ?>"
                                        <?= isset($asignaciones[$u['id']]) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($u['nombre']) ?>
                                </label>
                                <select name="roles[<?= $u['id'] ?>]" class="role-input">
                                    <option value="">Seleccionar rol</option>
                                    <option value="conductor" <?= ($asignaciones[$u['id']] ?? '') === 'conductor' ? 'selected' : '' ?>>Conductor</option>
                                    <option value="profesor" <?= ($asignaciones[$u['id']] ?? '') === 'profesor' ? 'selected' : '' ?>>Profesor</option>
                                    <option value="socio" <?= ($asignaciones[$u['id']] ?? '') === 'socio' ? 'selected' : '' ?>>
                                        Socio</option>
                                    <option value="participante" <?= ($asignaciones[$u['id']] ?? '') === 'participante' ? 'selected' : '' ?>>Participante</option>
                                    <option value="organizador" <?= ($asignaciones[$u['id']] ?? '') === 'organizador' ? 'selected' : '' ?>>Organizador</option>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Guardar Cambios</button>
                <a href="calendario.php" class="btn cancel">Cancelar</a>
            </div>
        </form>
    </div>

    <?php include '../components/footer.php'; ?>
</body>

</html>