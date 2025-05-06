<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: modules/auth/login.php");
    exit();
}

// Include utils
include_once 'includes/utils.php';

// Check if id parameter is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    safe_redirect("?module=proveedores&page=index");
    exit();
}

// Include database connection
include_once 'config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get supplier ID
$id = $_GET['id'];

// Define variables and initialize with empty values
$nombre = $nit = $direccion = $ciudad = $telefono = $email = "";
$nombre_err = "";

// Get supplier data
$query = "SELECT * FROM proveedores WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);

if ($stmt->rowCount() == 0) {
    // No supplier found with that ID
    $_SESSION['error'] = "Proveedor no encontrado.";
    safe_redirect("?module=proveedores&page=index");
    exit();
}

$proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre = $proveedor['nombre'];
$nit = $proveedor['nit'];
$direccion = $proveedor['direccion'];
$ciudad = $proveedor['ciudad'];
$telefono = $proveedor['telefono'];
$email = $proveedor['email'];

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor ingrese el nombre del proveedor.";
    } else {
        $nombre = trim($_POST["nombre"]);
    }
    
    // Get optional fields
    $nit = trim($_POST["nit"]);
    $direccion = trim($_POST["direccion"]);
    $ciudad = trim($_POST["ciudad"]);
    $telefono = trim($_POST["telefono"]);
    $email = trim($_POST["email"]);
    
    // Check input errors before updating in database
    if (empty($nombre_err)) {
        
        // Prepare an update statement
        $sql = "UPDATE proveedores SET nombre = :nombre, nit = :nit, direccion = :direccion, ciudad = :ciudad, telefono = :telefono, email = :email WHERE id = :id";
         
        if ($stmt = $db->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":nombre", $param_nombre, PDO::PARAM_STR);
            $stmt->bindParam(":nit", $param_nit, PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $param_direccion, PDO::PARAM_STR);
            $stmt->bindParam(":ciudad", $param_ciudad, PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $param_telefono, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);
            
            // Set parameters
            $param_nombre = $nombre;
            $param_nit = $nit;
            $param_direccion = $direccion;
            $param_ciudad = $ciudad;
            $param_telefono = $telefono;
            $param_email = $email;
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Set success message
                $_SESSION['success'] = "Proveedor actualizado correctamente.";
                
                // Redirect to suppliers page using safe redirect
                safe_redirect("?module=proveedores&page=index");
            } else {
                echo "Oops! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            
            // Close statement
            unset($stmt);
        }
        
        // Close connection
        unset($db);
    }
}
?>

<div class="container px-6 py-8 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Editar Proveedor</h2>
        <a href="?module=proveedores&page=index" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="?module=proveedores&page=edit&id=<?php echo $id; ?>" method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($nombre_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $nombre; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $nombre_err; ?></span>
                </div>
                
                <div>
                    <label for="nit" class="block text-sm font-medium text-gray-700 mb-1">NIT (Opcional)</label>
                    <input type="text" name="nit" id="nit" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo $nit; ?>">
                </div>
                
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono (Opcional)</label>
                    <input type="text" name="telefono" id="telefono" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo $telefono; ?>">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email (Opcional)</label>
                    <input type="email" name="email" id="email" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo $email; ?>">
                </div>
                
                <div>
                    <label for="ciudad" class="block text-sm font-medium text-gray-700 mb-1">Ciudad (Opcional)</label>
                    <input type="text" name="ciudad" id="ciudad" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo $ciudad; ?>">
                </div>
                
                <div class="md:col-span-2">
                    <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Dirección (Opcional)</label>
                    <input type="text" name="direccion" id="direccion" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo $direccion; ?>">
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Actualizar Proveedor
                </button>
            </div>
        </form>
    </div>
</div>
