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

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT firstname, lastname, email FROM users1 WHERE email = ?");
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
        <h2>Welcome, <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>!</h2>
        <p>Your email: <?php echo htmlspecialchars($user['email']); ?></p> 
        <form method="POST" action="">
            <input type="submit" name="logout" value="Logout">
            <input type="submit" name="users" value="Users">
            <input type="submit" name="products" value="Products">
        </form>
    </div>
</body>
</html>