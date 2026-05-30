USE clinica_veterinaria;
CREATE TABLE vacuna (
    id_vacuna INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);
CREATE TABLE aplicacion_vacuna (
    id_aplicacion INT AUTO_INCREMENT PRIMARY KEY,
    fecha_aplicacion DATE NOT NULL,
    proxima_fecha_sugerida DATE,
    id_mascota INT NOT NULL,
    id_vacuna INT NOT NULL,
    id_veterinario INT NOT NULL,
    FOREIGN KEY (id_mascota) REFERENCES mascota(id_mascota) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_vacuna) REFERENCES vacuna(id_vacuna) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_veterinario) REFERENCES veterinario(id_veterinario) ON DELETE RESTRICT ON UPDATE CASCADE
);
CREATE TABLE medicamento (
    id_medicamento INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    presentacion VARCHAR(50) NOT NULL -- Ej: 'Tabletas', 'Jarabe', 'Suspensión inyectable'
);
CREATE TABLE tratamiento (
    id_tratamiento INT AUTO_INCREMENT PRIMARY KEY,
    id_consulta INT NOT NULL,
    descripcion TEXT NOT NULL,       -- Indicaciones generales del tratamiento
    duracion_dias INT NOT NULL,
    FOREIGN KEY (id_consulta) REFERENCES consulta(id_consulta) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE detalle_tratamiento (
    id_tratamiento INT NOT NULL,
    id_medicamento INT NOT NULL,
    dosis VARCHAR(100) NOT NULL,      -- Ej: '1/2 tableta' o '5 ml'
    frecuencia VARCHAR(100) NOT NULL, -- Ej: 'Cada 12 horas'
    PRIMARY KEY (id_tratamiento, id_medicamento),
    FOREIGN KEY (id_tratamiento) REFERENCES tratamiento(id_tratamiento) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_medicamento) REFERENCES medicamento(id_medicamento) ON DELETE RESTRICT ON UPDATE CASCADE
);