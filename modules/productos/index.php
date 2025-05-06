<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: modules/auth/login.php");
    exit();
}

// Include utils
include_once 'includes/utils.php';

// Include database connection
include_once 'config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Delete product if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if product is used in any sale
    $query = "SELECT COUNT(*) as count FROM detalles_venta WHERE producto_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        // Product is used in sales, cannot delete
        $_SESSION['error'] = "No se puede eliminar el producto porque está asociado a ventas.";
    } else {
        // Delete product
        $query = "DELETE FROM productos WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        // Set success message
        $_SESSION['success'] = "Producto eliminado correctamente.";
    }
    
    // Redirect to refresh page using safe redirect
    safe_redirect("?module=productos&page=index");
}

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$where_clause = '';
$params = [];

if (!empty($busqueda)) {
    $where_clause = " WHERE p.nombre LIKE ? OR p.descripcion LIKE ? ";
    $params = ["%$busqueda%", "%$busqueda%"];
}

// Contar total de registros para la paginación
$query_count = "SELECT COUNT(*) as total FROM productos p $where_clause";
$stmt_count = $db->prepare($query_count);
$stmt_count->execute($params);
$total_registros = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Get products with category and supplier names with pagination
$query = "SELECT p.*, c.nombre as categoria_nombre, pr.nombre as proveedor_nombre 
          FROM productos p 
          LEFT JOIN categorias c ON p.categoria_id = c.id 
          LEFT JOIN proveedores pr ON p.proveedor_id = pr.id 
          $where_clause
          ORDER BY p.id DESC
          LIMIT $offset, $registros_por_pagina";
$stmt = $db->prepare($query);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container px-6 py-8 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Gestión de Productos</h2>
        <a href="?module=productos&page=create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nuevo Producto
        </a>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 flash-message" role="alert">
        <p><?php echo $_SESSION['success']; ?></p>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 flash-message" role="alert">
        <p><?php echo $_SESSION['error']; ?></p>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Barra de búsqueda -->
    <div class="mb-6">
        <form action="" method="GET" class="flex items-center">
            <input type="hidden" name="module" value="productos">
            <input type="hidden" name="page" value="index">
            <div class="flex-grow">
                <input type="text" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar productos..." class="w-full px-4 py-2 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
        </form>
    </div>
    
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($productos as $producto): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $producto['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?php echo $producto['nombre']; ?></div>
                        <?php if (!empty($producto['descripcion'])): ?>
                        <div class="text-sm text-gray-500"><?php echo substr($producto['descripcion'], 0, 50) . (strlen($producto['descripcion']) > 50 ? '...' : ''); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo number_format($producto['precio'], 2); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $producto['cantidad'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $producto['cantidad']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $producto['categoria_nombre']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $producto['proveedor_nombre'] ?? 'No asignado'; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="?module=productos&page=edit&id=<?php echo $producto['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <a href="?module=productos&page=index&delete=<?php echo $producto['id']; ?>" class="text-red-600 hover:text-red-900 delete-confirm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                        <?php echo !empty($busqueda) ? 'No se encontraron productos que coincidan con la búsqueda.' : 'No hay productos registrados.'; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
    <div class="mt-6 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <?php if ($pagina_actual > 1): ?>
            <a href="?module=productos&page=index&pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <span class="sr-only">Anterior</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
            <?php else: ?>
            <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400">
                <span class="sr-only">Anterior</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </span>
            <?php endif; ?>
            
            <?php
            // Mostrar un número limitado de páginas
            $max_pages_to_show = 5;
            $start_page = max(1, min($pagina_actual - floor($max_pages_to_show / 2), $total_paginas - $max_pages_to_show + 1));
            $end_page = min($total_paginas, $start_page + $max_pages_to_show - 1);
            
            // Si estamos cerca del final, ajustar el inicio
            if ($end_page - $start_page + 1 < $max_pages_to_show) {
                $start_page = max(1, $end_page - $max_pages_to_show + 1);
            }
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <?php if ($i == $pagina_actual): ?>
                <span aria-current="page" class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                    <?php echo $i; ?>
                </span>
                <?php else: ?>
                <a href="?module=productos&page=index&pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <?php echo $i; ?>
                </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?module=productos&page=index&pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <span class="sr-only">Siguiente</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
            <?php else: ?>
            <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400">
                <span class="sr-only">Siguiente</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </span>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
    
    <!-- Información de paginación -->
    <?php if ($total_registros > 0): ?>
    <div class="mt-4 text-center text-sm text-gray-600">
        Mostrando <?php echo ($offset + 1); ?> - <?php echo min($offset + $registros_por_pagina, $total_registros); ?> de <?php echo $total_registros; ?> productos
    </div>
    <?php endif; ?>
</div>
