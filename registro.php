<?php
// Conexión a la base de datos
// Conexión a la base de datos
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "Rics_Racs";

$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar si la conexión es exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir los datos del formulario
    $nombre = $_POST['nombre'];
    $edad = $_POST['edad'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Validación simple
    if (empty($nombre) || empty($edad) || empty($correo) || empty($contrasena)) {
        echo "Por favor, completa todos los campos.";
    } else {
        // Hashear la contraseña
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

        // Preparar la consulta SQL para insertar los datos
        $sql = "INSERT INTO usuarios (nombre, edad, correo, contrasena) VALUES (?, ?, ?, ?)";

        // Preparar la declaración
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Asignar los parámetros a la declaración preparada
            $stmt->bind_param("siss", $nombre, $edad, $correo, $hashed_password);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo "Registro exitoso. ¡Bienvenido a Rics Racs!";
                header("Location: inicio_sesion.html");
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }

            // Cerrar la declaración
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }
}

// Cerrar la conexión
$conn->close();
?>
