<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';

// Obtener eventos desde la base de datos
$stmt = $pdo->prepare("
    SELECT eventos.*, usuarios.nombre AS asignado_nombre 
    FROM eventos 
    LEFT JOIN usuarios ON eventos.asignado_a = usuarios.id
");
$stmt->execute();
$eventos = $stmt->fetchAll();

// Convertir eventos a formato JSON para JS
$eventos_json = json_encode($eventos);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Calendario de Retiros</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .calendar-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        .calendar-day {
            border: 1px solid #ddd;
            min-height: 120px;
            padding: 10px;
            background: white;
        }
        .evento {
            padding: 5px;
            margin: 3px 0;
            border-radius: 4px;
            font-size: 0.9em;
            cursor: pointer;
        }
        .evento-sin-asignar {
            background: #ffcccc;
            border-left: 3px solid #dc3545;
        }
        .month-navigation {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .month-navigation button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 20px 0px 20px 0px;
        }
        .month-navigation button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include '../components/header.php'; ?>

    <div class="calendar-container">
        <div class="calendar-header">
            <h2>Calendario de Eventos</h2>
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
            <p id="eventAssigned"></p>
        </div>
    </div>

    <script>
        const eventos = <?= $eventos_json ?>;
        let currentDate = new Date();

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

            // Agregar celdas vacías al inicio
            for (let i = 0; i < firstDay; i++) {
                calendarGrid.innerHTML += `<div class="calendar-day empty"></div>`;
            }

            // Agregar los días del mes
            for (let day = 1; day <= daysInMonth; day++) {
                const currentDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayEvents = eventos.filter(event => event.fecha_inicio.startsWith(currentDateStr));

                calendarGrid.innerHTML += `
                    <div class="calendar-day">
                        <div class="day-number">${day}</div>
                        ${dayEvents.map(event => `
                            <div class="evento ${!event.asignado_a ? 'evento-sin-asignar' : ''}" 
                                 style="background: ${event.color || '#cce5ff'}"
                                 onclick="showEventDetails(${event.id})">
                                ${event.titulo}
                            </div>
                        `).join('')}
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
            const event = eventos.find(e => e.id === eventId);
            document.getElementById('eventTitle').textContent = event.titulo;
            document.getElementById('eventDescription').textContent = event.descripcion || 'Sin descripción';
            document.getElementById('eventTime').textContent = `Horario: ${new Date(event.fecha_inicio).toLocaleString()}`;
            document.getElementById('eventAssigned').textContent = `Asignado a: ${event.asignado_nombre || 'Sin asignar'}`;
            document.getElementById('eventModal').style.display = 'block';
        }

        document.querySelector('.close').addEventListener('click', () => {
            document.getElementById('eventModal').style.display = 'none';
        });

        // Inicializar
        updateCalendarHeader();
        generateCalendar();
    </script>

    <?php include '../components/footer.php'; ?>
</body>
</html>
