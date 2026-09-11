<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../view/inc/auth.php";
require_once __DIR__ . "/../view/inc/lang.php";
require_once __DIR__ . "/../model/conexion.php";

$idUsuarioActual = $_SESSION['id'] ?? 0;
$mensaje = "";
$tipoAlerta = "";

// Lógica para eliminar asegurando propiedad
if (isset($_GET['eliminar'])) {
    $idPunto = intval($_GET['eliminar']);
    
    $conexion->begin_transaction();
    try {
        $stmtV = $conexion->prepare("SELECT id FROM puntos_carga WHERE id = ? AND id_usuario = ?");
        $stmtV->bind_param("ii", $idPunto, $idUsuarioActual);
        $stmtV->execute();
        
        if ($stmtV->get_result()->num_rows > 0) {
            $stmtV->close();

            $stmtC = $conexion->prepare("DELETE FROM cargadores WHERE idPuntoCarga = ?");
            $stmtC->bind_param("i", $idPunto);
            $stmtC->execute();
            $stmtC->close();

            $stmtP = $conexion->prepare("DELETE FROM puntos_carga WHERE id = ?");
            $stmtP->bind_param("i", $idPunto);
            $stmtP->execute();
            $stmtP->close();

            $conexion->commit();
            $mensaje = $lang['cargador_eliminado_exito'] ?? 'Cargador eliminado correctamente.';
            $tipoAlerta = "success";
        } else {
            $conexion->rollback();
            $mensaje = "No tienes permisos para eliminar este cargador.";
            $tipoAlerta = "danger";
        }
    } catch (Exception $e) {
        $conexion->rollback();
        $mensaje = "Error al eliminar el cargador.";
        $tipoAlerta = "danger";
    }
}

// Obtener la lista de cargadores del usuario
$sql = "SELECT p.id as idPunto, p.direccion, p.ciudadYDepartamento, c.potenciaKilowatts, c.tipoConector, p.id_usuario 
        FROM puntos_carga p 
        INNER JOIN cargadores c ON p.id = c.idPuntoCarga 
        WHERE p.id_usuario = ?";
        
$stmtLista = $conexion->prepare($sql);
$stmtLista->bind_param("i", $idUsuarioActual);
$stmtLista->execute();
$resultado = $stmtLista->get_result();

// Nota: El controlador incluye la vista al finalizar para pasarle las variables $resultado, $mensaje, etc.
require_once __DIR__ . "/../view/dashboard/mis_cargadores.php";