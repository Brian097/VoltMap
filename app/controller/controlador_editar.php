<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/conexion.php";

// Validar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    header("Location: ../view/login.php");
    exit();
}

$idUsuarioActual = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btneditar'])) {
    // Recoger y limpiar datos del formulario
    $idPunto             = intval($_POST['id_punto'] ?? 0);
    $latitud             = floatval($_POST['latitud'] ?? 0);
    $longitud            = floatval($_POST['longitud'] ?? 0);
    $direccion           = trim($_POST['direccion'] ?? '');
    $ciudadYDepartamento = trim($_POST['ciudadYDepartamento'] ?? '');
    $potencia            = floatval($_POST['potenciaKilowatts'] ?? 0);
    $tipoConector        = trim($_POST['tipoConector'] ?? '');
    $precioKwh           = !empty($_POST['precioKwh']) ? floatval($_POST['precioKwh']) : null;
    $precioHora          = !empty($_POST['precioHora']) ? floatval($_POST['precioHora']) : null;

    if ($idPunto <= 0) {
        header("Location: ../view/content/mis_cargadores.php?error=id_invalido");
        exit();
    }

    // Verificar estrictamente que el punto pertenezca al usuario actual por seguridad
    $stmtVerificar = $conexion->prepare("SELECT id FROM puntos_carga WHERE id = ? AND id_usuario = ?");
    $stmtVerificar->bind_param("ii", $idPunto, $idUsuarioActual);
    $stmtVerificar->execute();
    $resultadoVerificar = $stmtVerificar->get_result();

    if ($resultadoVerificar->num_rows === 0) {
        $stmtVerificar->close();
        header("Location: ../view/content/mis_cargadores.php?error=no_autorizado");
        exit();
    }
    $stmtVerificar->close();

    // Iniciar transacción para actualizar ambas tablas (puntos_carga y cargadores) de forma segura
    $conexion->begin_transaction();

    try {
        // 1. Actualizar el punto de carga (ubicación y dirección)
        $stmtPunto = $conexion->prepare("UPDATE puntos_carga SET latitud = ?, longitud = ?, direccion = ?, ciudadYDepartamento = ? WHERE id = ?");
        $stmtPunto->bind_param("ddssi", $latitud, $longitud, $direccion, $ciudadYDepartamento, $idPunto);
        $stmtPunto->execute();
        $stmtPunto->close();

        // 2. Actualizar los datos técnicos del cargador asociado
        $stmtCargador = $conexion->prepare("UPDATE cargadores SET potenciaKilowatts = ?, tipoConector = ?, precioKwh = ?, precioHora = ? WHERE idPuntoCarga = ?");
        $stmtCargador->bind_param("dsddi", $potencia, $tipoConector, $precioKwh, $precioHora, $idPunto);
        $stmtCargador->execute();
        $stmtCargador->close();

        // Confirmar transacción
        $conexion->commit();
        header("Location: ../view/content/mis_cargadores.php?exito=actualizado");
        exit();

    } catch (Exception $e) {
        // Revertir en caso de fallo
        $conexion->rollback();
        header("Location: ../view/content/editar_cargador.php?id=" . $idPunto . "&error=fallo_actualizacion");
        exit();
    }
} else {
    header("Location: ../view/content/mis_cargadores.php");
    exit();
}