<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = htmlspecialchars($_POST['titulo']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $tipo = $_POST['tipo'];
    $color = $_POST['color'];
    $asignado_a = $_POST['asignado_a'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO eventos (titulo, descripcion, fecha_inicio, tipo, color, asignado_a)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$titulo, $descripcion, $fecha_inicio, $tipo, $color, $asignado_a]);
    header("Location: calendario.php");
    exit();
}

// Obtener usuarios para asignación
$stmt = $pdo->query("SELECT id, nombre FROM usuarios");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crear Evento</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../components/header.php'; ?>

    <div class="form-container">
        <h2>Crear Nuevo Evento</h2>
        <form method="POST">
            <input type="text" name="titulo" placeholder="Título" required>
            <textarea name="descripcion" placeholder="Descripción"></textarea>
            <input type="datetime-local" name="fecha_inicio" required>
            <select name="tipo" required>
                <option value="grupal">Evento Grupal</option>
                <option value="privado">Evento Privado</option>
            </select>
            <input type="color" name="color" value="#007bff">
            <select name="asignado_a">
                <option value="">Sin asignar</option>
                <?php foreach ($usuarios as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= $user['nombre'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Guardar Evento</button>
        </form>
    </div>

    <?php include '../components/footer.php'; ?>
</body>
</html>