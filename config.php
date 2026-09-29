<?php
// ============================================================
//  Configuracion central del sistema
//  Lee las variables de conexion desde el archivo .env
//  (nunca se guardan credenciales dentro del codigo)
// ============================================================

$envFile = __DIR__ . '/.env';

if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);

        if ($linea === '' || $linea[0] === '#') {
            continue;
        }

        $pos = strpos($linea, '=');
        if ($pos === false) {
            continue;
        }

        $clave = trim(substr($linea, 0, $pos));
        $valor = trim(trim(substr($linea, $pos + 1)), "\"'");

        if ($clave === '' || getenv($clave) !== false) {
            continue;
        }

        putenv("$clave=$valor");
        $_ENV[$clave] = $valor;
    }
}

define('DB_HOST', getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost');
define('DB_NAME', getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'osman_db');
define('DB_USER', getenv('DB_USER') !== false ? getenv('DB_USER') : 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

?>
