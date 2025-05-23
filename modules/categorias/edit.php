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
    safe_redirect("?module=categorias&page=index");
    exit();
}

// Include database connection
include_once 'config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get category ID
$id = $_GET['id'];

// Define variables and initialize with empty values
$nombre = $descripcion = "";
$nombre_err = "";

// Get category data
$query = "SELECT * FROM categorias WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);

if ($stmt->rowCount() == 0) {
    // No category found with that ID
    $_SESSION['error'] = "Categoría no encontrada.";
    safe_redirect("?module=categorias&page=index");
    exit();
}

$categoria = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre = $categoria['nombre'];
$descripcion = $categoria['descripcion'];

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor ingrese el nombre de la categoría.";
    } else {
        // Check if category name already exists (only if name has changed)
        if ($nombre !== trim($_POST["nombre"])) {
            // Create database connection
            $database = new Database();
            $db = $database->getConnection();
            
            // Check if category name already exists
            $sql = "SELECT id FROM categorias WHERE nombre = :nombre";
            
            if ($stmt = $db->prepare($sql)) {
                $stmt->bindParam(":nombre", $param_nombre, PDO::PARAM_STR);
                $param_nombre = trim($_POST["nombre"]);
                
                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        $nombre_err = "Esta categoría ya existe.";
                    } else {
                        $nombre = trim($_POST["nombre"]);
                    }
                } else {
                    echo "Oops! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
                }
                
                unset($stmt);
            }
        } else {
            $nombre = trim($_POST["nombre"]);
        }
    }
    
    // Get optional description
    $descripcion = trim($_POST["descripcion"]);
    
    // Check input errors before updating in database
    if (empty($nombre_err)) {
        
        // Create database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Prepare an update statement
        $sql = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
         
        if ($stmt = $db->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":nombre", $param_nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $param_descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);
            
            // Set parameters
            $param_nombre = $nombre;
            $param_descripcion = $descripcion;
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Set success message
                $_SESSION['success'] = "Categoría actualizada correctamente.";
                
                // Redirect to categories page using safe redirect
                safe_redirect("?module=categorias&page=index");
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
        <h2 class="text-2xl font-semibold text-gray-800">Editar Categoría</h2>
        <a href="?module=categorias&page=index" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="?module=categorias&page=edit&id=<?php echo $id; ?>" method="post">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($nombre_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $nombre; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $nombre_err; ?></span>
                </div>
                
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción (Opcional)</label>
                    <textarea name="descripcion" id="descripcion" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo $descripcion; ?></textarea>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Actualizar Categoría
                </button>
            </div>
        </form>
    </div>
</div>
