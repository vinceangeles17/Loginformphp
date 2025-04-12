<?php
session_start();
include 'connection.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// DELETE ITEMS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id']; // Get the cart_id from the form

    $stmt = $conn->prepare("DELETE FROM my_cart WHERE id = ? AND user_email = ?");
    $stmt->bind_param("is", $cart_id, $email);
    
    if ($stmt->execute()) {
        // Redirect back to the cart page after deletion
        header("Location: mycart.php");
        exit();
    } else {
        echo "Error deleting item: " . htmlspecialchars($stmt->error);
    }
}

$query = "SELECT * FROM my_cart WHERE user_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container large-container">
        <span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</span>
        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <a href="welcome.php">Home</a>
            <a href="products.php">Products</a>
            <a href="mycart.php">My Cart</a>
            <a href="orders.php">Orders</a>
        </div>
        <h2>My Cart</h2>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Action</th>
                <th></th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['price']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['total_price']); ?></td>
                <td>
                    <form method="POST" action="orders.php">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <input type="hidden" name="quantity" value="<?php echo $row['quantity']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo $row['product_name']; ?>">
                        <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                        <input type="hidden" name="total_price" value="<?php echo $row['total_price']; ?>">
                        <input type="hidden" name="cart_id" value="<?php echo $row['id']; ?>">
                        <input type="submit" name="order" value="Order"> <!--LAGYAN NYO NA LANG ALERT TO-->
                    </form>
                    <td>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="cart_id" value="<?php echo $row['id']; ?>"> 
                        <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this item from the cart?');">
                    </form>
                    </td>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
<script src="index.js"></script>
<?php
$conn->close();
?>