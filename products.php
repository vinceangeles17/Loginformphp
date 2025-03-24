<?php
session_start();
include 'connection.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

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
    header("Location: products.php");
    exit();
}

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
    <title>Products Management</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container large-container">
	<a href="welcome.php" class="back-button">Go Back?</a>
        <h2>Products List</h2>

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

        <h3>All Products</h3>
        <table>
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
                <td><?php echo htmlspecialchars ($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['price']); ?></td>
                <td><?php echo htmlspecialchars($row['stock']); ?></td>
                <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td><?php echo htmlspecialchars($row['edited_at']); ?></td>
                <td>
                    <a href="products.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    <a href="products.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>