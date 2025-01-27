
# Aplicación Organizadora de Retiros

## Descripción
La **Aplicación Organizadora de Retiros** es una herramienta diseñada para facilitar la planificación, gestión y ejecución de retiros. Con funcionalidades que abarcan desde la organización de eventos hasta la asignación de tareas, esta aplicación ayuda a los organizadores a reducir la carga logística y optimizar la experiencia para los asistentes.

## Estructura del Proyecto

```plaintext
├── proyecto_retiros/  
│   ├── assets/  
│   │   ├── css/  
│   │   │   └── styles.css          # Estilos globales  
│   │   ├── js/  
│   │   │   └── scripts.js          # Funcionalidades JS (ej: calendario interactivo)  
│   │   └── img/                    # Imágenes del proyecto (logos, íconos)  
│   │  
│   ├── components/                 # Componentes reutilizables  
│   │   ├── header.php              # Encabezado común (navbar, logo)  
│   │   ├── footer.php              # Pie de página  
│   │   ├── alerts.php              # Mensajes de error/éxito  
│   │   └── sidebar.php             # Menú lateral (si aplica)  
│   │  
│   ├── includes/                   # Lógica y conexiones  
│   │   ├── config.php              # Configuración de la base de datos  
│   │   ├── db_connect.php          # Conexión PDO a MySQL  
│   │   ├── auth.php                # Validación de sesiones  
│   │   └── functions.php           # Funciones auxiliares (ej: sanitizar inputs)  
│   │  
│   ├── database/                   # Scripts SQL y backups  
│   │   └── schema.sql              # Estructura inicial de la base de datos  
│   │  
│   ├── paginas/                    # Páginas principales  
│   │   ├── login.php               # Inicio de sesión  
│   │   ├── registro.php            # Registro de nuevos usuarios  
│   │   ├── panel_control.php       # Dashboard principal  
│   │   ├── calendario.php          # Vista de calendario  
│   │   ├── gestion_usuarios.php    # CRUD de usuarios (solo admin)  
│   │   ├── gestion_tareas.php      # Lista de tareas asignadas  
│   │   └── perfil.php              # Edición de perfil de usuario  
│   │  
│   └── index.php                   # Redirige a login.php o panel_control.php  
```

## Características Principales

1. **Gestión de Calendario y Eventos**
   - Vistas personalizables (día, semana, mes).
   - Clasificación por colores y duplicación de eventos.
   - Eventos grupales y privados.

2. **Gestión de Usuarios y Roles**
   - Perfiles con roles personalizados.
   - Acceso limitado según el rol asignado.

3. **Gestión de Asistentes**
   - Perfiles con información médica y preferencias.
   - Comunicación automatizada (WhatsApp y correo).

4. **Gestión de Tareas**
   - Tareas personalizadas con prioridad y estado.
   - Notificaciones de vencimiento.

5. **Notificaciones y Alertas**
   - Recordatorios automáticos y configurables.

6. **Integraciones con Herramientas de Terceros**
   - Sincronización con Google Calendar.
   - Comunicación vía WhatsApp y correo.

7. **Modo de Preparación de Retiro**
   - Listas de verificación, alertas y listas de compras previas.

8. **Gestión de Múltiples Retiros**
   - Panel centralizado y asignación multi-retiro.

## Requisitos del Sistema

### Tecnologías Recomendadas
- **Frontend:** HTML, CSS, JavaScript.
- **Backend:** PHP 8+.
- **Base de Datos:** MySQL.
- **Servidor:** XAMPP
- **Integraciones:** Google Calendar API, Twilio/WhatsApp API.

### Instalación
1. Clona este repositorio:
   ```bash
   git clone https://github.com/usuario/proyecto_retiros.git
   ```
2. Configura el archivo `includes/config.php` con las credenciales de tu base de datos.
3. Importa el archivo `database/schema.sql` en tu servidor MySQL.
4. Asegúrate de que las dependencias están instaladas y configuradas (PHP, servidor web, etc.).

### Uso
1. Abre la aplicación en tu navegador accediendo a `http://localhost/proyecto_retiros`.
2. Registra un usuario administrador y accede al panel de control.
3. Configura los roles, eventos y usuarios según tus necesidades.


## Licencia
Este proyecto está bajo la Licencia MIT. Consulta el archivo `LICENSE` para más información.

---

¡Gracias por contribuir al éxito de los retiros!
```