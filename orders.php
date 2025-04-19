<?php
session_start();
include 'connection.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch the user's role from the database
$stmt = $conn->prepare("SELECT role FROM users1 WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_role = $user['role'];

// ORDER PLACEMENT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order'])) {
    $product_id = $_POST['product_id']; // Get product_id from the form
    $quantity = $_POST['quantity']; // Get quantity from the form
    
    // Fetch product details
    $stmt = $conn->prepare("SELECT product_name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $product_name = $product['product_name'];
        $price = $product['price'];
        $total_price = $price * $quantity;
        $status = 'Pending';

        // INSERT TO ORDERS
        $stmt = $conn->prepare("INSERT INTO orders (user_email, product_id, price, quantity, total_price, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissis", $email, $product_id, $price, $quantity, $total_price, $status);
        $stmt->execute();

        // DELETE WHEN ORDERED
        $stmt = $conn->prepare("DELETE FROM my_cart WHERE product_id = ? AND user_email = ?");
        $stmt->bind_param("is", $product_id, $email);
        $stmt->execute();

        header("Location: orders.php");
        exit();
    }
}

// ORDER STATUS TO IN TRANSIT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['in_transit'])) {
    $order_id = $_POST['order_id'];

    // FETCH DETAILS
    $stmt = $conn->prepare("SELECT product_id, quantity FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    // UPDATE STOCK AMOUNT kasi pag na place pa lang order sa mycart di pa dapat bawas stock dapat pag intransit na kasi admin mag update non
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $stmt->bind_param("ii", $order['quantity'], $order['product_id']);
    $stmt->execute();

    // STATUS TO IN TRANSIT
    $stmt = $conn->prepare("UPDATE orders SET status = 'In Transit' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    header("Location: orders.php"); // Redirect back to orders page
    exit();
}

// ORDER STATUS TO RECIEVED
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['received'])) {
    $order_id = $_POST['order_id'];

    // STATUS TO RECIEVED
    $stmt = $conn->prepare("UPDATE orders SET status = 'Received' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    header("Location: orders.php"); // Redirect back to orders page
    exit();
}

// FETCH ORDERS BASED ON ROLES
if ($user['role'] === 'Admin') {
    $query = "SELECT o.*, u.firstname, u.lastname, p.product_name FROM orders o 
              JOIN users1 u ON o.user_email = u.email 
              JOIN products p ON o.product_id = p.id"; // SHOW ALL FOR ADMIN
} else {
    $query = "SELECT o.*, u.firstname, u.lastname, p.product_name FROM orders o 
              JOIN users1 u ON o.user_email = u.email 
              JOIN products p ON o.product_id = p.id 
              WHERE o.user_email = ?"; // SHOW CUS THEIR ORDERS
}

$stmt = $conn->prepare($query);
if ($user['role'] !== 'Admin') {
    $stmt->bind_param("s", $email);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Orders</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
       <?php if ($user['role'] === 'Admin'): ?>
        <div class="container large-container">
        <div id="mySidenav" class="sidenav">
                <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                <a href="welcome.php">Home</a>
                <a href="users.php">Users</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Order List</a>
            </div>
    <?php elseif ($user['role'] !== 'Admin'): ?>
        <div class="container medium-container">
        <div id="mySidenav" class="sidenav">
                <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                <a href="welcome.php">Home</a>
                <a href="products.php">Products</a>
                <a href="mycart.php">My Cart</a>
                <a href="orders.php">Orders</a>
            </div>
    <?php endif; ?>
        <span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</span>
        <h2>Your Orders </h2>
        <table>
            <tr>
                <?php if ($user['role'] === 'Admin'): ?>
                    <th>Customer Name</th>
                <?php endif; ?>
                <th>Product Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <?php if ($user['role'] === 'Admin'): ?>
                        <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <?php if ($user['role'] === 'Admin' && $row['status'] === 'Pending'): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <input type="submit" name="in_transit" value="Mark as In Transit" onclick="return confirm('Are you sure you want to mark this order as In Transit?');">
                            </form>
                        <?php elseif ($user['role'] !== 'Admin' && $row['status'] === 'In Transit'): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <input type="submit" name="received" value="Mark as Received" onclick="return confirm('Have you received the item?');">
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
 <script src="index.js"></script>
</body>
</html>