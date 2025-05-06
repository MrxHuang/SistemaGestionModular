<?php
/**
 * Script para verificar la configuración del sistema
 * Comprueba requisitos, permisos y conexión a la base de datos
 */

// Función para verificar si un requisito se cumple
function check_requirement($name, $condition, $message = '') {
    echo "<tr>";
    echo "<td>$name</td>";
    if ($condition) {
        echo '<td class="text-green-600 font-bold">✓ OK</td>';
    } else {
        echo '<td class="text-red-600 font-bold">✗ Error</td>';
    }
    echo "<td>$message</td>";
    echo "</tr>";
    return $condition;
}

// Iniciar contador de errores
$errors = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - Sistema de Gestión</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-800 text-white py-4 px-6">
            <div class="flex items-center">
                <div class="mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2H5v-2h10zM7 10h2v2H7v-2zm6-3h-2v2h2V7z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">Sistema de Gestión</h1>
                    <p class="text-sm text-blue-300">Verificación del Sistema</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Diagnóstico del Sistema</h2>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Requisitos del Sistema</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requisito</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            // Verificar versión de PHP
                            $php_version = phpversion();
                            $php_ok = version_compare($php_version, '7.4.0', '>=');
                            if (!check_requirement('PHP Version', $php_ok, "Se requiere PHP 7.4 o superior. Versión actual: $php_version")) {
                                $errors++;
                            }
                            
                            // Verificar extensiones requeridas
                            $extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring', 'gd'];
                            foreach ($extensions as $ext) {
                                $loaded = extension_loaded($ext);
                                if (!check_requirement("Extensión $ext", $loaded, $loaded ? "Instalada" : "No instalada")) {
                                    $errors++;
                                }
                            }
                            
                            // Verificar permisos de escritura
                            $directories = [
                                'database' => __DIR__ . '/database',
                                'logs' => __DIR__ . '/logs',
                                'assets/img' => __DIR__ . '/assets/img'
                            ];
                            
                            foreach ($directories as $name => $dir) {
                                $exists = file_exists($dir);
                                if (!$exists) {
                                    // Intentar crear el directorio
                                    $exists = @mkdir($dir, 0755, true);
                                }
                                $writable = $exists && is_writable($dir);
                                if (!check_requirement("Directorio $name", $writable, $writable ? "Permisos correctos" : "Sin permisos de escritura")) {
                                    $errors++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Conexión a la Base de Datos</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prueba</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            // Verificar conexión a la base de datos
                            $db_connected = false;
                            $db_message = "";
                            
                            try {
                                $host = "localhost";
                                $db_name = "sistema_gestion";
                                $username = "root";
                                $password = "";
                                
                                $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
                                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                
                                $db_connected = true;
                                $db_message = "Conexión exitosa a la base de datos '$db_name'";
                                
                                // Verificar tablas requeridas
                                $tables = ['usuarios', 'categorias', 'proveedores', 'productos', 'ventas', 'detalles_venta'];
                                $missing_tables = [];
                                
                                foreach ($tables as $table) {
                                    $stmt = $conn->query("SHOW TABLES LIKE '$table'");
                                    if ($stmt->rowCount() == 0) {
                                        $missing_tables[] = $table;
                                    }
                                }
                                
                                if (count($missing_tables) > 0) {
                                    $db_connected = false;
                                    $db_message = "Faltan las siguientes tablas: " . implode(", ", $missing_tables);
                                    $errors++;
                                }
                                
                            } catch(PDOException $e) {
                                $db_connected = false;
                                $db_message = "Error de conexión: " . $e->getMessage();
                                $errors++;
                            }
                            
                            check_requirement('Conexión a la Base de Datos', $db_connected, $db_message);
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Resultado</h3>
                <?php if ($errors == 0): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                    <p class="font-bold">Todo está configurado correctamente.</p>
                    <p>El sistema está listo para ser utilizado.</p>
                </div>
                <?php else: ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                    <p class="font-bold">Se encontraron <?php echo $errors; ?> error(es).</p>
                    <p>Por favor, corrija los problemas antes de utilizar el sistema.</p>
                </div>
                <?php endif; ?>
                
                <div class="mt-4 flex space-x-4">
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Ir al sistema
                    </a>
                    <a href="init_db.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Inicializar Base de Datos
                    </a>
                </div>
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
