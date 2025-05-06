<!-- Sidebar -->
<div class="bg-blue-800 text-white w-64 flex-shrink-0 sidebar">
    <div class="p-4 border-b border-blue-700">
        <div class="flex items-center">
            <div class="mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2H5v-2h10zM7 10h2v2H7v-2zm6-3h-2v2h2V7z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold">Sistema de Gestión</h1>
                <p class="text-xs text-blue-300">Panel de Administración</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Links -->
    <nav class="mt-5">
        <a href="index.php" class="flex items-center py-3 px-4 text-white hover:bg-blue-700 <?php echo $current_module === 'dashboard' ? 'bg-blue-700' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>
        <a href="?module=productos&page=index" class="flex items-center py-3 px-4 text-white hover:bg-blue-700 <?php echo $current_module === 'productos' ? 'bg-blue-700' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
            </svg>
            Productos
        </a>
        <a href="?module=categorias&page=index" class="flex items-center py-3 px-4 text-white hover:bg-blue-700 <?php echo $current_module === 'categorias' ? 'bg-blue-700' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Categorías
        </a>
        <a href="?module=proveedores&page=index" class="flex items-center py-3 px-4 text-white hover:bg-blue-700 <?php echo $current_module === 'proveedores' ? 'bg-blue-700' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Proveedores
        </a>
        <a href="?module=ventas&page=index" class="flex items-center py-3 px-4 text-white hover:bg-blue-700 <?php echo $current_module === 'ventas' ? 'bg-blue-700' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Ventas
        </a>
        <a href="?module=reportes&page=index" class="flex items-center py-3 px-4 text-white hover:bg-blue-700 <?php echo $current_module === 'reportes' ? 'bg-blue-700' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Reportes
        </a>
        <?php if (isset($user) && is_array($user) && array_key_exists('rol', $user) && $user['rol'] === 'admin'): ?>
        <a href="?module=usuarios&page=index" class="flex items-center py-3 px-4 text-white hover:bg-blue-700 <?php echo $current_module === 'usuarios' ? 'bg-blue-700' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Usuarios
        </a>
        <?php endif; ?>
        
        <!-- Botón de Cierre de Sesión destacado -->
        <a href="modules/auth/logout.php" class="flex items-center py-3 px-4 text-white bg-red-700 hover:bg-red-800 mt-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Cerrar Sesión
        </a>
    </nav>
    
    <!-- User Profile -->
    <?php if (isset($user) && is_array($user)): ?>
    <div class="absolute bottom-0 w-64 bg-blue-900">
        <div class="p-4 flex items-center">
            <div class="w-10 h-10 rounded-full bg-blue-700 flex items-center justify-center text-lg font-semibold mr-3">
                <?php 
                    $nombre_inicial = array_key_exists('nombre', $user) ? strtoupper(substr($user['nombre'], 0, 1)) : '';
                    $apellido_inicial = array_key_exists('apellido', $user) ? strtoupper(substr($user['apellido'], 0, 1)) : '';
                    echo $nombre_inicial . $apellido_inicial; 
                ?>
            </div>
            <div>
                <p class="font-medium">
                    <?php 
                        $nombre_completo = '';
                        if(array_key_exists('nombre', $user)) $nombre_completo .= $user['nombre'];
                        if(array_key_exists('apellido', $user)) $nombre_completo .= ' ' . $user['apellido'];
                        echo $nombre_completo; 
                    ?>
                </p>
                <p class="text-xs text-blue-300"><?php echo array_key_exists('rol', $user) ? ucfirst($user['rol']) : ''; ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Main Content -->
<div class="flex-1 content overflow-y-auto">
    <header class="bg-white shadow-sm">
        <div class="flex justify-between items-center px-6 py-3">
            <h2 class="text-xl font-semibold text-gray-800">
                <?php
                switch ($current_module) {
                    case 'dashboard':
                        echo 'Dashboard';
                        break;
                    case 'productos':
                        echo 'Gestión de Productos';
                        break;
                    case 'categorias':
                        echo 'Gestión de Categorías';
                        break;
                    case 'proveedores':
                        echo 'Gestión de Proveedores';
                        break;
                    case 'ventas':
                        echo 'Gestión de Ventas';
                        break;
                    case 'reportes':
                        echo 'Reportes';
                        break;
                    case 'usuarios':
                        echo 'Gestión de Usuarios';
                        break;
                    default:
                        echo 'Sistema de Gestión';
                }
                ?>
            </h2>
            <div class="flex items-center">
                <div class="text-sm text-gray-500 mr-4">
                    <?php echo date('l, d M Y'); ?>
                </div>
                <a href="modules/auth/logout.php" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-1 px-3 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Salir
                </a>
            </div>
        </div>
    </header>
