<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: modules/auth/login.php");
    exit();
}

// Include database connection
include_once 'config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Set default date range (last 30 days)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// Get sales report
$query = "SELECT DATE(v.fecha_venta) as fecha, 
          COUNT(v.id) as total_ventas, 
          SUM(v.total) as total_ingresos
          FROM ventas v 
          WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
          GROUP BY DATE(v.fecha_venta)
          ORDER BY fecha";
$stmt = $db->prepare($query);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$ventas_por_fecha = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sales by category
$query = "SELECT c.nombre as categoria, 
          COUNT(dv.id) as total_ventas, 
          SUM(dv.subtotal) as total_ingresos
          FROM detalles_venta dv
          JOIN productos p ON dv.producto_id = p.id
          JOIN categorias c ON p.categoria_id = c.id
          JOIN ventas v ON dv.venta_id = v.id
          WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
          GROUP BY c.id
          ORDER BY total_ingresos DESC";
$stmt = $db->prepare($query);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$ventas_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top selling products
$query = "SELECT p.nombre as producto, 
          c.nombre as categoria,
          SUM(dv.cantidad) as cantidad_vendida, 
          SUM(dv.subtotal) as total_ingresos
          FROM detalles_venta dv
          JOIN productos p ON dv.producto_id = p.id
          JOIN categorias c ON p.categoria_id = c.id
          JOIN ventas v ON dv.venta_id = v.id
          WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
          GROUP BY p.id
          ORDER BY cantidad_vendida DESC
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$productos_mas_vendidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sales by user
$query = "SELECT CONCAT(u.nombre, ' ', u.apellido) as usuario, 
          COUNT(v.id) as total_ventas, 
          SUM(v.total) as total_ingresos
          FROM ventas v
          JOIN usuarios u ON v.usuario_id = u.id
          WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
          GROUP BY u.id
          ORDER BY total_ingresos DESC";
$stmt = $db->prepare($query);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$ventas_por_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate summary
$total_ventas = 0;
$total_ingresos = 0;
$total_productos = 0;

foreach ($ventas_por_fecha as $venta) {
    $total_ventas += $venta['total_ventas'];
    $total_ingresos += $venta['total_ingresos'];
}

foreach ($productos_mas_vendidos as $producto) {
    $total_productos += $producto['cantidad_vendida'];
}

// Format data for charts
$fechas = [];
$ventas_count = [];
$ingresos = [];

foreach ($ventas_por_fecha as $venta) {
    $fechas[] = date('d/m', strtotime($venta['fecha']));
    $ventas_count[] = $venta['total_ventas'];
    $ingresos[] = $venta['total_ingresos'];
}

$categorias = [];
$ventas_por_categoria_data = [];

foreach ($ventas_por_categoria as $venta) {
    $categorias[] = $venta['categoria'];
    $ventas_por_categoria_data[] = $venta['total_ingresos'];
}
?>

<div class="container px-6 py-8 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Reportes</h2>
        <div>
            <button id="printReportBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Reporte
            </button>
        </div>
    </div>
    
    <!-- Date Range Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="" method="get" class="flex flex-wrap items-end space-x-4">
            <input type="hidden" name="module" value="reportes">
            <input type="hidden" name="page" value="index">
            
            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo $fecha_inicio; ?>">
            </div>
            
            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo $fecha_fin; ?>">
            </div>
            
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Filtrar
                </button>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="rounded-full bg-blue-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Ventas</p>
                <p class="text-2xl font-bold"><?php echo $total_ventas; ?></p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="rounded-full bg-green-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Ingresos</p>
                <p class="text-2xl font-bold">$<?php echo number_format($total_ingresos, 2); ?></p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="rounded-full bg-purple-100 p-3 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Total Productos Vendidos</p>
                <p class="text-2xl font-bold"><?php echo $total_productos; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales Over Time Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Ventas por Fecha</h3>
            <div class="h-80">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <!-- Sales by Category Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Ventas por Categoría</h3>
            <div class="h-80">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700">Productos Más Vendidos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($productos_mas_vendidos as $producto): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $producto['producto']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $producto['categoria']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $producto['cantidad_vendida']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo number_format($producto['total_ingresos'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($productos_mas_vendidos)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No hay datos disponibles.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Sales by User Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700">Ventas por Usuario</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($ventas_por_usuario as $venta): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $venta['usuario']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $venta['total_ventas']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo number_format($venta['total_ingresos'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($ventas_por_usuario)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No hay datos disponibles.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($fechas); ?>,
            datasets: [
                {
                    label: 'Ventas',
                    data: <?php echo json_encode($ventas_count); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: 'Ingresos',
                    data: <?php echo json_encode($ingresos); ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Ventas'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Ingresos ($)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
    
    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($categorias); ?>,
            datasets: [{
                data: <?php echo json_encode($ventas_por_categoria_data); ?>,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(75, 85, 99, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(14, 165, 233, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(234, 179, 8, 0.8)'
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
    
    // Print Report
    document.getElementById('printReportBtn').addEventListener('click', function() {
        window.print();
    });
});
</script>

<style media="print">
    @page {
        size: auto;
        margin: 10mm;
    }
    
    body {
        margin: 0;
        padding: 0;
    }
    
    .container {
        width: 100%;
        max-width: 100%;
        padding: 0;
    }
    
    .no-print, .no-print * {
        display: none !important;
    }
    
    header, nav, .sidebar, footer, .flex-shrink-0 {
        display: none !important;
    }
    
    .content {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    form, button#printReportBtn {
        display: none !important;
    }
</style>
