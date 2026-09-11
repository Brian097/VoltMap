<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoltMap - Publicar Cargador Doméstico</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col justify-between">

    <!-- Header / Navbar simplificado -->
    <header class="bg-gray-900 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <span class="text-xl font-bold tracking-wider text-emerald-400">Volt<span class="text-white">Map</span></span>
            <a href="mis-cargadores.php" class="text-sm bg-gray-800 hover:bg-gray-700 px-3 py-1.5 rounded transition">Mis Cargadores</a>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="container mx-auto px-4 py-8 max-w-xl">
        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Poner en alquiler mi cargador</h2>
            <p class="text-sm text-gray-600 mb-6">Completa los datos técnicos de tu infraestructura doméstica para compartirla en la red colaborativa.</p>

            <form action="guardar_cargador.php" method="POST" class="space-y-4">
                <!-- ID de Usuario Propietario (Simulado o tomado de sesión) -->
                <input type="hidden" name="id_usuario" value="1">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección / Ubicación</label>
                    <input type="text" name="direccion" required placeholder="Ej: Av. General Artigas 1234, Rocha" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Potencia del Cargador (kW)</label>
                        <input type="number" step="0.1" name="potencia_kw" required placeholder="Ej: 7.4" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-xs text-gray-500 mt-1 block">Modelo monofásico/trifásico estándar.</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Conector</label>
                        <select name="tipo_conector" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Tipo 2 (Mennekes)">Tipo 2 (Mennekes)</option>
                            <option value="Tipo 1 (J1772)">Tipo 1 (J1772)</option>
                            <option value="CCS / Combo">CCS / Combo</option>
                            <option value="Schuko (Doméstico)">Schuko (Doméstico)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tarifa por Hora ($ UYU)</label>
                    <input type="number" step="0.01" name="precio_hora" required placeholder="Ej: 150.00" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción o Instrucciones de Acceso</label>
                    <textarea name="descripcion" rows="3" placeholder="Ej: Entrada por lateral, dejar libre el portón..." 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition duration-200 shadow-md">
                    Publicar Cargador
                </button>
            </form>
        </div>
    </main>

    <footer class="text-center py-4 text-xs text-gray-500">
        Garra Dev &copy; 2026 — Proyecto VoltMap
    </footer>

</body>
</html>