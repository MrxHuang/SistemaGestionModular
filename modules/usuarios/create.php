<?php
// Check if user is logged in and is admin
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

// Define variables and initialize with empty values
$nombre = $apellido = $email = $password = $confirm_password = $rol = "";
$nombre_err = $apellido_err = $email_err = $password_err = $confirm_password_err = $rol_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor ingrese el nombre.";
    } else {
        $nombre = trim($_POST["nombre"]);
    }
    
    // Validate apellido
    if (empty(trim($_POST["apellido"]))) {
        $apellido_err = "Por favor ingrese el apellido.";
    } else {
        $apellido = trim($_POST["apellido"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor ingrese el email.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        
        if ($stmt = $db->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            
            // Set parameters
            $param_email = trim($_POST["email"]);
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $email_err = "Este email ya está en uso.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            
            // Close statement
            unset($stmt);
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese una contraseña.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Por favor confirme la contraseña.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }
    
    // Validate rol
    if (empty(trim($_POST["rol"]))) {
        $rol_err = "Por favor seleccione un rol.";
    } else {
        $rol = trim($_POST["rol"]);
    }
    
    // Check input errors before inserting in database
    if (empty($nombre_err) && empty($apellido_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($rol_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (:nombre, :apellido, :email, :password, :rol)";
         
        if ($stmt = $db->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":nombre", $param_nombre, PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $param_apellido, PDO::PARAM_STR);
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":rol", $param_rol, PDO::PARAM_STR);
            
            // Set parameters
            $param_nombre = $nombre;
            $param_apellido = $apellido;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_rol = $rol;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Set success message
                $_SESSION['success'] = "Usuario creado correctamente.";
                
                // Redirect to users page using safe redirect
                safe_redirect("?module=usuarios&page=index");
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
        <h2 class="text-2xl font-semibold text-gray-800">Crear Nuevo Usuario</h2>
        <a href="?module=usuarios&page=index" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="?module=usuarios&page=create" method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($nombre_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $nombre; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $nombre_err; ?></span>
                </div>
                
                <div>
                    <label for="apellido" class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text" name="apellido" id="apellido" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($apellido_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $apellido; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $apellido_err; ?></span>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $email_err; ?></span>
                </div>
                
                <div>
                    <label for="rol" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="rol" id="rol" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($rol_err)) ? 'border-red-500' : ''; ?>">
                        <option value="">Seleccione un rol</option>
                        <option value="admin" <?php echo ($rol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="basico" <?php echo ($rol === 'basico') ? 'selected' : ''; ?>>Básico</option>
                    </select>
                    <span class="text-red-500 text-xs italic"><?php echo $rol_err; ?></span>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" id="password" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $password_err; ?></span>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md <?php echo (!empty($confirm_password_err)) ? 'border-red-500' : ''; ?>">
                    <span class="text-red-500 text-xs italic"><?php echo $confirm_password_err; ?></span>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
