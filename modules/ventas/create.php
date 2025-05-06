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

// Verificar que el usuario existe en la base de datos
$user_check_query = "SELECT id FROM usuarios WHERE id = ?";
$user_check_stmt = $db->prepare($user_check_query);
$user_check_stmt->execute([$_SESSION['user_id']]);
if ($user_check_stmt->rowCount() == 0) {
    $_SESSION['error'] = "Error de sesión: Usuario no encontrado en la base de datos.";
    safe_redirect("modules/auth/logout.php");
    exit();
}

// Get all products for selection
$query = "SELECT p.id, p.nombre, p.precio, p.cantidad, c.nombre as categoria 
          FROM productos p 
          JOIN categorias c ON p.categoria_id = c.id 
          WHERE p.cantidad > 0 
          ORDER BY p.nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if products are selected
    if (isset($_POST['producto_id']) && is_array($_POST['producto_id']) && count($_POST['producto_id']) > 0) {
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Calculate total
            $total = 0;
            foreach ($_POST['producto_id'] as $key => $producto_id) {
                if (!empty($_POST['cantidad'][$key]) && $_POST['cantidad'][$key] > 0) {
                    $total += $_POST['precio'][$key] * $_POST['cantidad'][$key];
                }
            }
            
            // Insert sale
            $query = "INSERT INTO ventas (usuario_id, total) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$_SESSION['user_id'], $total]);
            
            // Get last inserted ID
            $venta_id = $db->lastInsertId();
            
            // Insert sale details
            $query = "INSERT INTO detalles_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            // Update product quantities
            $update_query = "UPDATE productos SET cantidad = cantidad - ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            
            foreach ($_POST['producto_id'] as $key => $producto_id) {
                if (!empty($_POST['cantidad'][$key]) && $_POST['cantidad'][$key] > 0) {
                    $cantidad = $_POST['cantidad'][$key];
                    $precio = $_POST['precio'][$key];
                    $subtotal = $precio * $cantidad;
                    
                    // Insert sale detail
                    $stmt->execute([$venta_id, $producto_id, $cantidad, $precio, $subtotal]);
                    
                    // Update product quantity
                    $update_stmt->execute([$cantidad, $producto_id]);
                }
            }
            
            // Commit transaction
            $db->commit();
            
            // Set success message
            $_SESSION['success'] = "Venta registrada correctamente.";
            
            // Redirect to sales page using safe redirect
            safe_redirect("?module=ventas&page=index");
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollBack();
            $_SESSION['error'] = "Error al registrar la venta: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Debe seleccionar al menos un producto.";
    }
}
?>

<div class="container px-6 py-8 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Registrar Nueva Venta</h2>
        <a href="?module=ventas&page=index" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 flash-message" role="alert">
        <p><?php echo $_SESSION['error']; ?></p>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="?module=ventas&page=create" method="post" id="ventaForm">
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Productos</h3>
                    <button type="button" id="addProductBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Añadir Producto
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="productosTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="productosBody">
                            <!-- Product rows will be added here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right font-bold">Total:</td>
                                <td class="px-6 py-4 font-bold text-green-600" id="totalVenta">$0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Registrar Venta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Product Selection Modal -->
<div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Seleccionar Producto</h3>
            <button type="button" id="closeModalBtn" class="text-gray-400 hover:text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="mb-4">
            <input type="text" id="searchProduct" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Buscar producto...">
        </div>
        
        <div class="overflow-y-auto max-h-80">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="productList">
                    <?php foreach ($productos as $producto): ?>
                    <tr data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo $producto['nombre']; ?>" data-precio="<?php echo $producto['precio']; ?>" data-stock="<?php echo $producto['cantidad']; ?>">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $producto['nombre']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $producto['categoria']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo number_format($producto['precio'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $producto['cantidad']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="selectProductBtn text-blue-600 hover:text-blue-900">
                                Seleccionar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay productos disponibles.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addProductBtn = document.getElementById('addProductBtn');
    const productModal = document.getElementById('productModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const searchProduct = document.getElementById('searchProduct');
    const productList = document.getElementById('productList');
    const productosBody = document.getElementById('productosBody');
    const totalVenta = document.getElementById('totalVenta');
    
    // Show modal
    addProductBtn.addEventListener('click', function() {
        productModal.classList.remove('hidden');
    });
    
    // Close modal
    closeModalBtn.addEventListener('click', function() {
        productModal.classList.add('hidden');
    });
    
    // Close modal when clicking outside
    productModal.addEventListener('click', function(e) {
        if (e.target === productModal) {
            productModal.classList.add('hidden');
        }
    });
    
    // Search products
    searchProduct.addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const rows = productList.querySelectorAll('tr');
        
        rows.forEach(row => {
            const productName = row.getAttribute('data-nombre') ? row.getAttribute('data-nombre').toLowerCase() : '';
            if (productName.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Select product
    document.querySelectorAll('.selectProductBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const id = row.getAttribute('data-id');
            const nombre = row.getAttribute('data-nombre');
            const precio = parseFloat(row.getAttribute('data-precio'));
            const stock = parseInt(row.getAttribute('data-stock'));
            
            // Check if product is already added
            const existingProduct = document.querySelector(`#productosBody tr[data-id="${id}"]`);
            if (existingProduct) {
                alert('Este producto ya está en la lista.');
                return;
            }
            
            // Add product to table
            addProductToTable(id, nombre, precio, stock);
            
            // Close modal
            productModal.classList.add('hidden');
        });
    });
    
    // Add product to table
    function addProductToTable(id, nombre, precio, stock) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', id);
        
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${nombre}
                <input type="hidden" name="producto_id[]" value="${id}">
                <input type="hidden" name="precio[]" value="${precio}">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                $${precio.toFixed(2)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <input type="number" name="cantidad[]" min="1" max="${stock}" value="1" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-20 sm:text-sm border-gray-300 rounded-md cantidad-input">
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 subtotal">
                $${precio.toFixed(2)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button type="button" class="text-red-600 hover:text-red-900 remove-product">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        `;
        
        productosBody.appendChild(tr);
        
        // Add event listeners
        const cantidadInput = tr.querySelector('.cantidad-input');
        cantidadInput.addEventListener('change', function() {
            updateSubtotal(tr);
            updateTotal();
        });
        
        const removeBtn = tr.querySelector('.remove-product');
        removeBtn.addEventListener('click', function() {
            tr.remove();
            updateTotal();
        });
        
        updateTotal();
    }
    
    // Update subtotal for a row
    function updateSubtotal(row) {
        const precio = parseFloat(row.querySelector('input[name="precio[]"]').value);
        const cantidad = parseInt(row.querySelector('input[name="cantidad[]"]').value);
        const subtotal = precio * cantidad;
        row.querySelector('.subtotal').textContent = `$${subtotal.toFixed(2)}`;
    }
    
    // Update total
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('#productosBody tr').forEach(row => {
            const precio = parseFloat(row.querySelector('input[name="precio[]"]').value);
            const cantidad = parseInt(row.querySelector('input[name="cantidad[]"]').value);
            total += precio * cantidad;
        });
        
        totalVenta.textContent = `$${total.toFixed(2)}`;
    }
});
</script>
