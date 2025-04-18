<?php
session_start();
include 'connection.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_POST['users'])) {
    header("Location: users.php");
    exit();
}

if (isset($_POST['products'])) {
    header("Location: products.php");
    exit();
}

if (isset($_POST['orders'])) {
    header("Location: orders.php");
    exit();
}

if (isset($_POST['mycart'])) {
    header("Location: mycart.php");
    exit();
}


$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT firstname, lastname, email, role FROM users1 WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars(($user['role']) . ' ' . $user['firstname'] . ' ' . $user['lastname']); ?>!</h2>
        <form method="POST" action="">
        <?php if ($user['role'] === 'Admin'): ?>
                <input type="submit" name="users" value="Users">
                <input type="submit" name="products" value="Products">
                <input type="submit" name="orders" value="Order List">
            <?php else: ?>
                <input type="submit" name="products" value="Products">
                <input type="submit" name="mycart" value="My Cart">
                <input type="submit" name="orders" value="Orders">
            <?php endif; ?>
            <input type="submit" name="logout" value="Logout">
        </form>
    </div>
</body>
</html>