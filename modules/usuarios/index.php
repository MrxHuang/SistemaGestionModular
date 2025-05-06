<?php
// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
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

// Delete user if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if it's not the current user
    if ($id != $_SESSION['user_id']) {
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        // Set success message
        $_SESSION['success'] = "Usuario eliminado correctamente.";
    } else {
        // Set error message
        $_SESSION['error'] = "No puede eliminar su propio usuario.";
    }
    
    // Redirect to refresh page using safe redirect
    safe_redirect("?module=usuarios&page=index");
}

// Get all users
$query = "SELECT id, nombre, apellido, email, rol FROM usuarios ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container px-6 py-8 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Gestión de Usuarios</h2>
        <a href="?module=usuarios&page=create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nuevo Usuario
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $usuario['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 font-semibold">
                                <?php echo strtoupper(substr($usuario['nombre'], 0, 1)) . strtoupper(substr($usuario['apellido'], 0, 1)); ?>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $usuario['email']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $usuario['rol'] === 'admin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                            <?php echo ucfirst($usuario['rol']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="?module=usuarios&page=edit&id=<?php echo $usuario['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                            <a href="?module=usuarios&page=index&delete=<?php echo $usuario['id']; ?>" class="text-red-600 hover:text-red-900 delete-confirm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay usuarios registrados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
