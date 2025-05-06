<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Include database connection
include_once '../../config/database.php';

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor ingrese su email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // Create database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Prepare a select statement
        $sql = "SELECT id, nombre, apellido, email, password, rol FROM usuarios WHERE email = :email";
        
        if ($stmt = $db->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
            
            // Set parameters
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if email exists, if yes then verify password
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["id"];
                        $nombre = $row["nombre"];
                        $apellido = $row["apellido"];
                        $email = $row["email"];
                        $hashed_password = $row["password"];
                        $rol = $row["rol"];
                        
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["nombre"] = $nombre;
                            $_SESSION["apellido"] = $apellido;
                            $_SESSION["email"] = $email;
                            $_SESSION["rol"] = $rol;
                            
                            // Redirect user to dashboard
                            header("location: ../../index.php");
                            exit();
                        } else {
                            // Password is not valid
                            $login_err = "Email o contraseña incorrectos.";
                        }
                    }
                } else {
                    // Email doesn't exist
                    $login_err = "Email o contraseña incorrectos.";
                }
            } else {
                $login_err = "Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            
            // Close statement
            unset($stmt);
        }
        
        // Close connection
        unset($db);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-blue-800 text-white py-4 px-6">
                <div class="flex items-center justify-center">
                    <div class="mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2H5v-2h10zM7 10h2v2H7v-2zm6-3h-2v2h2V7z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Sistema de Gestión</h1>
                        <p class="text-sm text-blue-300">Iniciar Sesión</p>
                    </div>
                </div>
            </div>
            
            <div class="py-6 px-8">
                <?php 
                if (!empty($login_err)) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">' . $login_err . '</div>';
                }        
                ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $email; ?>">
                        <span class="text-red-500 text-xs italic"><?php echo $email_err; ?></span>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
                        <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>">
                        <span class="text-red-500 text-xs italic"><?php echo $password_err; ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-800 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
                
                <p class="text-center text-gray-500 text-xs mt-6">
                    &copy; 2025 Sistema de Gestión. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
