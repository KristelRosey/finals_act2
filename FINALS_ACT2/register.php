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
$conn = new mysqli("localhost", "root", "", "blog_app");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        header("Location: login.php?success=Account created! Please log in.");
        exit();
    } else {
        echo "Registration failed.";
    }
}
?>

<form method="post">
    <h2>Register</h2>
    Username: <input name="username" required><br>
    Password: <input name="password" type="password" required><br>
    <button type="submit">Register</button>
</form>
<a href="login.php"><button>Back to Login</button></a>

