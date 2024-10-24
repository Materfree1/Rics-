<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "Rics_Racs";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Resto del código...


$nombre_usuario = null;

// Comprobar si hay sesión iniciada
if (isset($_SESSION['id'])) {
    $usuario_id = $_SESSION['id'];
    $sql = "SELECT nombre FROM usuarios WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->bind_result($nombre_usuario);
        $stmt->fetch();
        $stmt->close();
    }
}

// Manejo de agregar al carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['producto_id'])) {
    $producto_id = $_POST['producto_id'];
    $cantidad = 1;

    // Verificar si el producto ya está en el carrito
    $sql_check = "SELECT * FROM pedidos WHERE usuario_id = ? AND producto_id = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("ii", $usuario_id, $producto_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $sql_update = "UPDATE pedidos SET cantidad = cantidad + 1 WHERE usuario_id = ? AND producto_id = ?";
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("ii", $usuario_id, $producto_id);
                $stmt_update->execute();
                $stmt_update->close();
            }
        } else {
            $sql_insert = "INSERT INTO pedidos (usuario_id, producto_id, cantidad, estado) VALUES (?, ?, ?, 'pendiente')";
            if ($stmt_insert = $conn->prepare($sql_insert)) {
                $stmt_insert->bind_param("iii", $usuario_id, $producto_id, $cantidad);
                $stmt_insert->execute();
                $stmt_insert->close();
            }
        }
        $stmt_check->close();
    }
}

// Manejo de eliminación del carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = $_POST['eliminar_id'];
    $sql_delete = "DELETE FROM pedidos WHERE usuario_id = ? AND producto_id = ?";
    if ($stmt_delete = $conn->prepare($sql_delete)) {
        $stmt_delete->bind_param("ii", $usuario_id, $eliminar_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
}

// Obtener productos
$sql = "SELECT * FROM productos";
$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

// Obtener artículos en el carrito
$sql_carrito = "SELECT p.nombre, p.precio, pd.cantidad, pd.estado, pd.producto_id FROM pedidos pd JOIN productos p ON pd.producto_id = p.id WHERE pd.usuario_id = ?";
$stmt_carrito = $conn->prepare($sql_carrito);
$stmt_carrito->bind_param("i", $usuario_id);
$stmt_carrito->execute();
$result_carrito = $stmt_carrito->get_result();

// Calcular total del carrito
$total = 0;
$cart_items = [];
if ($result_carrito->num_rows > 0) {
    while ($row_cart = $result_carrito->fetch_assoc()) {
        $total += $row_cart['precio'] * $row_cart['cantidad'];
        $cart_items[] = $row_cart; 
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #333;
        }
        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #fff;
            transition: box-shadow 0.3s;
        }
        .product-card:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .product-card img {
            height: 150px;
            object-fit: cover;
            width: 100%;
            border-radius: 5px;
        }
        .add-to-cart {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .add-to-cart:hover {
            background-color: #e55b00;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .cart-table th, .cart-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .cart-table th {
            background-color: #f2f2f2;
        }
        .remove-button {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .remove-button:hover {
            background-color: #e60000;
        }
    </style>
</head>
<body>

<h1>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></h1>
<h2>Productos Disponibles</h2>

<div class="product-list">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($row['imagen']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                <h3><?php echo htmlspecialchars($row['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
                <p>Precio: $<?php echo htmlspecialchars($row['precio']); ?></p>
                <form action="" method="POST">
                    <input type="hidden" name="producto_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="add-to-cart">Agregar al Carrito</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No hay productos disponibles.</p>
    <?php endif; ?>
</div>

<h2>Artículos en el Carrito</h2>
<table class="cart-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $row_cart): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row_cart['nombre']); ?></td>
                    <td>$<?php echo htmlspecialchars($row_cart['precio']); ?></td>
                    <td><?php echo htmlspecialchars($row_cart['cantidad']); ?></td>
                    <td><?php echo htmlspecialchars($row_cart['estado']) == 'completado' ? 'Pagado' : 'Pendiente'; ?></td>
                    <td>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="eliminar_id" value="<?php echo $row_cart['producto_id']; ?>">
                            <button type="submit" class="remove-button">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No hay artículos en el carrito.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Contenedor del botón de PayPal -->
<div id="paypal-button-container"></div>

<script src="https://www.paypal.com/sdk/js?client-id=ARwtXym4v6BTEwbF7bCjNGh9TxVPfjbo6uPenwnDkLr1AubhBdvojLvYxehJjoiHxIlt5CnXTHfMPqbF&currency=MXN"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Renderizar los botones de PayPal
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo number_format($total, 2); ?>',
                        },
                        description: 'Compra en tienda'
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Pago completado con éxito por ' + details.payer.name.given_name);
                    
                    let formData = new FormData();
                    formData.append('usuario_id', <?php echo $usuario_id; ?>);
                    formData.append('total', '<?php echo number_format($total, 2); ?>');

                    fetch('procesar_pago.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.text())
                      .then(data => {
                          console.log(data);
                          window.location.href = 'productos.php'; // Redirigir después del pago
                      });
                });
            },
            onCancel: function(data) {
                alert('El pago fue cancelado.');
            },
            onError: function(err) {
                console.error(err);
                alert('Hubo un error con el pago.');
            }
        }).render('#paypal-button-container');
    });
</script>

</body>
</html>

<?php 
$stmt_carrito->close();
$conn->close(); 
?>
