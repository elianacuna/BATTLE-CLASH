<?php

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {

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

loadEnv(__DIR__ . '/.env');


$connectionOptions = array(
    "Database" => getenv('DB_NAME'),
    "Uid" => getenv('DB_USERNAME'),
    "PWD" => getenv('DB_PASSWORD')
);

$conn = sqlsrv_connect(getenv('DB_SERVER'), $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

function getConnection() {
    global $conn;
    return $conn;
}

/*
# Credenciales de la conexióon  a la DB

DB_SERVER=EACUNAP\SQLEXPRESS
DB_USERNAME=sa
DB_PASSWORD=Holaque2
DB_NAME=BattleClash

# Configuración del juego
APP_NAME=BattleClash
APP_VERSION=1.0.0 #Versión

#BattleClash.mssql.somee.com
#eacunap_SQLLogin_1
#659l1ty3uv
#BattleClash
*/