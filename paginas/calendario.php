<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

// Obtener eventos y usuarios asignados desde la base de datos
$stmt = $pdo->prepare("
    SELECT e.id, e.titulo, e.descripcion, e.fecha_inicio, e.color, e.tipo, u.nombre AS asignado_nombre, eu.rol_asociado
    FROM eventos e
    LEFT JOIN eventos_usuarios eu ON e.id = eu.evento_id
    LEFT JOIN usuarios u ON eu.usuario_id = u.id
    ORDER BY e.fecha_inicio
");
$stmt->execute();
$rawEventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar eventos por ID
$eventosAgrupados = [];
foreach ($rawEventos as $evento) {
    if (!isset($eventosAgrupados[$evento['id']])) {
        $eventosAgrupados[$evento['id']] = [
            'id' => $evento['id'],
            'titulo' => $evento['titulo'],
            'descripcion' => $evento['descripcion'],
            'fecha_inicio' => $evento['fecha_inicio'],
            'color' => $evento['color'],
            'tipo' => $evento['tipo'],
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

// Convertir eventos agrupados a formato JSON para JS
$eventos_json = json_encode(array_values($eventosAgrupados));
?>

<!DOCTYPE html>
<html>

<head>
    <title>📅 Calendario de Retiros</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .modal-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #editEventBtn {
            background-color: #4CAF50;
            color: white;
        }

        #deleteEventBtn {
            background-color: #f44336;
            color: white;
        }

        .add-event-hint {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
            cursor: pointer;
        }

        .calendar-day {
            cursor: pointer;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }
    </style>
</head>

<body>
    <?php include '../components/header.php'; ?>

    <div class="calendar-container">
        <div class="calendar-header">
            <h2>🗓️ Calendario de Eventos</h2>
            <a href="crear_evento.php" class="btn">➕ Nuevo Evento</a>
        </div>

        <div class="month-navigation">
            <button onclick="changeMonth(-1)">⬅️ Anterior</button>
            <h3 id="monthYear"></h3>
            <button onclick="changeMonth(1)">Siguiente ➡️</button>
        </div>

        <div id="month-view" class="calendar-view">
            <div class="calendar-grid" id="calendar-grid">
                <!-- Los días se generan dinámicamente con JavaScript -->
            </div>
        </div>
    </div>

    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="eventTitle"></h3>
            <p id="eventDescription"></p>
            <p id="eventTime"></p>
            <p id="eventTipo"></p>
            <ul id="eventAssigned"></ul>
            <div class="modal-actions">
                <button id="editEventBtn">✏️ Editar</button>
                <button id="deleteEventBtn">🗑️ Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        const eventos = <?= $eventos_json ?>;
        let currentDate = new Date();
        let currentEventId = null;

        function updateCalendarHeader() {
            const monthNames = [
                "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
            ];
            document.getElementById('monthYear').textContent =
                `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
        }

        function generateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const firstDay = new Date(year, month, 1).getDay();
            const calendarGrid = document.getElementById('calendar-grid');

            calendarGrid.innerHTML = '';

            for (let i = 0; i < firstDay; i++) {
                calendarGrid.innerHTML += `<div class="calendar-day empty"></div>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const currentDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayEvents = eventos.filter(event => event.fecha_inicio.startsWith(currentDateStr));
                const hasEvents = dayEvents.length > 0;

                calendarGrid.innerHTML += `
                    <div class="calendar-day" onclick="${!hasEvents ? `location.href='crear_evento.php?fecha=${currentDateStr}'` : 'void(0)'}">
                        <div class="day-number">${day}</div>
                        ${hasEvents ? `
                            <div class="evento ${!dayEvents[0].usuarios.length ? 'evento-sin-asignar' : ''}" 
                                 style="background: ${dayEvents[0].color || '#cce5ff'}"
                                 onclick="showEventDetails(${dayEvents[0].id}); event.stopPropagation()">
                                ${dayEvents[0].titulo}
                            </div>
                        ` : '<div class="add-event-hint">➕</div>'}
                    </div>
                `;
            }
        }

        function changeMonth(offset) {
            currentDate.setMonth(currentDate.getMonth() + offset);
            updateCalendarHeader();
            generateCalendar();
        }

        function showEventDetails(eventId) {
            currentEventId = eventId;
            const event = eventos.find(e => e.id === eventId);

            if (!event) {
                console.error("Evento no encontrado");
                return;
            }

            const assignedUsers = (event.usuarios || []).map(u =>
                `<li>${u.nombre} ${u.rol ? `(${u.rol})` : ''}</li>`
            ).join('');

            document.getElementById('eventTitle').textContent = event.titulo || 'Sin título';
            document.getElementById('eventDescription').textContent = event.descripcion || 'Sin descripción';
            document.getElementById('eventTipo').textContent = event.tipo || 'Sin tipo';
            document.getElementById('eventAssigned').innerHTML = assignedUsers || '<li>👤 Sin asignar</li>';

            try {
                const eventDate = new Date(event.fecha_inicio);
                if (isNaN(eventDate.getTime())) {
                    throw new Error("Fecha inválida");
                }
                document.getElementById('eventTime').textContent = `📅 Fecha: ${eventDate.toLocaleDateString('es-ES', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}`;
            } catch (error) {
                console.error("Error al formatear la fecha:", error);
                document.getElementById('eventTime').textContent = '📅 Fecha no disponible';
            }

            // Configurar acciones de los botones
            document.getElementById('editEventBtn').onclick = () => {
                window.location.href = `editar_evento.php?id=${currentEventId}`;
            };

            document.getElementById('deleteEventBtn').onclick = async () => {
                if (confirm('¿Estás seguro de querer eliminar este evento?')) {
                    try {
                        const response = await fetch(`eliminar_evento.php?id=${currentEventId}`, {
                            method: 'POST'
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert('Error al eliminar el evento');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al conectar con el servidor');
                    }
                }
            };

            document.getElementById('eventModal').style.display = 'block';
        }

        // Cerrar modal al hacer clic en la X
        document.querySelector('.close').addEventListener('click', () => {
            document.getElementById('eventModal').style.display = 'none';
        });

        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = (event) => {
            const modal = document.getElementById('eventModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Inicializar calendario
        updateCalendarHeader();
        generateCalendar();
    </script>

    <?php include '../components/footer.php'; ?>
</body>

</html>