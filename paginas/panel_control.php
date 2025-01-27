<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

$usuario_id = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'] ?? 'asistente';

// Obtener eventos
$stmt = $pdo->prepare("
    SELECT e.id, e.titulo, e.descripcion, e.fecha_inicio, e.color, u.nombre AS asignado_nombre, eu.rol_asociado
    FROM eventos e
    LEFT JOIN eventos_usuarios eu ON e.id = eu.evento_id
    LEFT JOIN usuarios u ON eu.usuario_id = u.id
    ORDER BY e.fecha_inicio
");
$stmt->execute();
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$eventosAgrupados = [];
foreach ($eventos as $evento) {
    if (!isset($eventosAgrupados[$evento['id']])) {
        $eventosAgrupados[$evento['id']] = [
            'id' => $evento['id'],
            'titulo' => $evento['titulo'],
            'descripcion' => $evento['descripcion'],
            'fecha_inicio' => $evento['fecha_inicio'],
            'color' => $evento['color'],
            'usuarios' => []
        ];
    }
    if ($evento['asignado_nombre']) {
        $eventosAgrupados[$evento['id']]['usuarios'][] = [
            'nombre' => $evento['asignado_nombre'],
            'rol' => $evento['rol_asociado']
        ];
    }
}

// Obtener tareas con estado
$stm = $pdo->prepare("SELECT * FROM tareas ORDER BY fecha_limite DESC");
$stm->execute();
$tareas = $stm->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Panel de Control</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .tarea-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .estado-selector {
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .estado-selector:hover {
            border-color: #007bff;
        }

        .estado-selector[value="pendiente"] {
            color: #dc3545;
        }

        .estado-selector[value="en_progreso"] {
            color: #ffc107;
        }

        .estado-selector[value="completada"] {
            color: #28a745;
        }

        .tarea-card.completada {
            opacity: 0.7;
            background: #f8f9fa;
            position: relative;
        }

        .tarea-card.completada::after {
            content: "✓";
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            color: #28a745;
        }
    </style>
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

            <a href="tareas.php" class="action-card">
                <h3>✅ Mis Tareas</h3>
                <p>Ver todas mis asignaciones</p>
            </a>
        </div>

        <!-- Resumen de Eventos -->
        <div class="dashboard-section">
            <h3>📌 Próximos Eventos</h3>
            <div class="eventos-list">
                <?php foreach ($eventosAgrupados as $evento): ?>
                    <div class="evento-card" style="border-left: 5px solid <?= $evento['color'] ?>">
                        <div class="evento-header">
                            <h4><?= htmlspecialchars($evento['titulo']) ?></h4>
                            <span class="fecha">
                                <?= date('d M H:i', strtotime($evento['fecha_inicio'])) ?>
                            </span>
                        </div>
                        <p><?= htmlspecialchars($evento['descripcion']) ?></p>
                        <?php if (!empty($evento['usuarios'])): ?>
                            <div class="asignados">
                                <strong>Participantes:</strong>
                                <?php foreach ($evento['usuarios'] as $usuario): ?>
                                    <span class="participante">
                                        <?= htmlspecialchars($usuario['nombre']) ?> (<?= $usuario['rol'] ?>)
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lista de Tareas Actualizada -->
        <div class="dashboard-section">
            <h3>📋 Tareas Pendientes</h3>
            <a href="crear_tarea.php" class="btn">➕ Nueva Tarea</a>
            <div class="tareas-list">
                <?php foreach ($tareas as $tarea): ?>
                    <div class="tarea-card <?= $tarea['prioridad'] ?> <?= $tarea['estado'] ?>">
                        <div class="tarea-header">
                            <h4><?= htmlspecialchars($tarea['descripcion']) ?></h4>
                            <div class="tarea-controls">
                                <select class="estado-selector" data-tarea-id="<?= $tarea['id'] ?>">
                                    <option value="pendiente" <?= $tarea['estado'] == 'pendiente' ? 'selected' : '' ?>>
                                        Pendiente</option>
                                    <option value="en_progreso" <?= $tarea['estado'] == 'en_progreso' ? 'selected' : '' ?>>En
                                        Progreso</option>
                                    <option value="completada" <?= $tarea['estado'] == 'completada' ? 'selected' : '' ?>>
                                        Completada</option>
                                </select>
                                <span class="prioridad"><?= $tarea['prioridad'] ?></span>
                            </div>
                        </div>
                        <div class="tarea-footer">
                            <span>Vence: <?= date('d M', strtotime($tarea['fecha_limite'])) ?></span>
                            <div class="acciones">
                                <a href="editar_tarea.php?id=<?= $tarea['id'] ?>" class="btn">Editar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    
</body>

</html>