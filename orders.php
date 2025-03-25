<?php
session_start();
include 'connection.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$query = "SELECT o.id, o.product_id, p.product_name, o.quantity, o.created_at FROM orders o JOIN products p ON o.product_id = p.id WHERE o.user_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>No orders found for this user.</p>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Orders</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="welcome.php">Home</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
    </div>
    <div class="container">
    <span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</span>
        <h2>Your Orders</h2>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Order Date</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_id']); ?></td> 
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <script src="index.js"></script>
</body>
</html>

<?php
$conn->close();
?>