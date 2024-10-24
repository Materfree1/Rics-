<?php
// Iniciar la sesión
session_start();
// Conexión a la base de datos
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "Rics_Racs";

// Crear conexión
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Comprobar si la conexión es exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Variable para almacenar el nombre del usuario, si está logueado
$nombre_usuario = null;

// Comprobar si ya hay una sesión iniciada
if (isset($_SESSION['id'])) {
    // Recuperar el nombre del usuario basado en la sesión actual
    $usuario_id = $_SESSION['id'];
    $sql = "SELECT nombre FROM usuarios WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->bind_result($nombre_usuario);
        $stmt->fetch();
        $stmt->close();
    }
} else {
    // Si no hay sesión iniciada, buscar el inicio de sesión más reciente
    $sql = "SELECT u.nombre 
            FROM inicios i 
            JOIN usuarios u ON i.usuario_id = u.id 
            WHERE i.fecha_hora >= NOW() - INTERVAL 1 MINUTE 
            ORDER BY i.id DESC 
            LIMIT 1";
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre_usuario = $row['nombre'];
    }
}

// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rics Racs - Inicio</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600&display=swap');

        body {
            font-family: 'Work Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #0e0b0b;
            color: #ffffff;
        }

        nav {
            background-color: rgba(14, 11, 11, 0.8);
            padding: 15px 40px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 10;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
        }

        nav.scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo img {
            width: 80px;
            display: block;
            border-radius: 40%;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .logo:hover img {
            transform: scale(1.05);
        }

        .user-session {
            display: flex;
            align-items: center;
            color: #fff;
            font-weight: 600;
            position: relative;
            cursor: pointer;
        }

        .user-session img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: 60px;
            right: 0;
            background-color: #ffffff;
            color: #333;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            z-index: 10;
        }

        .user-session:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 5px 0;
        }

        .user-dropdown a:hover {
            color: #ff6600;
        }

        .logout-button, .login-button {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover, .login-button:hover {
            background-color: #e65c00;
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .menu-toggle div {
            width: 25px;
            height: 3px;
            background-color: #dad5d5;
            margin: 4px 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-links a {
            color: #e7e3e3;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 600;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            display: block;
            width: 0;
            height: 2px;
            background: #ff6600;
            transition: width 0.3s;
            position: absolute;
            bottom: -5px;
            left: 0;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 70px;
                right: 20px;
                background-color: rgba(14, 11, 11, 0.9);
                flex-direction: column;
                width: 200px;
                padding: 20px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
            }

            .nav-links a {
                margin: 10px 0;
                text-align: center;
            }

            .menu-toggle {
                display: flex;
            }

            .nav-links.active {
                display: flex;
            }
        }

        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: 0;
            transform: translate(-50%, -50%);
            object-fit: cover;
            opacity: 0.7;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
            max-width: 800px;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: 3em;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        .cta-button {
            padding: 12px 40px;
            background-color: #ff6600;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s ease;
            display: inline-block;
        }

        .cta-button:hover {
            background-color: #e65c00;
        }
    </style>
</head>

<body>

    <nav id="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="Imagenes/Logo.jpg" alt="Rics Racs Logo">
            </div>
            <div class="menu-toggle" id="mobile-menu">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="nav-links">
                <a href="index.php">Inicio</a>
                <a href="productos.php">Productos</a>
                <a href="instalacion.html">Instalación</a>
                <a href="garantia.html">Garantía</a>
                <a href="contacto.html">Contacto</a>
            </div>
            <?php if ($nombre_usuario): ?>
                <div class="user-session">
                    <img src="Imagenes/icon.png" alt="User Icon">
                    <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
                    <div class="user-dropdown">
                        <a href="Carrito.php">Carrito</a>
                        <form action="logout.php" method="POST">
                            <button type="submit" class="logout-button">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="inicio_sesion.html" class="login-button">Acceder</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <video autoplay muted loop>
            <source src="Imagenes/Fondo.mp4" type="video/mp4">
            Tu navegador no soporta la etiqueta de video.
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Canastillas de Calidad para Tu Aventura</h1>
            <p>Maximiza el espacio de carga de tu vehículo con nuestras canastillas premium</p>
            <a href="productos.html" class="cta-button">Ver Productos</a>
        </div>
    </header>

    <script>
    const navbar = document.getElementById('navbar');
    const mobileMenu = document.getElementById('mobile-menu');
    const navLinks = document.querySelector('.nav-links');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    mobileMenu.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    const userSession = document.querySelector('.user-session');
    const userDropdown = document.querySelector('.user-dropdown');
    let hideTimeout;

    if (userSession) {
        userSession.addEventListener('mouseenter', function() {
            clearTimeout(hideTimeout);
            userDropdown.style.display = 'block';
        });

        userSession.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(function() {
                userDropdown.style.display = 'none';
            }, 5000);
        });

        userDropdown.addEventListener('mouseenter', function() {
            clearTimeout(hideTimeout);
        });

        userDropdown.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(function() {
                userDropdown.style.display = 'none';
            }, 5000);
        });
    }
    </script>

</body>

</html>