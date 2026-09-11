<?php
require_once __DIR__ . '/../model/conexion.php';

$mensaje = "";
$tipoAlerta = "";

// Obtenemos el ID correcto desde la sesión
$idUsuarioActual = $_SESSION['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnpublicar'])) {
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudadYDepartamento = trim($_POST['ciudadYDepartamento'] ?? '');
    $latitud = trim($_POST['latitud'] ?? '');
    $longitud = trim($_POST['longitud'] ?? '');
    $potencia = trim($_POST['potenciaKilowatts'] ?? 0);
    $tipoConector = trim($_POST['tipoConector'] ?? '');
    $tipoCargador = trim($_POST['tipoCargador'] ?? '');
    $precioKwh = trim($_POST['precioKwh'] ?? 0);
    $precioHora = trim($_POST['precioHora'] ?? 0);

    if (empty($direccion) || empty($ciudadYDepartamento) || empty($latitud) || empty($longitud) || empty($tipoConector)) {
        $mensaje = "Todos los campos obligatorios deben ser completados.";
        $tipoAlerta = "danger";
    } else {
        $conexion->begin_transaction();
        try {
            // 1. Insertar el punto de carga con el ID de usuario real
            $visible = 1;
            $tipoUsuario = "Particular";
            $stmtPunto = $conexion->prepare("INSERT INTO puntos_carga (id_usuario, visible, tipoUsuario, latitud, longitud, direccion, ciudadYDepartamento) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtPunto->bind_param("iisddss", $idUsuarioActual, $visible, $tipoUsuario, $latitud, $longitud, $direccion, $ciudadYDepartamento);
            $stmtPunto->execute();
            $idPuntoCarga = $conexion->insert_id;
            $stmtPunto->close();

            // 2. Insertar el cargador asociado
            $cId = "usr-{$idPuntoCarga}-0";
            $estadoUso = "Disponible";
            $estadoOperativo = "Operativo";

            $stmtCargador = $conexion->prepare("INSERT INTO cargadores (id, idPuntoCarga, potenciaKilowatts, tipoConector, tipoCargador, estadoUso, estadoOperativo, precioKwh, precioHora) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtCargador->bind_param("sisdsssdd", $cId, $idPuntoCarga, $potencia, $tipoConector, $tipoCargador, $estadoUso, $estadoOperativo, $precioKwh, $precioHora);
            $stmtCargador->execute();
            $stmtCargador->close();

            $conexion->commit();
            $mensaje = $lang['punto_publicado_exito'] ?? 'Punto de carga publicado exitosamente.';
            $tipoAlerta = "success";
        } catch (Exception $e) {
            $conexion->rollback();
            $mensaje = ($lang['error_publicar'] ?? 'Hubo un error al publicar el cargador.');
            $tipoAlerta = "danger";
        }
    }
}