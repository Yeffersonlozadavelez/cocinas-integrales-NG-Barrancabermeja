CREATE DATABASE IF NOT EXISTS cocinas_ng;
USE cocinas_ng;

CREATE TABLE IF NOT EXISTS cotizaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    servicio VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

-- Crea el primer administrador con una contrasena propia (ver README.md).

CREATE TABLE IF NOT EXISTS testimonios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    calificacion INT NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
    mensaje TEXT NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    estado ENUM('pendiente', 'aprobado') DEFAULT 'pendiente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SET @imagen_column_missing = (
    SELECT COUNT(*) = 0
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'testimonios'
      AND column_name = 'imagen'
);
SET @add_imagen_column = IF(
    @imagen_column_missing,
    'ALTER TABLE testimonios ADD COLUMN imagen VARCHAR(255) DEFAULT NULL',
    'SELECT 1'
);
PREPARE add_imagen_statement FROM @add_imagen_column;
EXECUTE add_imagen_statement;
DEALLOCATE PREPARE add_imagen_statement;
