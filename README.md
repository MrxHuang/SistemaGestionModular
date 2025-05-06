# Sistema de Gestión Modular

Un sistema de gestión completo con arquitectura modular desarrollado en PHP y MariaDB, con una interfaz moderna usando Tailwind CSS.

## Descripción

Este sistema de gestión incluye los siguientes módulos:

1. **Módulo Principal: Autenticación de Usuario (Login)**
   - Controla el acceso a los demás módulos
   - Solo usuarios autenticados pueden interactuar con el sistema
   - Login por email y clave

2. **Módulo 1: Gestión de Usuarios**
   - Registrar, actualizar, eliminar y listar usuarios
   - Rol de cada usuario (Admin o Básico)

3. **Módulo 2: Gestión de Proveedores**
   - CRUD de proveedores con nombre, nit, dirección, ciudad, teléfono, email

4. **Módulo 3: Gestión de Productos**
   - CRUD de productos con nombre, precio, cantidad y categoría

5. **Módulo 4: Gestión de Categorías**
   - CRUD de categorías asociadas a los productos

6. **Módulo 5: Gestión de Ventas**
   - Registro de ventas por usuario autenticado
   - Asociación a productos

7. **Módulo 6: Reportes**
   - Generación de reportes por rango de fechas
   - Resumen de ventas y productos vendidos
   - Gráficos de ventas por categoría y productos más vendidos

## Requisitos

- PHP 7.4 o superior
- MariaDB/MySQL
- Servidor web (Apache, Nginx, etc.)
- XAMPP (recomendado para desarrollo local)

## Instalación

1. Clona o descarga este repositorio en tu directorio de servidor web (por ejemplo, `htdocs` en XAMPP)
2. Importa la base de datos desde el archivo `database/schema.sql` a tu servidor MariaDB/MySQL
3. Configura los parámetros de conexión a la base de datos en `config/database.php` si es necesario
4. Accede al sistema a través de tu navegador web
5. Alternativamente, puedes ejecutar `init_db.php` para crear la base de datos automáticamente
6. Para verificar que todo esté correctamente configurado, ejecuta `check_system.php`

## Credenciales por defecto

- **Email**: admin@sistema.com
- **Contraseña**: password

## Estructura del proyecto

```
Modular/
├── assets/           # Archivos estáticos (CSS, JS, imágenes)
├── config/           # Configuración del sistema
│   ├── config.php    # Configuración global
│   └── database.php  # Configuración de la base de datos
├── database/         # Scripts SQL
├── includes/         # Archivos de inclusión (header, footer, sidebar)
├── logs/             # Registros de errores
├── modules/          # Módulos del sistema
│   ├── auth/         # Autenticación
│   ├── categorias/   # Gestión de categorías
│   ├── dashboard/    # Panel principal
│   ├── productos/    # Gestión de productos
│   ├── proveedores/  # Gestión de proveedores
│   ├── reportes/     # Generación de reportes
│   ├── usuarios/     # Gestión de usuarios
│   └── ventas/       # Gestión de ventas
├── .htaccess         # Configuración de seguridad Apache
├── 403.php           # Página de error 403 (Acceso prohibido)
├── 404.php           # Página de error 404 (No encontrado)
├── check_system.php  # Verificador de configuración del sistema
├── init_db.php       # Inicializador de base de datos
└── index.php         # Punto de entrada principal
```

## Características

- Interfaz de usuario moderna con Tailwind CSS
- Sistema de autenticación seguro
- Gestión completa de productos, categorías y proveedores
- Sistema de ventas con cálculo automático de totales
- Reportes detallados con gráficos usando Chart.js
- Diseño responsivo para diferentes dispositivos
- Validación de formularios
- Mensajes de retroalimentación para el usuario
- Páginas de error personalizadas
- Configuración centralizada
- Verificador de sistema para diagnóstico
- Protección contra acceso directo a archivos sensibles
- Manejo de sesiones seguras
- Funciones de sanitización de datos

## Seguridad

El sistema implementa varias medidas de seguridad:

- Contraseñas almacenadas con hash seguro
- Protección contra inyección SQL mediante consultas preparadas
- Validación de datos de entrada
- Control de acceso basado en roles
- Protección contra listado de directorios
- Configuración de seguridad en .htaccess
- Manejo de errores personalizado

## Próximas mejoras

- Implementación de paginación para grandes conjuntos de datos
- Subida de imágenes para productos
- Implementación de carrito de compras para el módulo de ventas
- Exportación de reportes a PDF y Excel
- Sistema de notificaciones
- Registro de actividad de usuarios

## Licencia

Este proyecto está bajo la Licencia MIT.

## Desarrolladores

- Juan Jose Pantoja y Luis
- John Stiven Muñoz y Juan Jose Ospina
- Emmanuel y Santiago
- Juan David Gaitan y Nayely
- Lizeth Mariana y Kennyan
- Camilo Agudelo
