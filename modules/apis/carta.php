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

function determinarFuncionCarta($conn){
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'listar') {
            listarCartas($conn);
        } 
    } else {
        echo json_encode(['error' => 'No se especificó ninguna acción']);
    }   
}

determinarFuncionCarta($conn);
?>

