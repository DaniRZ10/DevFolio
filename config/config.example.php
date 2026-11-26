<?php
// archivo: config/config.example.php
// Copia este archivo como config/config.php y pon tus datos locales.
// config.php NO se sube al repositorio (está en .gitignore).
define('DB_HOST', 'localhost');
define('DB_NAME', 'devfolio');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);
