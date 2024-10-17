<?php

// Función para cargar las variables del .env 
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        //Saltar las lineas de los comentarios en .env
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
            putenv(sprintf('%s=%s', $key, $value));
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Cargar las variables del .env
loadEnv(__DIR__ . '/.env');


// Conectar a la base de datos utilizando las credenciales del .env
$connectionOptions = array(
    "Database" => getenv('DB_NAME'),
    "Uid" => getenv('DB_USERNAME'),
    "PWD" => getenv('DB_PASSWORD')
);

// Establecer conexión
$conn = sqlsrv_connect(getenv('DB_SERVER'), $connectionOptions);

// Verificar la conexión
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Función para obtener la conexión
function getConnection() {
    global $conn;
    return $conn;
}
