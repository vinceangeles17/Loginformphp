<?php
session_start();
include 'connection.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT firstname, lastname, email, created_at, edited_at, role FROM users1 WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $id = $_POST['id'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users1 SET firstname = ?, lastname = ?, email = ? ,role= ? WHERE id = ?");
    $stmt->bind_param("ssssi", $firstname, $lastname, $email, $role, $id);
    $stmt->execute();
    header("Location: users.php");
    exit();
}

$query = "SELECT id, firstname, lastname, email, role, created_at, edited_at FROM users1";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users Management</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container large-container">
        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <a href="welcome.php">Home</a>
            <a href="users.php">Users</a>
            <a href="products.php">Products</a>
        </div>
        <span style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</span>
        <h2>Users List</h2>
        
        <?php if (isset($user)): ?>
            <h3>Edit User</h3>
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_id); ?>">
                <label>First Name:</label>
                <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                <label>Last Name:</label>
                <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <label>Role:</label>
                <select name="role" required>
                        <option value="Admin" <?php echo ($user['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="Customer" <?php echo ($user['role'] === 'Customer') ? 'selected' : ''; ?>>Customer</option>
                </select>
                <input type="submit" name="update" value="Update">
            </form>
        <?php endif; ?>

        <h3>All Users</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role</th>
		        <th>Created At</th>
                <th>Edited At</th>
                <th>EDIT</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td><?php echo htmlspecialchars($row['edited_at']); ?></td>
                <td>
                    <a href="users.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <script src="index.js"></script>
</body>
</html>