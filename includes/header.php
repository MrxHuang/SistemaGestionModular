<?php
// Get current user information
if (isset($_SESSION['user_id'])) {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, nombre, apellido, rol FROM usuarios WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get current page for navigation highlighting
$current_module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
$current_page = isset($_GET['page']) ? $_GET['page'] : 'index';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: calc(100vh - 0px);
        }
        .content {
            min-height: calc(100vh - 0px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
