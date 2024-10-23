<?php
include_once '../../includes/db_connection.php';

header('Content-Type: application/json');


function buscarUsuarioID($conn){
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['correo'])) {
        $email = $_GET['correo'];

    if (!!isset($input['correo'])) {
        echo json_encode(['error' => 'El correo no debe de ser null']);
        exit();
    }
    

        $sql = "exec sp_buscar_usuario @correo = ? ";
        $params = array($email);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo json_encode(['error' => 'Error en la consulta', 'details' => sqlsrv_errors()]);
            exit();
        }

        $userData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($userData) {
            echo json_encode(['status' => 'success', 'data' => $userData]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Carta no encontrado']);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida o falta el parámetro correo']);
    }
}


function actualizarUsuarioID($conn){
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isset($_GET['contrasena'])) {
        $id = $_GET['id'];
        $contrasena = $_GET['contrasena'];

        if (!is_numeric($id)) {
            echo json_encode(['error' => 'El ID debe ser un número']);
            exit();
        }

        if (empty($contrasena)) {
            echo json_encode(['error' => 'La contraseña no debe estar vacía']);
            exit();
        }

        $sql = "exec sp_cambiar_contrasena @contrasena = ?, @id = ?";
        $params = array($contrasena, $id);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo json_encode(['error' => 'Error en la consulta', 'details' => sqlsrv_errors()]);
            exit();
        }

        if (sqlsrv_rows_affected($stmt) > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Contraseña actualizada correctamente']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado o contraseña no actualizada']);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos: id o contraseña']);
    }
}


function determinarFuncionCarta($conn){
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'searchUser') {
            buscarUsuarioID($conn);
        } elseif ($_GET['action'] === 'updatePassword') {
            actualizarUsuarioID($conn);

        }
    } else {
        echo json_encode(['error' => 'No se especificó ninguna acción']);
    }
}

determinarFuncionCarta($conn);
?>
