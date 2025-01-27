<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evento_id = $_GET['id'] ?? null;
    
    // Eliminar relaciones primero
    $pdo->prepare("DELETE FROM eventos_usuarios WHERE evento_id = ?")->execute([$evento_id]);
    
    // Eliminar evento
    $pdo->prepare("DELETE FROM eventos WHERE id = ?")->execute([$evento_id]);
    
    header("Location: calendario.php");
    exit;
}