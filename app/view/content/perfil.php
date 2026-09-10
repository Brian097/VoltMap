<?php
    require_once __DIR__ . "/../../view/inc/auth.php"; // Archivo que protege la sesión privada
    require_once __DIR__ . "/../../view/inc/lang.php"; 
    /** @var array $lang */
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['mi_perfil'] ?? 'Mi Perfil'; ?> - VoltMap</title>
    <link rel="stylesheet" href="../../view/css/estilosLogin.css">
    <link rel="stylesheet" href="../../view/css/estilosPerfil.css">
</head>
<body>
    <div class="sc on" id="s-perfil">
        <div class="perfil-container">
            
            <!-- Botón de Volver al Mapa -->
            <div style="margin-bottom: 15px;">
                <a href="mapa.php" class="btn-volver">
                    ← <?php echo $lang['volver_mapa'] ?? 'Volver al Mapa'; ?>
                </a>
            </div>

            <div class="perfil-header-info">
                <h1><?php echo $lang['mi_perfil'] ?? 'Mi Perfil'; ?></h1>
                <p><?php echo $lang['sub_perfil'] ?? 'Administra tu información personal y credenciales'; ?></p>
            </div>

            <!-- Incluir el controlador que procesa la actualización y muestra alertas -->
            <?php include_once __DIR__ . '/../../controller/controlador_perfil.php'; ?>

            <div class="perfil-grid">
                
                <!-- Formulario de edición -->
                <div class="perfil-card">
                    <form method="POST" action="">
                        <div class="perfil-section-title">
                            <?php echo $lang['info_personal'] ?? 'Información Personal'; ?>
                        </div>

                        <div class="perfil-form-row">
                            <div class="campo">
                                <label><?php echo $lang['nombre_lbl'] ?? 'Nombre'; ?></label>
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($datosUsuario['nombre'] ?? ''); ?>" required>
                            </div>
                            <div class="campo">
                                <label><?php echo $lang['seudonimo_lbl'] ?? 'Seudónimo'; ?></label>
                                <input type="text" name="seudonimo" value="<?php echo htmlspecialchars($datosUsuario['seudonimo'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="perfil-form-row">
                            <div class="campo">
                                <label><?php echo $lang['correo_lbl'] ?? 'Correo Electrónico'; ?></label>
                                <input type="email" name="correo" value="<?php echo htmlspecialchars($datosUsuario['correo'] ?? ''); ?>" required>
                            </div>
                            <div class="campo">
                                <label><?php echo $lang['cedula_rut_lbl'] ?? 'Cédula / RUT'; ?></label>
                                <input type="text" value="<?php echo htmlspecialchars($datosUsuario['cedula_identidad'] ?? $datosUsuario['rut'] ?? ''); ?>" disabled class="campo-disabled">
                            </div>
                        </div>

                        <div class="perfil-divider"></div>

                        <div class="perfil-section-title">
                            <?php echo $lang['cambiar_pass'] ?? 'Cambiar Contraseña (Opcional)'; ?>
                        </div>

                        <div class="perfil-form-row">
                            <div class="campo">
                                <label><?php echo $lang['nueva_pass_lbl'] ?? 'Nueva Contraseña'; ?></label>
                                <input type="password" name="nueva_password" placeholder="••••••••" autocomplete="new-password">
                            </div>
                            <div class="campo">
                                <label><?php echo $lang['confirmar_pass_lbl'] ?? 'Confirmar Contraseña'; ?></label>
                                <input type="password" name="confirmar_password" placeholder="••••••••" autocomplete="new-password">
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 24px;">
                            <button type="submit" name="btnactualizar" value="1" class="btn-full btn-azul" style="flex: 1;">
                                <?php echo $lang['btn_guardar'] ?? 'Guardar Cambios'; ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tarjeta lateral de resumen de cuenta -->
                <div class="perfil-sidebar">
                    <div class="perfil-avatar">
                        <?php echo strtoupper(substr($datosUsuario['nombre'] ?? 'U', 0, 2)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($datosUsuario['nombre'] ?? ''); ?></h3>
                    <p class="perfil-tipo-usuario"><?php echo htmlspecialchars($datosUsuario['tipo_usuario'] ?? 'Usuario'); ?></p>
                    
                    <span class="perfil-badge-activo">
                        <span class="punto-verde"></span> 
                        <?php echo htmlspecialchars($datosUsuario['estado'] ?? 'Activo'); ?>
                    </span>

                    <div class="perfil-id-box">
                        <p><strong>ID de Usuario:</strong> #<?php echo $datosUsuario['id'] ?? ''; ?></p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>