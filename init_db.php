<?php
/**
 * Script para inicializar la base de datos
 * Este archivo crea la base de datos y ejecuta el esquema SQL
 */

// Configuración de la base de datos
$host = "localhost";
$username = "root";
$password = "";
$db_name = "sistema_gestion";

// Conectar a MySQL sin seleccionar base de datos
try {
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Inicializando base de datos...</h2>";
    
    // Leer el archivo SQL
    $sql_file = file_get_contents('database/schema.sql');
    
    // Ejecutar el script SQL
    $conn->exec($sql_file);
    
    echo "<div style='color: green; margin: 20px 0;'>¡Base de datos inicializada correctamente!</div>";
    echo "<p>Ahora puedes acceder al sistema con las siguientes credenciales:</p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> admin@sistema.com</li>";
    echo "<li><strong>Contraseña:</strong> password</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Ir al sistema</a></p>";
    
} catch(PDOException $e) {
    echo "<div style='color: red; margin: 20px 0;'>Error al inicializar la base de datos: " . $e->getMessage() . "</div>";
}

// Cerrar conexión
$conn = null;
?>
