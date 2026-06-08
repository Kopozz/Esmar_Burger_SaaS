<?php
/**
 * ESMAR BURGER - Inicializador de Base de Datos
 * Avance 2 - Ingeniería Web
 */

// Asegurarse de que config.php esté cargado pero evitar bucle infinito
$dbFile = __DIR__ . '/database/esmar_burger.db';
$dbDir = dirname($dbFile);
if (!file_exists($dbDir)) {
    mkdir($dbDir, 0777, true);
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla de usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        rol TEXT CHECK(rol IN ('admin', 'cliente')) NOT NULL DEFAULT 'cliente',
        telefono TEXT,
        direccion TEXT,
        fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Crear tabla de productos (platos)
    $pdo->exec("CREATE TABLE IF NOT EXISTS productos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        descripcion TEXT,
        precio REAL NOT NULL,
        categoria TEXT NOT NULL,
        imagen TEXT
    )");

    // Crear tabla de pedidos
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedidos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER,
        total REAL NOT NULL,
        direccion_entrega TEXT NOT NULL,
        telefono_contacto TEXT NOT NULL,
        metodo_pago TEXT NOT NULL,
        estado TEXT CHECK(estado IN ('pendiente', 'preparando', 'en camino', 'entregado')) NOT NULL DEFAULT 'pendiente',
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )");

    // Crear tabla de detalles de pedido
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedido_detalles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pedido_id INTEGER,
        producto_id INTEGER,
        cantidad INTEGER NOT NULL,
        precio_unitario REAL NOT NULL,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
        FOREIGN KEY (producto_id) REFERENCES productos(id)
    )");

    // Semilla: Insertar usuarios por defecto si no existen
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $adminPassword = password_hash('admin', PASSWORD_DEFAULT);
        $clientePassword = password_hash('cliente', PASSWORD_DEFAULT);

        $pdo->exec("INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion) VALUES 
            ('Administrador Esmar', 'admin@esmarburger.com', '$adminPassword', 'admin', '935550240', 'Av. Central 123'),
            ('Juan Pérez', 'cliente@gmail.com', '$clientePassword', 'cliente', '921157440', 'Calle Las Flores 456')
        ");
    }

    // Semilla: Insertar productos (Menu) si no existen
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        // Hamburguesas
        $productos = [
            // Hamburguesas
            ['nombre' => 'Hawaianda', 'descripcion' => 'Carne + queso + jamón + piña + lechuga + tomate + papas fritas', 'precio' => 12.00, 'categoria' => 'Hamburguesas', 'imagen' => 'hawaiana.jpg'],
            ['nombre' => 'Americana', 'descripcion' => 'Carne + queso + jamón + lechuga + tomate + papas fritas', 'precio' => 10.00, 'categoria' => 'Hamburguesas', 'imagen' => 'americana.jpg'],
            ['nombre' => 'A lo Pobre', 'descripcion' => 'Maduro + carne + huevo + lechuga + tomate + papas fritas', 'precio' => 9.00, 'categoria' => 'Hamburguesas', 'imagen' => 'pobre.jpg'],
            ['nombre' => 'Cheese', 'descripcion' => 'Carne + queso edam + lechuga + tomate + papas fritas', 'precio' => 7.00, 'categoria' => 'Hamburguesas', 'imagen' => 'cheese.jpg'],
            ['nombre' => 'Royal', 'descripcion' => 'Carne + huevo + lechuga + tomate + papas fritas', 'precio' => 7.00, 'categoria' => 'Hamburguesas', 'imagen' => 'royal.jpg'],
            ['nombre' => 'Clásica Burger', 'descripcion' => 'Carne + lechuga + tomate + papas fritas', 'precio' => 6.00, 'categoria' => 'Hamburguesas', 'imagen' => 'clasica.jpg'],

            // Broaster
            ['nombre' => '1/4 de Broaster', 'descripcion' => '2 piezas de broaster + guarnición a elegir + ensalada', 'precio' => 18.00, 'categoria' => 'Broaster', 'imagen' => 'un_cuarto.jpg'],
            ['nombre' => '1/8 de Broaster', 'descripcion' => 'Broaster + ensalada + guarnición a elegir', 'precio' => 10.00, 'categoria' => 'Broaster', 'imagen' => 'un_octavo.jpg'],
            ['nombre' => 'Mostrito', 'descripcion' => 'Broaster + chaufa + guarnición a elegir + ensalada', 'precio' => 12.00, 'categoria' => 'Broaster', 'imagen' => 'mostrito.jpg'],

            // Salchipapas
            ['nombre' => 'Salchipapa Clásica', 'descripcion' => 'Salchicha + papa + ensalada', 'precio' => 7.00, 'categoria' => 'Salchipapas', 'imagen' => 'salchi_clasica.jpg'],
            ['nombre' => 'Choripapa', 'descripcion' => 'Chorizo parrillero + papa + ensalada', 'precio' => 8.00, 'categoria' => 'Salchipapas', 'imagen' => 'choripapa.jpg'],
            ['nombre' => 'Salchipapa Especial', 'descripcion' => 'Salchicha + papa + huevo + chorizo parrillero', 'precio' => 10.00, 'categoria' => 'Salchipapas', 'imagen' => 'salchi_especial.jpg'],
            ['nombre' => 'Salchibroaster', 'descripcion' => 'Salchicha + papa + ensalada + broaster', 'precio' => 11.00, 'categoria' => 'Salchipapas', 'imagen' => 'salchibroaster.jpg'],
            ['nombre' => 'Salchisuprema', 'descripcion' => 'Papa + chaufa + salchicha + chorizo parrillero + huevo + tiras de pollo frito + ensalada', 'precio' => 18.00, 'categoria' => 'Salchipapas', 'imagen' => 'salchisuprema.jpg'],

            // Combos
            ['nombre' => 'Combo Patas', 'descripcion' => '2 choriburgers + porcion de papas + 2 gaseosas', 'precio' => 25.00, 'categoria' => 'Combos', 'imagen' => 'combo_patas.jpg'],
            ['nombre' => 'Combo Supremo', 'descripcion' => '2 hamburguesa a lo pobre + salchipapa + una pieza de broaster', 'precio' => 30.00, 'categoria' => 'Combos', 'imagen' => 'combo_supremo.jpg']
        ];

        $stmtInsert = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria, imagen) VALUES (:nombre, :descripcion, :precio, :categoria, :imagen)");
        foreach ($productos as $p) {
            $stmtInsert->execute($p);
        }
    }

} catch (PDOException $e) {
    error_log("Error al inicializar la base de datos: " . $e->getMessage());
}
?>
