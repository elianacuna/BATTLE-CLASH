<?php
include_once '../../includes/db_connection.php';

header('Content-Type: application/json');

function iniciarSesion($conn){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['correo']) || !isset($input['contrasena'])) {
            echo json_encode(['error' => 'Faltan datos: correo y/o contraseña']);
            exit();
        }
    
        $correo = $input['correo'];
        $contrasena = $input['contrasena'];
    
        $sql = "{CALL sp_login(?, ?)}";
        $params = array($correo, $contrasena);
        $stmt = sqlsrv_query($conn, $sql, $params);
    
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    
        $userData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
        if ($userData) {
            echo json_encode([
                'status' => 'success',
                'data' => $userData
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Las credenciales no coinciden o usuario no encontrado.'
            ]);
        }
    
        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['error' => 'Método no permitido']);
    }
    

}

function crearJugadorusuario($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['nombre_usuario']) || !isset($input['correo']) || !isset($input['contrasena']) || !isset($input['rol'])) {
            echo json_encode(['error' => 'Faltan datos: nombre_usuario, correo, contraseña o rol']);
            exit();
        }

        $nombre_usuario = $input['nombre_usuario'];
        $correo = $input['correo'];
        $contrasena = $input['contrasena'];
        $rol = $input['rol'];

        $sql = "{CALL sp_registrar_jugador(?, ?, ?, ?)}";
        $params = array($nombre_usuario, $correo, $contrasena, $rol);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode(['error' => 'Error en la consulta', 'details' => $errors]);
            exit();
        }

        $rowsAffected = sqlsrv_rows_affected($stmt);
        if ($rowsAffected === false || $rowsAffected === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar el jugador']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Jugador registrado correctamente']);
        }

        sqlsrv_free_stmt($stmt);
    }
}

function listarTodosUsuarios($conn){
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $criterio = isset($_GET['criterio']) ? $_GET['criterio'] : NULL;
        $rol = isset($_GET['rol']) ? $_GET['rol'] : NULL;

        $sql = "{CALL sp_listar_usuario(?, ?)}";
        $params = array($criterio, $rol);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo json_encode(['error' => 'Error en la consulta', 'details' => sqlsrv_errors()]);
            exit();
        }

        $usuarios = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $usuarios[] = $row;
        }

        if (count($usuarios) > 0) {
            echo json_encode([
                'status' => 'success',
                'data' => $usuarios
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encontraron usuarios.'
            ]);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['error' => 'Método no permitido']);
    }
}

function obtenerUsuarioPorId($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $id = $_GET['id'];

        $sql = "SELECT ID, Username, rol FROM Usuario WHERE ID = ?";
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
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        }

        sqlsrv_free_stmt($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida o falta el parámetro ID']);
    }
}


function actualizarUsuarioRol($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'update') {

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['rol']) || !isset($input['id'])) {
            echo json_encode(['error' => 'Faltan datos: rol o id']);
            exit();
        }

        $rol = $input['rol'];
        $id = $input['id'];

        $sql = "{CALL sp_actualizar_usuario(?, ?)}";
        $params = array($rol, $id);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode(['error' => 'Error en la consulta', 'details' => $errors]);
            exit();
        }

        $rowsAffected = sqlsrv_rows_affected($stmt);
        if ($rowsAffected === false || $rowsAffected === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el usuario']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente']);
        }

        sqlsrv_free_stmt($stmt);
    }
}

// Determinar la acción
function determinarLoginRegister($conn){
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'login') {
            iniciarSesion($conn);
        } elseif ($_GET['action'] === 'register') {
            crearJugadorusuario($conn);
        } elseif ($_GET['action'] == 'listar') {
            listarTodosUsuarios($conn);
        } elseif ($_GET['action'] == 'update') {
            actualizarUsuarioRol($conn);
        } elseif ($_GET['action'] == 'getUser') {
            obtenerUsuarioPorId($conn);
        } else {
            echo json_encode(['error' => 'Acción no permitida']);
        }
    } else {
        echo json_encode(['error' => 'No se especificó ninguna acción']);
    }    
}

determinarLoginRegister($conn)
?>