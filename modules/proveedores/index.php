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

// Delete supplier if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if supplier is used in any product
    $query = "SELECT COUNT(*) as count FROM productos WHERE proveedor_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        // Supplier is used in products, cannot delete
        $_SESSION['error'] = "No se puede eliminar el proveedor porque está asociado a productos.";
    } else {
        // Delete supplier
        $query = "DELETE FROM proveedores WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        // Set success message
        $_SESSION['success'] = "Proveedor eliminado correctamente.";
    }
    
    // Redirect to refresh page using safe redirect
    safe_redirect("?module=proveedores&page=index");
    exit();
}

// Get all suppliers with product count
$query = "SELECT p.*, COUNT(pr.id) as productos_count 
          FROM proveedores p 
          LEFT JOIN productos pr ON p.id = pr.proveedor_id 
          GROUP BY p.id 
          ORDER BY p.nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container px-6 py-8 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Gestión de Proveedores</h2>
        <a href="?module=proveedores&page=create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nuevo Proveedor
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
    
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIT</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($proveedores as $proveedor): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $proveedor['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo $proveedor['nombre']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo !empty($proveedor['nit']) ? $proveedor['nit'] : 'N/A'; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php if (!empty($proveedor['telefono']) || !empty($proveedor['email'])): ?>
                        <div>
                            <?php if (!empty($proveedor['telefono'])): ?>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <?php echo $proveedor['telefono']; ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($proveedor['email'])): ?>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <?php echo $proveedor['email']; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        N/A
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php if (!empty($proveedor['ciudad']) || !empty($proveedor['direccion'])): ?>
                        <div>
                            <?php if (!empty($proveedor['ciudad'])): ?>
                            <div><?php echo $proveedor['ciudad']; ?></div>
                            <?php endif; ?>
                            <?php if (!empty($proveedor['direccion'])): ?>
                            <div class="text-xs text-gray-400"><?php echo $proveedor['direccion']; ?></div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        N/A
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            <?php echo $proveedor['productos_count']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="?module=proveedores&page=edit&id=<?php echo $proveedor['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <?php if ($proveedor['productos_count'] == 0): ?>
                            <a href="?module=proveedores&page=index&delete=<?php echo $proveedor['id']; ?>" class="text-red-600 hover:text-red-900 delete-confirm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($proveedores)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No hay proveedores registrados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
