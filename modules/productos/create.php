<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: modules/auth/login.php");
    exit();
}

include_once 'includes/utils.php';

// Include database connection
include_once 'config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Get categories for dropdown
$query = "SELECT id, nombre FROM categorias ORDER BY nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get suppliers for dropdown
$query = "SELECT id, nombre FROM proveedores ORDER BY nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Define variables and initialize with empty values
$nombre = $descripcion = $precio = $cantidad = $categoria_id = $proveedor_id = "";
$nombre_err = $precio_err = $cantidad_err = $categoria_id_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor ingrese el nombre del producto.";
    } else {
        $nombre = trim($_POST["nombre"]);
    }
    
    // Validate precio
    if (empty(trim($_POST["precio"]))) {
        $precio_err = "Por favor ingrese el precio.";
    } elseif (!is_numeric($_POST["precio"]) || $_POST["precio"] <= 0) {
        $precio_err = "Por favor ingrese un precio válido.";
    } else {
        $precio = trim($_POST["precio"]);
    }
    
    // Validate cantidad
    if (empty(trim($_POST["cantidad"]))) {
        $cantidad_err = "Por favor ingrese la cantidad.";
    } elseif (!is_numeric($_POST["cantidad"]) || $_POST["cantidad"] < 0) {
        $cantidad_err = "Por favor ingrese una cantidad válida.";
    } else {
        $cantidad = trim($_POST["cantidad"]);
    }
    
    // Validate categoria_id
    if (empty(trim($_POST["categoria_id"]))) {
        $categoria_id_err = "Por favor seleccione una categoría.";
    } else {
        $categoria_id = trim($_POST["categoria_id"]);
    }
    
    // Get optional fields
    $descripcion = trim($_POST["descripcion"]);
    $proveedor_id = !empty($_POST["proveedor_id"]) ? trim($_POST["proveedor_id"]) : null;
    
    // Check input errors before inserting in database
    if (empty($nombre_err) && empty($precio_err) && empty($cantidad_err) && empty($categoria_id_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO productos (nombre, descripcion, precio, cantidad, categoria_id, proveedor_id) VALUES (:nombre, :descripcion, :precio, :cantidad, :categoria_id, :proveedor_id)";
         
        if ($stmt = $db->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":nombre", $param_nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $param_descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":precio", $param_precio);
            $stmt->bindParam(":cantidad", $param_cantidad, PDO::PARAM_INT);
            $stmt->bindParam(":categoria_id", $param_categoria_id, PDO::PARAM_INT);
            $stmt->bindParam(":proveedor_id", $param_proveedor_id, PDO::PARAM_INT);
            
            // Set parameters
            $param_nombre = $nombre;
            $param_descripcion = $descripcion;
            $param_precio = $precio;
            $param_cantidad = $cantidad;
            $param_categoria_id = $categoria_id;
            $param_proveedor_id = $proveedor_id;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Set success message
                $_SESSION['success'] = "Producto creado correctamente.";
                
                // Redirect to products page using safe redirect
                safe_redirect("?module=productos&page=index");
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
        <h2 class="text-2xl font-semibold text-gray-800">Crear Nuevo Producto</h2>
        <a href="?module=productos&page=index" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="?module=productos&page=create" method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($nombre_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $nombre; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $nombre_err; ?></span>
                </div>
                
                <div>
                    <label for="precio" class="block text-sm font-medium text-gray-700 mb-1">Precio</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" step="0.01" name="precio" id="precio" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md <?php echo (!empty($precio_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $precio; ?>">
                    </div>
                    <span class="text-red-500 text-xs italic"><?php echo $precio_err; ?></span>
                </div>
                
                <div>
                    <label for="cantidad" class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                    <input type="number" name="cantidad" id="cantidad" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($cantidad_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $cantidad; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $cantidad_err; ?></span>
                </div>
                
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select name="categoria_id" id="categoria_id" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($categoria_id_err)) ? 'border-red-500' : ''; ?>">
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id']; ?>" <?php echo ($categoria_id == $categoria['id']) ? 'selected' : ''; ?>><?php echo $categoria['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="text-red-500 text-xs italic"><?php echo $categoria_id_err; ?></span>
                </div>
                
                <div>
                    <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1">Proveedor (Opcional)</label>
                    <select name="proveedor_id" id="proveedor_id" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="">Seleccione un proveedor</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['id']; ?>" <?php echo ($proveedor_id == $proveedor['id']) ? 'selected' : ''; ?>><?php echo $proveedor['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción (Opcional)</label>
                    <textarea name="descripcion" id="descripcion" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo $descripcion; ?></textarea>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>
