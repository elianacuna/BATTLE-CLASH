<?php
include_once '../../includes/db_connection.php';

header('Content-Type: application/json');


function listarCartas($conn){
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $criterio = isset($_GET['criterio']) ? $_GET['criterio'] : NULL;

        $sql = "{CALL sp_listar_carta(?)}";
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
                'message' => 'No se encontraron cartas.'
            ]);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['error' => 'Método no permitido']);
    }
}

function crearNuevaCarta($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'insert') {

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['link']) || !isset($input['nombre']) || !isset($input['tipo']) || !isset($input['poderAtaque']) || !isset($input['poderDefensa'])) {
            echo json_encode(['error' => 'Faltan datos: link, nombre, tipo, poderAtaque o poderDefensa']);
            exit();
        }

        $link = $input['link'];
        $nombre = $input['nombre'];
        $tipo = $input['tipo'];
        $poderAtaque = $input['poderAtaque'];
        $poderDefensa = $input['poderDefensa'];

        $sql = "{CALL sp_insertar_carta(?, ?, ?, ?, ?)}";
        $params = array($link, $nombre, $tipo, $poderAtaque, $poderDefensa);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode(['error' => 'Error en la consulta', 'details' => $errors]);
            exit();
        }

        $rowsAffected = sqlsrv_rows_affected($stmt);
        if ($rowsAffected === false || $rowsAffected === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar la Carta']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Carta registrada correctamente']);
        }

        sqlsrv_free_stmt($stmt);
    }
}


function determinarFuncionCarta($conn){
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'listar') {
            listarCartas($conn);
        } elseif ($_GET['action'] === 'insert') {
            crearNuevaCarta($conn);
        }
    } else {
        echo json_encode(['error' => 'No se especificó ninguna acción']);
    }   
}

determinarFuncionCarta($conn);
?>

