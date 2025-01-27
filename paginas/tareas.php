<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';


$usuario_id = $_SESSION['usuario_id'];
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

include '../components/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="header-container">
            <h1 class="page-title">📋 Listado de Tareas</h1>
            <a href="crear_tarea.php" class="btn btn-primary">
                ➕ Nueva Tarea
            </a>
        </div>

        <div class="table-responsive">
            <table class="task-table">
                <thead>
                    <tr>
                        <th>📝 Tarea</th>
                        <th>📅 Fecha Límite</th>
                        <th>🟢 Estado</th>
                        <th>⚙️ Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $tarea): ?>
                    <tr>
                        <td>
                            <div class="task-info">
                                <strong><?= htmlspecialchars($tarea['descripcion']) ?></strong>
                                <?php if($tarea['prioridad']): ?>
                                    <span class="badge priority-<?= $tarea['prioridad'] ?>">
                                        <?= ucfirst($tarea['prioridad']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td>
                            <?= date('d M Y', strtotime($tarea['fecha_limite'])) ?>
                            <?php if(date('Y-m-d') == $tarea['fecha_limite']): ?>
                                <span class="badge urgent">Hoy</span>
                            <?php elseif(date('Y-m-d', strtotime('+1 day')) == $tarea['fecha_limite']): ?>
                                <span class="badge warning">Mañana</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <span class="status-badge status-<?= $tarea['estado'] ?>">
                                <?= ucfirst($tarea['estado']) ?>
                            </span>
                        </td>
                        
                        <td>
                            <div class="action-buttons">
                                <a href="editar_tarea.php?id=<?= $tarea['id'] ?>" 
                                   class="btn btn-sm btn-edit" 
                                   title="Editar tarea">
                                    ✏️
                                </a>
                                
                                <?php if($tarea['estado'] != 'completada'): ?>
                                <a href="../includes/marcar_completada.php?id=<?= $tarea['id'] ?>" 
                                   class="btn btn-sm btn-success"
                                   title="Marcar como completada">
                                    ✅
                                </a>
                                <?php endif; ?>
                                
                                <a href="../includes/eliminar_tarea.php?id=<?= $tarea['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Eliminar esta tarea permanentemente?')"
                                   title="Eliminar tarea">
                                    🗑️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if(empty($tareas)): ?>
            <div class="empty-state">
                <p>🎉 ¡No hay tareas pendientes!</p>
                <small>Crea tu primera tarea usando el botón superior</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
</body>
</html>