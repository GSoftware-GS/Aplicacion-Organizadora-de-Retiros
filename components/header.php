<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicación Organizadora de Retiros</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <!-- Logo -->
            <div class="logo">
                <h1>🌿 RetirosApp</h1>
            </div>

            <!-- Menú de Navegación -->
            <nav class="nav-menu">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <!-- Menú para usuarios logueados -->
                    <a href="panel_control.php">Panel</a>
                    <a href="calendario.php">Calendario</a>
                    <a href="gestion_tareas.php">Tareas</a>
                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                        <a href="gestion_usuarios.php">Usuarios</a>
                    <?php endif; ?>
                    <a href="../includes/logout.php" class="logout">Cerrar Sesión</a>
                <?php else: ?>
                    <!-- Menú para invitados -->
                    <a href="login.php">Iniciar Sesión</a>
                    <a href="registro.php">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>