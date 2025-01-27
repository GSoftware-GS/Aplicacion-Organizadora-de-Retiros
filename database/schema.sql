DROP DATABASE IF EXISTS retiros_db;

-- Crear la base de datos
CREATE DATABASE retiros_db;
USE retiros_db;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol ENUM('admin', 'conductor', 'cocinero', 'asistente', 'trabajador', 'profesor', 'socio') DEFAULT 'trabajador',
    password_hash VARCHAR(255) NOT NULL
);

-- Tabla de eventos
CREATE TABLE eventos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME,
    tipo ENUM('vuelo', 'clase_yoga', 'cena', 'reunion', 'otro') DEFAULT 'otro',
    color VARCHAR(20) DEFAULT '#007bff'
);

-- Tabla para asignar usuarios individuales a eventos
CREATE TABLE eventos_usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    rol_asociado ENUM('conductor', 'profesor', 'socio', 'participante', 'organizador') NOT NULL,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
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
    '$2y$10$HI2RLSMnYg/A22wn6ivfmeh3hRuk/5XdoQkYRPA/Kiq/ZkZpnuXia'
);
