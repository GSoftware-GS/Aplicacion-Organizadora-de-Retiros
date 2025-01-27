-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS retiros_db;
USE retiros_db;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol ENUM('admin', 'conductor', 'cocinero', 'asistente') DEFAULT 'asistente',
    password_hash VARCHAR(255) NOT NULL
);

-- Tabla de eventos
CREATE TABLE eventos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME,
    color VARCHAR(20) DEFAULT '#007bff',
    tipo ENUM('grupal', 'privado') DEFAULT 'grupal',
    asignado_a,
    FOREIGN KEY (asignado_a) REFERENCES usuarios(id)
);

-- Tabla de tareas
CREATE TABLE tareas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    descripcion TEXT NOT NULL,
    prioridad ENUM('alta', 'media', 'baja') DEFAULT 'media',
    estado ENUM('pendiente', 'en_progreso', 'completada') DEFAULT 'pendiente',
    fecha_limite DATE,
    asignado_a INT NOT NULL,
    FOREIGN KEY (asignado_a) REFERENCES usuarios(id)
);

-- Insertar usuario admin por defecto (contraseña: Admin1234)
INSERT INTO usuarios (nombre, email, rol, password_hash)
VALUES (
    'Administrador', 
    'admin@retiros.com', 
    'admin', 
    -- Contraseña hasheada con SHA-256 (solo para ejemplo; en producción usar password_hash() de PHP)
    SHA2('Admin1234', 256)
);