<?php
/**
 * Configuración global del sistema
 */

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Gestión');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Proyectos/Modular');
define('TIMEZONE', 'America/Bogota');

// Configuración de la sesión
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_PATH', '/');
define('SESSION_SECURE', false); // Cambiar a true si se usa HTTPS
define('SESSION_HTTPONLY', true);

// Configuración de errores
define('DISPLAY_ERRORS', true); // Cambiar a false en producción
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', __DIR__ . '/../logs/error.log');

// Configuración de la base de datos (redundante con database.php pero útil para otros scripts)
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_gestion');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de rutas
define('ROOT_PATH', dirname(__DIR__));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('INCLUDES_PATH', ROOT_PATH . '/includes');

// Configuración de fecha y hora
date_default_timezone_set(TIMEZONE);

// Configuración de la sesión
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_path', SESSION_PATH);
ini_set('session.cookie_secure', SESSION_SECURE);
ini_set('session.cookie_httponly', SESSION_HTTPONLY);
ini_set('session.use_only_cookies', 1);

// Configuración de errores
ini_set('display_errors', DISPLAY_ERRORS);
ini_set('log_errors', LOG_ERRORS);
ini_set('error_log', ERROR_LOG_FILE);

// Crear directorio de logs si no existe
if (!file_exists(dirname(ERROR_LOG_FILE))) {
    mkdir(dirname(ERROR_LOG_FILE), 0755, true);
}

// Función para cargar automáticamente clases
function autoload($class) {
    $file = ROOT_PATH . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('autoload');

// Función para sanitizar entradas
function sanitize($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
    } else {
        $input = htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

// Función para redireccionar
function redirect($url) {
    header("Location: $url");
    exit();
}

// Función para mostrar mensajes de alerta
function alert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Función para verificar si el usuario está autenticado
function is_authenticated() {
    return isset($_SESSION['user_id']);
}

// Función para verificar si el usuario es administrador
function is_admin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}
?>
