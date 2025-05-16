<?php
session_start();
include("includes/conexion.php");

// Variables de búsqueda y filtro
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$precio_min = isset($_GET['precio_min']) ? $_GET['precio_min'] : '';
$precio_max = isset($_GET['precio_max']) ? $_GET['precio_max'] : '';

// Configuración de paginación
$por_pagina = 12;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Construcción de la consulta SQL base
$sql_base = "SELECT * FROM coches WHERE 1";

// Aplicar filtros
if (!empty($busqueda)) {
    $sql_base .= " AND modelo LIKE '%" . $conn->real_escape_string($busqueda) . "%'";
}

if (!empty($precio_min) && !empty($precio_max)) {
    $sql_base .= " AND precio BETWEEN " . floatval($precio_min) . " AND " . floatval($precio_max);
} elseif (!empty($precio_min)) {
    $sql_base .= " AND precio >= " . floatval($precio_min);
} elseif (!empty($precio_max)) {
    $sql_base .= " AND precio <= " . floatval($precio_max);
}

// Consulta para el total de coches
$sql_total = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql_base);
$resultado_total = $conn->query($sql_total);
$total_coches = $resultado_total->fetch_assoc()['total'];
$total_paginas = ceil($total_coches / $por_pagina);

// Consulta para los coches de la página actual
$sql = $sql_base . " LIMIT $por_pagina OFFSET $offset";
$coches = $conn->query($sql);

// Verificar si hay error en la consulta
if (!$coches) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Coches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
    <style>
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1100;
        }
        .card-img-top {
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">

<!-- Toast Container (para notificaciones) -->
<div id="toastContainer" class="toast-container"></div>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-4" style="width: 250px; min-height: 100vh;">
        <div class="d-flex justify-content-center mb-4">
            <img src="./img/logo.jpg" class="img-fluid" alt="Logo empresa" style="max-width: 100px;">
        </div>
        <h5 class="text-center">Concesionario</h5>
        <hr>
        <nav class="nav flex-column">
            <a class="nav-link text-white" href="coches.php">Inicio</a>
            <a class="nav-link text-white" href="perfil.php">Perfil</a>
            <a class="nav-link text-white" href="logout.php">Cerrar sesión</a>
        </nav>
    </div>

    <!-- Contenido principal -->
    <div class="flex-fill">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></span>
                <div class="ms-auto d-flex">
                    <form class="d-flex" method="GET" action="coches.php">
                        <input type="hidden" name="pagina" value="1">
                        <input class="form-control me-2" type="search" placeholder="Buscar coche" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>">
                        <input class="form-control me-2" type="number" placeholder="Precio mínimo" name="precio_min" value="<?= htmlspecialchars($precio_min) ?>">
                        <input class="form-control me-2" type="number" placeholder="Precio máximo" name="precio_max" value="<?= htmlspecialchars($precio_max) ?>">
                        <button class="btn btn-outline-primary" type="submit">Buscar</button>
                    </form>
                    <button class="btn btn-outline-primary position-relative ms-2" id="carritoBtn" data-bs-toggle="modal" data-bs-target="#carritoModal">
                        <i class="bi bi-cart"></i> Carrito
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="carritoCantidad">0</span>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Listado de coches -->
        <div class="container py-4">
            <h3 class="mb-4">Nuestros Coches</h3>
            
            <?php if ($coches->num_rows > 0): ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php while($coche = $coches->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card shadow-sm h-100">
                                <img src="img/<?= htmlspecialchars($coche['imagen']) ?>" class="card-img-top" alt="<?= htmlspecialchars($coche['modelo']) ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($coche['modelo']) ?></h5>
                                    <p class="card-text">Precio: <?= number_format($coche['precio'], 2) ?> €</p>
                                    <button class="btn btn-success mt-auto" onclick="agregarCarrito(<?= $coche['id'] ?>)">Añadir al carrito</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No se encontraron coches con los filtros aplicados.</div>
            <?php endif; ?>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Botón Anterior -->
                        <?php if ($pagina_actual > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= 
                                    http_build_query(array_merge(
                                        $_GET,
                                        ['pagina' => $pagina_actual - 1]
                                    )) 
                                ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Números de página -->
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= 
                                    http_build_query(array_merge(
                                        $_GET,
                                        ['pagina' => $i]
                                    )) 
                                ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Botón Siguiente -->
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= 
                                    http_build_query(array_merge(
                                        $_GET,
                                        ['pagina' => $pagina_actual + 1]
                                    )) 
                                ?>">Siguiente</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal del carrito -->
<div class="modal fade" id="carritoModal" tabindex="-1" aria-labelledby="carritoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="carritoModalLabel">Detalles del Carrito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <ul id="modalCarrito" class="list-group">
                    <li class="list-group-item">El carrito está vacío</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="finalizarCompra()">Finalizar Compra</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/carrito.js"></script>
</body>
</html>