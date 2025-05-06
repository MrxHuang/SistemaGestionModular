<?php
// Include database connection
include_once 'config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get statistics for dashboard
// Total products
$query = "SELECT COUNT(*) as total FROM productos";
$stmt = $db->prepare($query);
$stmt->execute();
$productos = $stmt->fetch(PDO::FETCH_ASSOC);
$total_productos = $productos['total'];

// Total sales
$query = "SELECT COUNT(*) as total FROM ventas";
$stmt = $db->prepare($query);
$stmt->execute();
$ventas = $stmt->fetch(PDO::FETCH_ASSOC);
$total_ventas = $ventas['total'];

// Total income
$query = "SELECT SUM(total) as total FROM ventas";
$stmt = $db->prepare($query);
$stmt->execute();
$ingresos = $stmt->fetch(PDO::FETCH_ASSOC);
$total_ingresos = $ingresos['total'] ? $ingresos['total'] : 0;

// Total suppliers
$query = "SELECT COUNT(*) as total FROM proveedores";
$stmt = $db->prepare($query);
$stmt->execute();
$proveedores = $stmt->fetch(PDO::FETCH_ASSOC);
$total_proveedores = $proveedores['total'];

// Get recent activity
$query = "SELECT v.id, v.fecha_venta, v.total, CONCAT(u.nombre, ' ', u.apellido) as usuario 
          FROM ventas v 
          JOIN usuarios u ON v.usuario_id = u.id 
          ORDER BY v.fecha_venta DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sales by category for pie chart
$query = "SELECT c.nombre, SUM(dv.subtotal) as total 
          FROM detalles_venta dv 
          JOIN productos p ON dv.producto_id = p.id 
          JOIN categorias c ON p.categoria_id = c.id 
          GROUP BY c.id";
$stmt = $db->prepare($query);
$stmt->execute();
$sales_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get weekly sales for line chart
$query = "SELECT DATE_FORMAT(fecha_venta, '%W') as dia, SUM(total) as total 
          FROM ventas 
          WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
          GROUP BY DATE_FORMAT(fecha_venta, '%W') 
          ORDER BY FIELD(DATE_FORMAT(fecha_venta, '%W'), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$stmt = $db->prepare($query);
$stmt->execute();
$weekly_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data for charts
$category_labels = [];
$category_data = [];
foreach ($sales_by_category as $category) {
    $category_labels[] = $category['nombre'];
    $category_data[] = $category['total'];
}

$weekly_labels = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
$weekly_data = array_fill(0, 7, 0);
$day_map = [
    'Monday' => 0,
    'Tuesday' => 1,
    'Wednesday' => 2,
    'Thursday' => 3,
    'Friday' => 4,
    'Saturday' => 5,
    'Sunday' => 6
];

foreach ($weekly_sales as $day) {
    if (isset($day_map[$day['dia']])) {
        $weekly_data[$day_map[$day['dia']]] = $day['total'];
    }
}
?>

<div class="container px-6 mx-auto">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h2>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Products Card -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
            <div class="rounded-full bg-blue-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Productos</p>
                <p class="text-2xl font-bold"><?php echo $total_productos; ?></p>
                <a href="?module=productos&page=index" class="text-blue-500 text-sm hover:underline">Ver todos los productos →</a>
            </div>
        </div>
        
        <!-- Total Sales Card -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
            <div class="rounded-full bg-green-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Ventas</p>
                <p class="text-2xl font-bold"><?php echo $total_ventas; ?></p>
                <a href="?module=ventas&page=index" class="text-green-500 text-sm hover:underline">Ver todas las ventas →</a>
            </div>
        </div>
        
        <!-- Total Income Card -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
            <div class="rounded-full bg-purple-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Ingresos</p>
                <p class="text-2xl font-bold">$<?php echo number_format($total_ingresos, 2); ?></p>
                <a href="?module=reportes&page=index" class="text-purple-500 text-sm hover:underline">Ver detalles financieros →</a>
            </div>
        </div>
        
        <!-- Total Suppliers Card -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
            <div class="rounded-full bg-yellow-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Proveedores</p>
                <p class="text-2xl font-bold"><?php echo $total_proveedores; ?></p>
                <a href="?module=proveedores&page=index" class="text-yellow-500 text-sm hover:underline">Ver todos los proveedores →</a>
            </div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Weekly Sales Chart -->
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Ventas de la Semana</h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <!-- Category Distribution Chart -->
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Distribución por Categorías</h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Actividad Reciente</h3>
            <div class="space-y-4">
                <?php foreach ($recent_sales as $sale): ?>
                <div class="flex items-start">
                    <div class="rounded-full bg-green-100 p-2 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-800">Venta #<?php echo $sale['id']; ?> completada</p>
                        <p class="text-gray-500 text-sm">Por: <?php echo $sale['usuario']; ?></p>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-gray-500 text-sm"><?php echo date('d/m/Y H:i', strtotime($sale['fecha_venta'])); ?></span>
                            <span class="text-green-500 font-semibold">$<?php echo number_format($sale['total'], 2); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($recent_sales)): ?>
                <p class="text-gray-500">No hay actividad reciente</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Acciones Rápidas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="?module=productos&page=create" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <div class="rounded-full bg-blue-100 p-2 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-800 font-medium">Nuevo Producto</p>
                        <p class="text-gray-500 text-xs">Añadir un nuevo producto al inventario</p>
                    </div>
                </a>
                <a href="?module=ventas&page=create" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <div class="rounded-full bg-green-100 p-2 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-800 font-medium">Registrar Venta</p>
                        <p class="text-gray-500 text-xs">Crear una nueva venta</p>
                    </div>
                </a>
                <a href="?module=proveedores&page=create" class="flex items-center p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                    <div class="rounded-full bg-yellow-100 p-2 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-800 font-medium">Nuevo Proveedor</p>
                        <p class="text-gray-500 text-xs">Añadir un nuevo proveedor</p>
                    </div>
                </a>
                <a href="?module=reportes&page=index" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <div class="rounded-full bg-purple-100 p-2 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-800 font-medium">Ver Reportes</p>
                        <p class="text-gray-500 text-xs">Generar informes y estadísticas</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Setup charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($weekly_labels); ?>,
            datasets: [{
                label: 'Ventas',
                data: <?php echo json_encode($weekly_data); ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Category Distribution Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($category_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($category_data); ?>,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>
