<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: modules/auth/login.php");
    exit();
}

// Check if id parameter is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ?module=ventas&page=index");
    exit();
}

// Include database connection
include_once 'config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get sale ID
$id = $_GET['id'];

// Get sale data
$query = "SELECT v.*, CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre 
          FROM ventas v 
          JOIN usuarios u ON v.usuario_id = u.id 
          WHERE v.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);

if ($stmt->rowCount() == 0) {
    // No sale found with that ID
    $_SESSION['error'] = "Venta no encontrada.";
    header("Location: ?module=ventas&page=index");
    exit();
}

$venta = $stmt->fetch(PDO::FETCH_ASSOC);

// Get sale details
$query = "SELECT dv.*, p.nombre as producto_nombre, c.nombre as categoria_nombre 
          FROM detalles_venta dv 
          JOIN productos p ON dv.producto_id = p.id 
          JOIN categorias c ON p.categoria_id = c.id 
          WHERE dv.venta_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container px-6 py-8 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Detalles de Venta #<?php echo $id; ?></h2>
        <a href="?module=ventas&page=index" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Sale Information -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Información de la Venta</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Fecha</p>
                    <p class="font-medium"><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Usuario</p>
                    <p class="font-medium"><?php echo $venta['usuario_nombre']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="font-medium text-green-600">$<?php echo number_format($venta['total'], 2); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Productos</p>
                    <p class="font-medium"><?php echo count($detalles); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Acciones</h3>
            <div class="flex space-x-4">
                <a href="#" onclick="window.print();" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir
                </a>
                <a href="?module=ventas&page=index&delete=<?php echo $id; ?>" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center delete-confirm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                </a>
            </div>
        </div>
    </div>
    
    <!-- Sale Details -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700">Detalle de Productos</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?php echo $detalle['producto_nombre']; ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo $detalle['categoria_nombre']; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        $<?php echo number_format($detalle['precio_unitario'], 2); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo $detalle['cantidad']; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        $<?php echo number_format($detalle['subtotal'], 2); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($detalles)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay detalles disponibles.</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50">
                    <td colspan="4" class="px-6 py-4 text-right font-bold">Total:</td>
                    <td class="px-6 py-4 font-bold text-green-600">$<?php echo number_format($venta['total'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

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
</style>
