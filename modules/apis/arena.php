<?php
include_once '../../includes/db_connection.php';

header('Content-Type: application/json');

function listarArena($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $criterio = isset($_GET['criterio']) ? $_GET['criterio'] : NULL;

        $sql = "{CALL sp_listar_arena(?)}";
        $params = array($criterio);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo json_encode(['error' => 'Error en la consulta', 'details' => sqlsrv_errors()]);
            exit();
        }

        $cartas = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $cartas[] = $row;
        }

        if (count($cartas) > 0) {
            echo json_encode([
                'status' => 'success',
                'data' => $cartas
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encontraron arenas.'
            ]);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['error' => 'Método no permitido']);
    }
}

function crearNuevaArena($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'insert') {

        $input = json_decode(file_get_contents('php://input'), true);

        error_log(print_r($input, true));

        if (!isset($input['link']) || !isset($input['nombre']) || !isset($input['tipo']) || 
            !isset($input['rankingMin']) || !isset($input['rankingMax'])) {
            echo json_encode(['error' => 'Faltan datos: link, nombre, tipo, rankingMin o rankingMax']);
            exit();
        }

        $nombre = $input['nombre'];
        $fondoURL = $input['link']; 
        $tipo = $input['tipo'];
        $rankingMin = $input['rankingMin'];
        $rankingMax = $input['rankingMax'];

        error_log("Datos para insertar: $nombre, $fondoURL, $tipo, $rankingMin, $rankingMax");

        $sql = "{CALL sp_insertar_arena(?, ?, ?, ?, ?)}";
        $params = array($nombre, $fondoURL, $tipo, $rankingMin, $rankingMax);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode(['error' => 'Error en la consulta', 'details' => $errors]);
            exit();
        }

        $rowsAffected = sqlsrv_rows_affected($stmt);
        if ($rowsAffected === false || $rowsAffected === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar la Arena']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Arena registrada correctamente']);
        }

        sqlsrv_free_stmt($stmt);
    }
}

function determinarFuncionArena($conn) {
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'listar') {
            listarArena($conn);
        } elseif ($_GET['action'] === 'insert') {
            crearNuevaArena($conn);
        }
    } else {
        echo json_encode(['error' => 'No se especificó ninguna acción']);
    }
}

determinarFuncionArena($conn);

?>
