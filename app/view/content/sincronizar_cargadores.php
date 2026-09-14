<?php
    require_once __DIR__ . "/../../view/inc/auth.php";
    require_once __DIR__ . "/../../view/inc/lang.php";
    /** @var array $lang */
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual ?? 'es'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronizar Cargadores - VoltMap</title>
    <link rel="stylesheet" href="../../view/css/estilosPanelAdmin.css">
    <style>
        .sync-container {
            text-align: center;
            padding: 1.5rem 0;
        }
        /* Estilos del Spinner de Carga */
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--borde);
            border-top: 4px solid var(--primario);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .sync-mensaje {
            color: var(--sub);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }
        .sync-output {
            background-color: #f9fafb;
            border: 1px solid var(--borde);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: left;
            font-size: 0.9rem;
            max-height: 250px;
            overflow-y: auto;
            display: none; /* Oculto hasta que termine */
        }
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Sincronización de Cargadores</h1>
            <p>Actualización en curso, por favor no cierre esta ventana</p>
        </div>

        <!-- Contenedor de Carga -->
        <div id="loader-section" class="sync-container">
            <div class="spinner"></div>
            <p class="sync-mensaje">Sincronizando cargadores con la base de datos.<br>Este proceso puede tomar un par de minutos...</p>
        </div>

        <!-- Contenedor donde se mostrará el resultado de cron_sync.php -->
        <div id="sync-output" class="sync-output"></div>

        <!-- Acciones (Botón de volver se muestra al terminar o siempre disponible) -->
        <div class="admin-actions">
            <button type="button" id="btn-volver" class="btn-admin btn-admin-secundario" onclick="location.href='/app/view/content/panel_admin.php'">
                ← Volver al Panel Administrador
            </button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loaderSection = document.getElementById("loader-section");
            const syncOutput = document.getElementById("sync-output");

            // Realizamos la petición asíncrona al script de sincronización
            fetch('../inc/cron_sync.php')
                .then(response => response.text())
                .then(data => {
                    // Ocultar el loader
                    loaderSection.classList.add("hidden");
                    
                    // Mostrar el contenedor con la respuesta del cron
                    syncOutput.innerHTML = data;
                    syncOutput.style.display = "block";

                    // Cambiar el subtítulo del header para avisar que finalizó
                    document.querySelector(".admin-header p").textContent = "¡Proceso de sincronización finalizado!";
                })
                .catch(error => {
                    loaderSection.classList.add("hidden");
                    syncOutput.innerHTML = "<p style='color: red;'>Ocurrió un error de red al intentar sincronizar.</p>";
                    syncOutput.style.display = "block";
                    document.querySelector(".admin-header p").textContent = "Error en la sincronización";
                });
        });
    </script>
</body>
</html>