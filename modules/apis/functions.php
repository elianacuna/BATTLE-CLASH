<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'checkLogin') {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            echo json_encode(['status' => 'logged_in']);
        } else {
            echo json_encode(['status' => 'not_logged_in']);
        }
    } elseif ($_GET['action'] === 'logout') {
        cerrarSesion();
    } else {
        echo json_encode(['error' => 'Acción no permitida']);
    }
    exit();
}

function cerrarSesion() {
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'Sesión cerrada']);
    error_log('Sesión cerrada correctamente.'); // Esto solo sirve para logs, no se envía al cliente
}
?>
