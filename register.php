<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $messages = [];

    if ($password !== $confirm_password) {
        $messages[] = "<span style='color: red;'>Passwords do not match.</span>";
    }

    if (empty($messages)) {
        $stmt = $conn->prepare("SELECT * FROM users1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $messages[] = "<span style='color: red;'>Email is already in use.</span>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users1 (firstname, lastname, email, role, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $firstname, $lastname, $email, $role, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $messages[] = "<span style='color: red;'>Error: " . htmlspecialchars($stmt->error) . "</span>";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="">
            <label>First Name:</label>
            <input type="text" name="firstname" required>
            <label>Last Name:</label>
            <input type="text" name="lastname" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Role:</label>
                <select name="role" required>
                    <option value="Admin">Admin</option>
                    <option value="Customer">Customer</option>
                </select>
            <label>Password:</label>
            <input type="password" name="password" required>
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>
            <input type="submit" value="Register">
        </form>
        
        <div style="margin-top: 10px;">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <p><?php echo $message; ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <a href="login.php">Already have an account? Login here</a>
    </div>
</body>
</html>