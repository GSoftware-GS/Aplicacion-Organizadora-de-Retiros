<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

// Obtener fecha preseleccionada si existe
$fechaPreseleccionada = $_GET['fecha'] ?? '';
if ($fechaPreseleccionada) {
    // Convertir la fecha al formato adecuado para el input datetime-local
    $fechaPreseleccionada = date('Y-m-d\TH:i', strtotime($fechaPreseleccionada));
}

$colores_eventos = [
    'vuelo' => '#007bff',
    'clase_yoga' => '#28a745',
    'cena' => '#ffc107',
    'reunion' => '#dc3545',
    'otro' => '#ff0000'
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    

    $titulo = htmlspecialchars($_POST['titulo']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $tipo = $_POST['tipo'];
    

    try {
        $pdo->beginTransaction();

        // Insertar el evento en la tabla `eventos`
        $stmt = $pdo->prepare("
            INSERT INTO eventos (titulo, descripcion, fecha_inicio, fecha_fin, tipo, color)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$titulo, $descripcion, $fecha_inicio, $fecha_fin, $tipo, $colores_eventos[$tipo]]);
        $evento_id = $pdo->lastInsertId();

        // Insertar las relaciones con usuarios en la tabla `eventos_usuarios`
        if (!empty($_POST['usuarios'])) {
            $usuarios = $_POST['usuarios'];
            $roles = $_POST['roles'];

            $stmtRel = $pdo->prepare("
                INSERT INTO eventos_usuarios (evento_id, usuario_id, rol_asociado)
                VALUES (?, ?, ?)
            ");

            foreach ($usuarios as $index => $usuario_id) {
                $rol_asociado = $roles[$index];
                $stmtRel->execute([$evento_id, $usuario_id, $rol_asociado]);
            }
        }

        $pdo->commit();
        header("Location: calendario.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al crear el evento: " . $e->getMessage());
    }
}


// Obtener usuarios para el formulario
$stmt = $pdo->query("SELECT id, nombre, rol FROM usuarios");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>✨ Crear Evento</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../components/header.php'; ?>

    <div class="form-container">
        <h2>📝 Crear Nuevo Evento</h2>
        <form method="POST">
            <div class="form-group">
                <label>📌 Título</label>
                <input type="text" name="titulo" placeholder="Ej: Retiro de Yoga" required>
            </div>

            <div class="form-group">
                <label>📄 Descripción</label>
                <textarea name="descripcion" placeholder="Detalles del evento..."></textarea>
            </div>

            <div class="form-group">
                <label>⏰ Fecha y Hora de Inicio</label>
                <input type="datetime-local" name="fecha_inicio" 
                       value="<?= htmlspecialchars($fechaPreseleccionada) ?>" required>
            </div>

            <div class="form-group">
                <label>⏳ Fecha y Hora de Fin (opcional)</label>
                <input type="datetime-local" name="fecha_fin">
            </div>


            <div class="form-group">
                <label>📌 Tipo de Evento</label>
                <select name="tipo" required>
                    <option value="vuelo">✈️ Vuelo</option>
                    <option value="clase_yoga">🧘 Clase de Yoga</option>
                    <option value="cena">🍽️ Cena</option>
                    <option value="reunion">📅 Reunión</option>
                    <option value="otro">❓ Otro</option>
                </select>
            </div>

            <h3>👥 Asignar Participantes</h3>
            <div id="user-assignments">
                <div class="user-assignment">
                    <select name="usuarios[]" class="select-user">
                        <option value="">👤 Seleccionar usuario...</option>
                        <?php foreach ($usuarios as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= $user['nombre'] ?> (<?= $user['rol'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <select name="roles[]" class="select-role">
                        <option value="conductor">🚗 Conductor</option>
                        <option value="profesor">👨🏫 Profesor</option>
                        <option value="socio">🤝 Socio</option>
                        <option value="participante">🙋 Participante</option>
                        <option value="organizador">📋 Organizador</option>
                    </select>
                    <button type="button" class="btn-remove" onclick="removeAssignment(this)">❌ Eliminar</button>
                </div>
            </div>
            
            <button type="button" class="btn-add" onclick="addAssignment()">➕ Añadir Participante</button>

            <div class="form-actions">
                <button type="submit" class="btn-save">✅ Guardar Evento</button>
                <a href="calendario.php" class="btn-cancel">🚫 Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        function addAssignment() {
            const container = document.getElementById('user-assignments');
            const newAssignment = document.createElement('div');
            newAssignment.classList.add('user-assignment');
            newAssignment.innerHTML = `
                <select name="usuarios[]" class="select-user">
                    <option value="">👤 Seleccionar usuario...</option>
                    <?php foreach ($usuarios as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= $user['nombre'] ?> (<?= $user['rol'] ?>)</option>
                    <?php endforeach; ?>
                </select>
                <select name="roles[]" class="select-role">
                    <option value="conductor">🚗 Conductor</option>
                    <option value="profesor">👨🏫 Profesor</option>
                    <option value="socio">🤝 Socio</option>
                    <option value="participante">🙋 Participante</option>
                    <option value="organizador">📋 Organizador</option>
                </select>
                <button type="button" class="btn-remove" onclick="removeAssignment(this)">❌ Eliminar</button>
            `;
            container.appendChild(newAssignment);
        }

        function removeAssignment(button) {
            button.parentElement.remove();
        }
    </script>

    <?php include '../components/footer.php'; ?>
</body>
</html>