<?php
/**
 * Archivo de utilidades para el sistema
 */

/**
 * Función para redireccionar de manera segura
 * Esta función almacena la URL de redirección en una sesión y luego
 * utiliza JavaScript para realizar la redirección, evitando el error
 * "headers already sent"
 */
function safe_redirect($url) {
    $_SESSION['redirect_url'] = $url;
    echo "<script>window.location.href = '{$url}';</script>";
    exit;
}

/**
 * Función para mostrar mensajes de alerta
 */
function set_alert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Función para sanitizar entradas
 */
function sanitize_input($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize_input($value);
        }
    } else {
        $input = htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

/**
 * Función para validar email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Función para validar que un campo no esté vacío
 */
function is_not_empty($value) {
    return !empty(trim($value));
}

/**
 * Función para generar un token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Función para verificar un token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Función para formatear fecha
 */
function format_date($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Función para formatear moneda
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2, ',', '.');
}
?>
