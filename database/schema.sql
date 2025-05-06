-- Database schema for Sistema de Gestión
-- MariaDB version

-- Drop database if exists
DROP DATABASE IF EXISTS sistema_gestion;

-- Create database
CREATE DATABASE sistema_gestion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE sistema_gestion;

-- Users table (Módulo 1: Gestión de Usuarios)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'basico') NOT NULL DEFAULT 'basico',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table (Módulo 4: Gestión de Categorías)
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Suppliers table (Módulo 2: Gestión de Proveedores)
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    nit VARCHAR(50),
    direccion VARCHAR(255),
    ciudad VARCHAR(100),
    telefono VARCHAR(50),
    email VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table (Módulo 3: Gestión de Productos)
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    categoria_id INT NOT NULL,
    proveedor_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
);

-- Sales table (Módulo 5: Gestión de Ventas)
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Sales details table
CREATE TABLE detalles_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
);

-- Insert default admin user
INSERT INTO usuarios (nombre, apellido, email, password, rol) 
VALUES ('Admin', 'Sistema', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample categories
INSERT INTO categorias (nombre, descripcion) VALUES 
('Electrónicos', 'Productos electrónicos y tecnología'),
('Ropa', 'Todo tipo de prendas de vestir'),
('Alimentos', 'Productos alimenticios'),
('Hogar', 'Artículos para el hogar'),
('Otros', 'Productos varios');

-- Insert sample suppliers
INSERT INTO proveedores (nombre, nit, direccion, ciudad, telefono, email) VALUES
('Electro Tech', '900123456', 'Calle 123 #45-67', 'Bogotá', '601-1234567', 'contacto@electrotech.com'),
('Moda Total', '900234567', 'Carrera 78 #90-12', 'Medellín', '604-2345678', 'info@modatotal.com'),
('Alimentos del Valle', '900345678', 'Avenida 34 #56-78', 'Cali', '602-3456789', 'ventas@alimentosdelvalle.com');

-- Insert sample products
INSERT INTO productos (nombre, descripcion, precio, cantidad, categoria_id, proveedor_id) VALUES
('Laptop HP', 'Laptop HP Pavilion 15.6" Intel Core i5', 1200.00, 10, 1, 1),
('Smartphone Samsung', 'Samsung Galaxy S21 128GB', 800.00, 15, 1, 1),
('Camiseta Básica', 'Camiseta 100% algodón', 25.00, 50, 2, 2),
('Arroz Premium', 'Arroz grano largo 1kg', 3.50, 100, 3, 3),
('Lámpara LED', 'Lámpara de escritorio LED', 45.00, 20, 4, 1);

-- Create views for reporting (Módulo 6: Reportes)
CREATE VIEW reporte_ventas AS
SELECT 
    v.id AS venta_id, 
    v.fecha_venta, 
    CONCAT(u.nombre, ' ', u.apellido) AS usuario,
    v.total,
    COUNT(dv.id) AS total_productos
FROM ventas v
JOIN usuarios u ON v.usuario_id = u.id
JOIN detalles_venta dv ON v.id = dv.venta_id
GROUP BY v.id;

CREATE VIEW reporte_productos_vendidos AS
SELECT 
    p.id AS producto_id,
    p.nombre AS producto,
    c.nombre AS categoria,
    SUM(dv.cantidad) AS cantidad_vendida,
    SUM(dv.subtotal) AS total_ventas
FROM detalles_venta dv
JOIN productos p ON dv.producto_id = p.id
JOIN categorias c ON p.categoria_id = c.id
GROUP BY p.id;

CREATE VIEW reporte_ventas_por_categoria AS
SELECT 
    c.nombre AS categoria,
    SUM(dv.subtotal) AS total_ventas,
    COUNT(DISTINCT v.id) AS numero_ventas
FROM detalles_venta dv
JOIN productos p ON dv.producto_id = p.id
JOIN categorias c ON p.categoria_id = c.id
JOIN ventas v ON dv.venta_id = v.id
GROUP BY c.id;
