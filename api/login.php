<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'marvin' && $password === 'marvin') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'marvin';
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GeoTrack Pro - Login</title>
    <style>
        body { background-color: #0d1117; color: #c9d1d9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background-color: #161b22; border: 1px solid #30363d; border-radius: 6px; padding: 20px; width: 300px; }
        h2 { text-align: center; color: #58a6ff; }
        input { width: 100%; padding: 10px; margin: 10px 0; background-color: #0d1117; border: 1px solid #30363d; color: #c9d1d9; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #238636; border: 1px solid #2ea44f; color: white; border-radius: 6px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #2ea44f; }
        .error { color: #f85149; font-size: 14px; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>GeoTrack Pro</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
