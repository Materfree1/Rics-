<?php
// Iniciar la sesión
session_start();
// Conexión a la base de datos
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "Rics_Racs";


$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Comprobar si la conexión es exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y sanitizar los datos del formulario
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);

    // Validación simple
    if (empty($correo) || empty($contrasena)) {
        $error = "Por favor, completa todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
        // Preparar la consulta SQL para obtener el usuario
        $sql = "SELECT id, nombre, contrasena FROM usuarios WHERE correo = ?";

        // Preparar la declaración
        if ($stmt = $conn->prepare($sql)) {
            // Asignar el correo como parámetro
            $stmt->bind_param("s", $correo);

            // Ejecutar la consulta
            $stmt->execute();

            // Obtener el resultado
            $stmt->store_result();

            // Verificar si se encontró un usuario con el correo
            if ($stmt->num_rows == 1) {
                // Asociar los datos
                $stmt->bind_result($id, $nombre, $hashed_password);
                $stmt->fetch();

                // Verificar la contraseña
                if (password_verify($contrasena, $hashed_password)) {
                    // Crear sesión para el usuario
                    $_SESSION['id'] = $id;
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['correo'] = $correo;

                    // Opcional: Manejar la opción "Recuérdame"
                    if (isset($_POST['remember'])) {
                        // Implementa la funcionalidad "Recuérdame" aquí si lo deseas
                    }

                    // Redirigir a la página principal (index2.html)
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "La contraseña es incorrecta.";
                }
            } else {
                $error = "No existe una cuenta con ese correo.";
            }

            // Cerrar la declaración
            $stmt->close();
        } else {
            $error = "Error en la preparación de la consulta: " . $conn->error;
        }
    }
}

// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error de Inicio de Sesión</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .error-container {
            background-color: rgba(255, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
        }
        .error-container h2 {
            margin-bottom: 20px;
        }
        .error-container a {
            color: #ff6600;
            text-decoration: none;
            font-weight: bold;
        }
        .error-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php if(isset($error)): ?>
        <div class="error-container">
            <h2>Error de Inicio de Sesión</h2>
            <p><?php echo htmlspecialchars($error); ?></p>
            <a href="../login.html">Volver al Inicio de Sesión</a>
        </div>
    <?php endif; ?>
</body>
</html>
