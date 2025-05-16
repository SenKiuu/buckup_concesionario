<?php
session_start();
include("includes/conexion.php");

$mensaje = "";
$showLogin = false;
$showRegister = false;

// Mostrar formularios según parámetro
if (isset($_GET['form'])) {
    if ($_GET['form'] == 'login') {
        $showLogin = true;
    } elseif ($_GET['form'] == 'register') {
        $showRegister = true;
    }
}

// Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuarios WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION["usuario"] = $usuario;

        if ($usuario["rol"] == "admin") {
            header("Location: admin.php");
        } else {
            header("Location: tienda.php");
        }
        exit();
    } else {
        $mensaje = "Usuario o contraseña incorrectos.";
        $showLogin = true;
    }
}

// Registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $rol = $_POST["rol"];

    // Verificar si el correo ya está registrado
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $mensaje = "Este correo electrónico ya está registrado.";
        $showRegister = true;
    } else {
        // Insertar el nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
        if ($stmt->execute()) {
            $mensaje = "Registro exitoso. Ahora puedes iniciar sesión.";
            $showLogin = true;
        } else {
            $mensaje = "Error al registrar el usuario.";
            $showRegister = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionario Premium</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
        }
        
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1494972308805-463bc619d34e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2073&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            color: white;
        }
        
        .hero-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .hero-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }
        
        .btn-premium {
            background: var(--secondary-color);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
        }
        
        .btn-premium:hover {
            background: var(--accent-color);
            transform: translateY(-3px);
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 30px;
            color: #333;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .form-control {
            border-radius: 50px;
            padding: 12px 20px;
            border: 2px solid #eee;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
        }
        
        .logo span {
            color: var(--secondary-color);
        }
        
        .feature-icon {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="d-flex flex-column">

<!-- Hero Section -->
<div class="container my-auto py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h1 class="logo mb-4 animate__animated animate__fadeInDown">
                <i class="fas fa-car me-2"></i>Concesionario <span>Xuri</span>
            </h1>
            <p class="lead mb-5 animate__animated animate__fadeIn animate__delay-1s">
                Descubre la excelencia automotriz. Los mejores vehículos al mejor precio.
            </p>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-info animate__animated animate__fadeIn">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <!-- Mostrar botones si no hay formulario activo -->
            <?php if (!$showLogin && !$showRegister): ?>
                <div class="d-flex justify-content-center gap-4 animate__animated animate__fadeInUp animate__delay-1s">
                    <a href="?form=login" class="btn btn-premium btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </a>
                    <a href="?form=register" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Registrarse
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de Login -->
            <?php if ($showLogin): ?>
                <div class="form-container animate__animated animate__fadeIn mx-auto" style="max-width: 500px;">
                    <h3 class="text-center mb-4"><i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión</h3>
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" name="login" class="btn btn-premium">
                                <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                            </button>
                        </div>
                        <div class="text-center">
                            <a href="?" class="text-muted"><i class="fas fa-arrow-left me-2"></i>Volver</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de Registro -->
            <?php if ($showRegister): ?>
                <div class="form-container animate__animated animate__fadeIn mx-auto" style="max-width: 500px;">
                    <h3 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i>Crear Cuenta</h3>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label for="rol" class="form-label">Tipo de Cuenta</label>
                            <select name="rol" id="rol" class="form-select" required>
                                <option value="usuario">Usuario Normal</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" name="register" class="btn btn-premium">
                                <i class="fas fa-user-plus me-2"></i>Registrarse
                            </button>
                        </div>
                        <div class="text-center">
                            <a href="?" class="text-muted"><i class="fas fa-arrow-left me-2"></i>Volver</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-4 mt-auto">
    <div class="container">
        <p class="mb-0">
            &copy; 2025 Concesionario Xuri. Todos los derechos reservados.
        </p>
        <div class="mt-2">
            <a href="#" class="text-white mx-2"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="text-white mx-2"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-white mx-2"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Efecto de escritura para el título
    document.addEventListener('DOMContentLoaded', function() {
        const title = document.querySelector('.logo');
        title.classList.add('animate__animated', 'animate__fadeInDown');
    });
</script>
</body>
</html>