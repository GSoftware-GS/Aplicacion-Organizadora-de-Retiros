<?php
// Iniciar sesión y verificar autenticación
session_start();

// Redirigir al panel de control si el usuario está logueado
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: paginas/panel_control.php");
        exit();
    }

} else {
    // Redirigir al login si no hay sesión activa
    header("Location: paginas/login.php");
    exit();
}
?>