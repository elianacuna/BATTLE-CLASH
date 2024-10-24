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

function actualizarArena($conn){
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'update') {
        
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id']) || !isset($input['foto']) || !isset($input['nombre']) || !isset($input['tipo']) || !isset($input['rankingMin']) || !isset($input['rankingMax'])) {
            echo json_encode(['error' => 'Faltan datos: id, foto, nombre, tipo, poderAtaque o poderDefensa']);
            exit();
        }

        $id = $input['id'];
        $foto = $input['foto'];
        $nombre = $input['nombre'];
        $tipo = $input['tipo'];
        $rankingMin = $input['rankingMin'];
        $rankingMax = $input['rankingMax'];

        $sql = "{CALL sp_actualizar_arena(?, ?, ?, ?, ?, ?)}";
        $params = array($id, $foto, $nombre, $tipo, $rankingMin, $rankingMax);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode(['error' => 'Error en la consulta', 'details' => $errors]);
            exit();
        }

        $rowsAffected = sqlsrv_rows_affected($stmt);
        if ($rowsAffected === false || $rowsAffected === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la Arena']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Carta actualizada correctamente']);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida']);
    }
}

function  actualizarInfoArenaSoloDatos($conn){
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'updateData') {
        
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id']) || !isset($input['nombre']) || !isset($input['tipo']) || !isset($input['rankingMin']) || !isset($input['rankingMax'])) {
            echo json_encode(['error' => 'Faltan datos: id, nombre, tipo, poderAtaque o poderDefensa']);
            exit();
        }

        $id = $input['id'];
        $nombre = $input['nombre'];
        $tipo = $input['tipo'];
        $rankingMin = $input['rankingMin'];
        $rankingMax = $input['RankingMax'];

        $sql = "{CALL sp_actualizar_arena_sin_foto(?, ?, ?, ?, ?)}";
        $params = array($id, $nombre, $tipo, $rankingMin, $rankingMax);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode(['error' => 'Error en la consulta', 'details' => $errors]);
            exit();
        }

        $rowsAffected = sqlsrv_rows_affected($stmt);
        if ($rowsAffected === false || $rowsAffected === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la carta']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Carta actualizada correctamente']);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida']);
    }   
}

function obtenerArenaPorId($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $id = $_GET['id'];

        if (!is_numeric($id)) {
            echo json_encode(['error' => 'El ID debe ser un número']);
            exit();
        }

        $sql = "exec sp_leer_arena @id = ? ";
        $params = array($id);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo json_encode(['error' => 'Error en la consulta', 'details' => sqlsrv_errors()]);
            exit();
        }

        $userData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($userData) {
            echo json_encode(['status' => 'success', 'data' => $userData]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Arena no encontrado']);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida o falta el parámetro ID']);
    }
}

function determinarFuncionArena($conn) {
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'listar') {
            listarArena($conn);
        } 
        elseif ($_GET['action'] === 'insert') {
            crearNuevaArena($conn);
        }
        elseif ($_GET['action'] === 'update') {
            actualizarArena($conn);
        }
        elseif ($_GET['action'] === 'updateData'){
            actualizarInfoArenaSoloDatos($conn);
        }
        elseif ($_GET['action'] === 'obtenerArenaPorId'){
            obtenerArenaPorId($conn);
        }
    } else {
        echo json_encode(['error' => 'No se especificó ninguna acción']);
    }
}



determinarFuncionArena($conn);

?>
