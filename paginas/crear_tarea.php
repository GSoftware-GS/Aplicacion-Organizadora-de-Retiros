<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $prioridad = $_POST['prioridad'];
    $estado = $_POST['estado'];
    $fecha_limite = $_POST['fecha_limite'];
    $asignado = isset($_POST['asignado']) ? (int)$_POST['asignado'] : 0;

    try {
        $pdo->beginTransaction();

        // Insertar la tarea en la tabla `tareas`
        $stmt = $pdo->prepare("
            INSERT INTO tareas (descripcion, prioridad, estado, fecha_limite, asignado_a)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$descripcion, $prioridad, $estado, $fecha_limite, $asignado]);
        $tarea_id = $pdo->lastInsertId();

        $pdo->commit();
        // Redirect or show success message
        header("Location: tareas.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al crear la tarea: " . $e->getMessage());
    }
}

// Obtener usuarios para el formulario
$stmt = $pdo->query("SELECT id, nombre, rol FROM usuarios");
$usuarios = $stmt->fetchAll();
?>

<?php
// ... (el código PHP anterior se mantiene igual)
?>

<!DOCTYPE html>
<html>
<head>
    <title>📝 Crear Tarea</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../components/header.php'; ?>

    <div class="form-container">
        <h2>✨ Crear Nueva Tarea</h2>
        <form method="POST">
            <div class="form-group">
                <label>📝 Descripción</label>
                <textarea name="descripcion" placeholder="Escribe aquí la descripción de la tarea..." required rows="4"></textarea>
            </div>

            <div class="form-group">
                <label>🚩 Prioridad</label>
                <select name="prioridad" required class="select-emoji">
                    <option value="alta">🔥 Alta</option>
                    <option value="media">⚠️ Media</option>
                    <option value="baja">🌱 Baja</option>
                </select>
            </div>

            <div class="form-group">
                <label>📊 Estado</label>
                <select name="estado" required>
                    <option value="pendiente">🔄 Pendiente</option>
                    <option value="en_proceso">⚡ En Proceso</option>
                    <option value="completada">✅ Completada</option>
                </select>
            </div>

            <div class="form-group">
                <label>📅 Fecha Límite</label>
                <input type="datetime-local" name="fecha_limite" required>
                <small class="hint">Selecciona fecha y hora</small>
            </div>

            <div class="form-group">
                <label>👤 Asignar a:</label>
                <select name="asignado" required>
                    <option value="">👉 Selecciona un usuario</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= $usuario['id'] ?>">
                            <?= $usuario['nombre'] ?> 
                            <?= $usuario['rol'] === 'admin' ? '👑' : '👤' ?> 
                            (<?= $usuario['rol'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="submit-button">💾 Guardar Tarea</button>
        </form>
    </div>

    <?php include '../components/footer.php'; ?>
</body>
</html>