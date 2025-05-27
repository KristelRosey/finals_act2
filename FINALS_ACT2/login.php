<style>
    body {
        font-family: Arial;
        background: #f4f4f4;
        padding: 30px;
    }
    form {
        background: white;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 0 8px #ccc;
    }
    input, textarea, button {
        width: 100%;
        margin: 10px 0;
        padding: 10px;
    }
    button {
        background: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
</style>



<?php
session_start();
$conn = new mysqli("localhost", "root", "", "blog_app");

$success = $_GET['success'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hashed);

    if ($stmt->fetch() && password_verify($password, $hashed)) {
        $_SESSION["user_id"] = $id;
        $_SESSION["username"] = $username;
        header("Location: index.php");
        exit();
    } else {
        echo "Invalid login.";
    }
}
?>

<form method="post">
    <h2>Login</h2>
    <?php if ($success): ?>
        <p style="color:green"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    Username: <input name="username" required><br>
    Password: <input name="password" type="password" required><br>
    <button type="submit">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>

