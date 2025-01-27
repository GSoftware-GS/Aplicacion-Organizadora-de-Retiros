<?php
require_once '../includes/auth.php'; // Verifica autenticación
require_once '../includes/db_connect.php';

// Obtener información del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'] ?? 'asistente';

// Obtener próximos eventos (próximos 7 días)
$eventos = [];
$stmt_eventos = $pdo->prepare("
    SELECT * FROM eventos 
    WHERE fecha_inicio BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
    ORDER BY fecha_inicio ASC
    LIMIT 5
");
$stmt_eventos->execute();
$eventos = $stmt_eventos->fetchAll();

// Obtener tareas pendientes del usuario
$tareas = [];
$stmt_tareas = $pdo->prepare("
    SELECT * FROM tareas 
    WHERE asignado_a = ? 
    AND estado != 'completada'
    ORDER BY 
        FIELD(prioridad, 'alta', 'media', 'baja'),
        fecha_limite ASC
    LIMIT 5
");
$stmt_tareas->execute([$usuario_id]);
$tareas = $stmt_tareas->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel de Control</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../components/header.php'; ?>

    <div class="dashboard-container">
        <h2>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></h2>

        <!-- Sección de Accesos Rápidos -->
        <div class="quick-actions">
            <a href="calendario.php" class="action-card">
                <h3>📅 Nuevo Evento</h3>
                <p>Programa una nueva actividad</p>
            </a>
            
            <?php if ($rol === 'admin'): ?>
                <a href="gestion_usuarios.php" class="action-card">
                    <h3>👥 Gestionar Usuarios</h3>
                    <p>Administra roles y permisos</p>
                </a>
            <?php endif; ?>

            <a href="gestion_tareas.php" class="action-card">
                <h3>✅ Mis Tareas</h3>
                <p>Ver todas mis asignaciones</p>
            </a>
        </div>

        <!-- Resumen de Eventos -->
        <div class="dashboard-section">
            <h3>📌 Próximos Eventos</h3>
            <div class="eventos-list">
                <?php foreach ($eventos as $evento): ?>
                    <div class="evento-card" style="border-left: 5px solid <?= $evento['color'] ?>">
                        <div class="evento-header">
                            <h4><?= htmlspecialchars($evento['titulo']) ?></h4>
                            <span class="fecha">
                                <?= date('d M H:i', strtotime($evento['fecha_inicio'])) ?>
                            </span>
                        </div>
                        <p><?= htmlspecialchars($evento['descripcion']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lista de Tareas -->
        <div class="dashboard-section">
            <h3>📋 Tareas Pendientes</h3>
            <div class="tareas-list">
                <?php foreach ($tareas as $tarea): ?>
                    <div class="tarea-card <?= $tarea['prioridad'] ?>">
                        <div class="tarea-header">
                            <h4><?= htmlspecialchars($tarea['descripcion']) ?></h4>
                            <span class="prioridad"><?= $tarea['prioridad'] ?></span>
                        </div>
                        <div class="tarea-footer">
                            <span>Vence: <?= date('d M', strtotime($tarea['fecha_limite'])) ?></span>
                            <a href="../includes/marcar_tarea.php?id=<?= $tarea['id'] ?>&estado=en_progreso" 
                               class="btn">En Progreso</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
</body>
</html>