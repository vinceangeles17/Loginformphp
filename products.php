<?php
session_start();
include 'connection.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Handle adding to cart for customers
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("SELECT product_name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $total_price = $product['price'] * $quantity;
        $stmt = $conn->prepare("INSERT INTO my_cart (user_email, product_id, product_name, price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisdid", $_SESSION['email'], $product_id, $product['product_name'], $product['price'], $quantity, $total_price);
        $stmt->execute();

        // Set session variable for customer notification
        $_SESSION['cart_message'] = 'Item has been successfully added to your cart!';
        header("Location: products.php");
        exit();
    }
}

// Handle adding products for admins
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $supplier = $_POST['supplier'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, stock, supplier, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $product_name, $category, $price, $stock, $supplier, $description);
    $stmt->execute();

    // Set session variable for admin notification
    $_SESSION['product_message'] = 'New product has been successfully added!';
    header("Location: products.php");
    exit();
}

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT role FROM users1 WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_role = $user['role'];

if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $supplier = $_POST['supplier'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, category = ?, price = ?, stock = ?, supplier = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssdissi", $product_name, $category, $price, $stock, $supplier, $description, $id);
    $stmt->execute();
    header("Location: products.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: products.php");
    exit();
}

$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php if ($user['role'] === 'Admin'): ?>
        <title>Products Management</title>
    <?php elseif ($user['role'] !== 'Admin'): ?>
        <title>Products</title>
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container large-container">
        <span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</span>
        <h2>Products List</h2>
        <?php if ($user['role'] === 'Admin'): ?>
            <div id="mySidenav" class="sidenav">
                <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                <a href="welcome.php">Home</a>
                <a href="users.php">Users</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Order List</a>
            </div>
            <?php if (isset($product)): ?>
                <h3>Edit Product</h3>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                    <label>Product Name:</label>
                    <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    <label>Category:</label>
                    <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    <label>Stock:</label>
                    <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                    <label>Supplier:</label>
                    <input type="text" name="supplier" value="<?php echo htmlspecialchars($product['supplier']); ?>" required>
                    <label>Description:</label>
                    <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    <input type="submit" name="update_product" value="Update Product">
                </form>
            <?php else: ?>
                <h3>Add New Product</h3>
                <form method="POST" action="">
                    <label>Product Name:</label>
                    <input type="text" name="product_name" required>
                    <label>Category:</label>
                    <input type="text" name="category" required>
                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" required>
                    <label>Stock:</label>
                    <input type="number" name="stock" required>
                    <label>Supplier:</label>
                    <input type="text" name="supplier" required>
                    <label>Description:</label>
                    <textarea name="description" required></textarea>
                    <input type="submit" name="add_product" value="Add Product">
                </form>
            <?php endif; ?>
        <?php else: ?>   
            <div id="mySidenav" class="sidenav">
                <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                <a href="welcome.php">Home</a>
                <a href="products.php">Products</a>
                <a href="mycart.php">My Cart</a>
                <a href="orders.php">Orders</a>
            </div>
        <?php endif; ?>
        <h3>All Products</h3>
        <table>
            <?php if ($user['role'] === 'Admin'): ?>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Supplier</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Edited At</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                    <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($row['edited_at']); ?></td>
                    <td>
                        <a href="products.php?edit_id=<?php echo $row['id']; ?>" onclick="return alert('You are now Editing')">Edit</a>
                        <a href="products.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <form method="POST" action="">
                        <td>
                            <input type="number" name="quantity" min="1" max="<?php echo $row['stock']; ?>" required>
                        </td>
                        <td>
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" name="add_to_cart" value="Add to Cart">                                                                                                                 
                        </td>
                    </form>
                </tr>   
                <?php endwhile; ?>
            <?php endif;?>  
        </table>
    </div>
    <script src="index.js"></script>

    <?php
    // Display notification for Admin if set
    if (isset($_SESSION['product_message'])) {
        echo "<script>alert('" . $_SESSION['product_message'] . "');</script>";
        unset($_SESSION['product_message']); // Clear the message after displaying
    }

    // Display notification for Customer if set
    if (isset($_SESSION['cart_message'])) {
        echo "<script>alert('" . $_SESSION['cart_message'] . "');</script>";
        unset($_SESSION['cart_message']); // Clear the message after displaying
    }
    ?>
</body>
</html>

<?php
$conn->close();
?>