<?php
session_start();

// Redirige si no hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../paginas/login.php");
    exit();
}
?>