<?php
// Establecer el código de estado HTTP
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - Sistema de Gestión</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-800 text-white py-4 px-6">
            <div class="flex items-center justify-center">
                <div class="mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2H5v-2h10zM7 10h2v2H7v-2zm6-3h-2v2h2V7z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">Sistema de Gestión</h1>
                    <p class="text-sm text-blue-300">Error 404</p>
                </div>
            </div>
        </div>
        
        <div class="py-12 px-8 text-center">
            <div class="text-6xl font-bold text-blue-800 mb-4">404</div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-2">Página no encontrada</h2>
            <p class="text-gray-600 mb-8">Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
            
            <div class="flex justify-center">
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                    Volver al inicio
                </a>
            </div>
        </div>
        
        <div class="bg-gray-100 py-4 px-6 text-center">
            <p class="text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> Sistema de Gestión. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
