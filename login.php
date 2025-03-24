<?php
session_start();
include 'connection.php'; 

if (isset($_SESSION['email'])) {
    header("Location: welcome.php");
    exit();
}

$error_message = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT password FROM users1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];
            if (password_verify($password, $hashed_password)) {
                $_SESSION['email'] = $email;
                header("Location: welcome.php");
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }
        $stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="">
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <input type="submit" value="Login">
        </form>
        
        <?php if ($error_message): ?>
            <div style="color: red; margin-top: 10px;"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <a href="register.php">Don't have an account? Register here</a>
    </div>
</body>
</html>