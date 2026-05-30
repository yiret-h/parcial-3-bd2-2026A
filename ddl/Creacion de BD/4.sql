USE clinica_veterinaria;
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Aquí guardaremos la clave encriptada, NUNCA en texto plano
    rol ENUM('Administrador', 'Veterinario', 'Recepcionista') NOT NULL,
    id_veterinario INT NULL, -- Puede ser nulo porque un Recepcionista no es un Veterinario
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    FOREIGN KEY (id_veterinario) REFERENCES veterinario(id_veterinario) ON DELETE SET NULL ON UPDATE CASCADE
);