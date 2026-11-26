-- archivo: docs/sql/schema.sql
-- Base de datos de DevFolio.
-- Importar en phpMyAdmin sobre una base de datos vacía llamada devfolio (cotejamiento utf8mb4_unicode_ci).
-- Contraseña inicial de todos los usuarios: Prueba123 (guardada con password_hash).

-- ---------------------------------------------------------------
-- Tablas
-- ---------------------------------------------------------------

-- Usuarios registrados (cada uno tiene su perfil y su portfolio)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    bio TEXT NULL,
    avatar VARCHAR(255) NULL,
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Proyectos (entidad principal). Cada proyecto pertenece a un usuario.
-- visibilidad: 'publico' lo ve cualquiera; 'privado' solo el usuario que lo subió.
CREATE TABLE proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    url_repo VARCHAR(255) NULL,
    portada VARCHAR(255) NULL,
    visibilidad ENUM('publico', 'privado') NOT NULL DEFAULT 'publico',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo fijo de etiquetas
CREATE TABLE etiquetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relación N:M entre proyectos y etiquetas
CREATE TABLE proyecto_etiqueta (
    proyecto_id INT NOT NULL,
    etiqueta_id INT NOT NULL,
    PRIMARY KEY (proyecto_id, etiqueta_id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- Datos iniciales
-- ---------------------------------------------------------------

INSERT INTO usuarios (id, nombre, email, password_hash, bio) VALUES
(1, 'Lucía Martín', 'lucia@devfolio.test', '$2y$10$7H/9kjC9geXNuDkytgR8Kug87Ic6IoI/xtIv/bvHqk.rFF0W32kqS', 'Estudiante de 2º DAW. Me gusta el desarrollo en servidor con PHP.'),
(2, 'Carlos Ruiz', 'carlos@devfolio.test', '$2y$10$7H/9kjC9geXNuDkytgR8Kug87Ic6IoI/xtIv/bvHqk.rFF0W32kqS', 'Estudiante de 2º DAW. Programo en Java y hago videojuegos 2D.'),
(3, 'Marta Gómez', 'marta@devfolio.test', '$2y$10$7H/9kjC9geXNuDkytgR8Kug87Ic6IoI/xtIv/bvHqk.rFF0W32kqS', 'Estudiante de 2º DAW. Me interesan el diseño y el frontend.'),
(4, 'Daniel Ríos', 'daniel@devfolio.test', '$2y$10$7H/9kjC9geXNuDkytgR8Kug87Ic6IoI/xtIv/bvHqk.rFF0W32kqS', 'Estudiante de 2º DAW.'),
(5, 'Irene Pascual', 'irene@devfolio.test', '$2y$10$7H/9kjC9geXNuDkytgR8Kug87Ic6IoI/xtIv/bvHqk.rFF0W32kqS', 'Estudiante de 2º DAW.'),
(6, 'Juan Carlos Macías', 'juancarlos@devfolio.test', '$2y$10$7H/9kjC9geXNuDkytgR8Kug87Ic6IoI/xtIv/bvHqk.rFF0W32kqS', 'Estudiante de 2º DAW.');

INSERT INTO etiquetas (id, nombre) VALUES
(1, 'PHP'),
(2, 'JavaScript'),
(3, 'Java'),
(4, 'HTML y CSS'),
(5, 'Bases de datos'),
(6, 'Diseño'),
(7, 'Videojuegos');

INSERT INTO proyectos (id, usuario_id, titulo, descripcion, url_repo, visibilidad) VALUES
(1, 1, 'Gestor de tareas en PHP', 'Aplicación para crear, editar y completar tareas, con PHP y MariaDB.', 'https://example.com/lucia/gestor-tareas', 'publico'),
(2, 1, 'Landing de un acuario', 'Página de presentación de un acuario ficticio maquetada con HTML, CSS y Bootstrap.', 'https://example.com/lucia/landing-acuario', 'publico'),
(3, 1, 'Apuntes de Java', 'Ejercicios resueltos de Java para repasar antes del examen.', NULL, 'privado'),
(4, 2, 'Tres en raya en Java', 'Juego de tres en raya por consola para dos jugadores.', 'https://example.com/carlos/tres-en-raya', 'publico'),
(5, 2, 'Juego de plataformas 2D', 'Prototipo de juego de plataformas con un escenario de scroll lateral.', 'https://example.com/carlos/plataformas-2d', 'publico'),
(6, 2, 'Bot para un grupo de amigos', 'Bot que avisa de los cumpleaños del grupo. Todavía en pruebas.', NULL, 'privado'),
(7, 3, 'Calculadora de presupuestos', 'Calculadora en JavaScript que suma partidas y aplica descuentos.', 'https://example.com/marta/calculadora', 'publico'),
(8, 3, 'Tienda online con PDO', 'Catálogo de productos y carrito con PHP y PDO. Sin terminar.', NULL, 'privado');

INSERT INTO proyecto_etiqueta (proyecto_id, etiqueta_id) VALUES
(1, 1), (1, 5),
(2, 4), (2, 6),
(3, 3),
(4, 3),
(5, 7),
(6, 2),
(7, 2), (7, 4),
(8, 1), (8, 5);
