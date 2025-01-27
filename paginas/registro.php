<?php
require_once '../includes/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validar email único
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $error = "El email ya está registrado";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?, ?, ?, 'asistente')");
        if ($stmt->execute([$nombre, $email, $password_hash])) {
            header("Location: login.php?registro=exito");
            exit();
        } else {
            $error = "Error al registrar el usuario";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <div class="registro-container">
        <h2>Registro de Usuario</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>

    <?php include '../components/footer.php'; ?>
</body>
</html>