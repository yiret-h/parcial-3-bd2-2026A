CREATE DATABASE IF NOT EXISTS clinica_veterinaria;
USE clinica_veterinaria;
CREATE TABLE especie (
    id_especie INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);
CREATE TABLE raza (
    id_raza INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    id_especie INT NOT NULL,
    FOREIGN KEY (id_especie) REFERENCES especie(id_especie) ON DELETE RESTRICT ON UPDATE CASCADE
);
CREATE TABLE dueño (
    id_dueno INT AUTO_INCREMENT PRIMARY KEY,
    documento_identidad VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    direccion VARCHAR(150)
);
CREATE TABLE veterinario (
    id_veterinario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50),
    tarjeta_profesional VARCHAR(50) UNIQUE -- Ideal para darle realismo al proyecto
);
CREATE TABLE mascota (
    id_mascota INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE,
    sexo ENUM('Macho', 'Hembra') NOT NULL,
    id_dueño INT NOT NULL,
    id_raza INT NOT NULL,
    FOREIGN KEY (id_dueño) REFERENCES dueño(id_dueño) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_raza) REFERENCES raza(id_raza) ON DELETE RESTRICT ON UPDATE CASCADE
);
CREATE TABLE cita (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL,
    motivo_previo VARCHAR(255),
    estado ENUM('Pendiente', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
    id_mascota INT NOT NULL,
    id_veterinario INT NOT NULL,
    FOREIGN KEY (id_mascota) REFERENCES mascota(id_mascota) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_veterinario) REFERENCES veterinario(id_veterinario) ON DELETE RESTRICT ON UPDATE CASCADE
);
CREATE TABLE consulta (
    id_consulta INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    diagnostico TEXT NOT NULL,
    observaciones TEXT,
    id_mascota INT NOT NULL,
    id_veterinario INT NOT NULL,
    FOREIGN KEY (id_mascota) REFERENCES mascota(id_mascota) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_veterinario) REFERENCES veterinario(id_veterinario) ON DELETE RESTRICT ON UPDATE CASCADE
);
CREATE TABLE fotografia_mascota (
    id_fotografia INT AUTO_INCREMENT PRIMARY KEY,
    ruta_archivo VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_mascota INT NOT NULL,
    FOREIGN KEY (id_mascota) REFERENCES mascota(id_mascota) ON DELETE CASCADE ON UPDATE CASCADE
);






