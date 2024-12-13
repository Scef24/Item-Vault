<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $user_type = trim($_POST['user_type']);

    $errors = [];

    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";

    if (empty($errors)) {
        try {
            if ($user_type == 'admin') {
                $table = 'admin';
                $redirect = 'admin/dashboard.php';
            } else {
                $table = 'users';
                $redirect = 'student/dashboard.php';
            }

            $stmt = $pdo->prepare("SELECT * FROM $table WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user_type;
                
                header("Location: $redirect");
                exit();
            } else {
                $errors[] = "Invalid username or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Login failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Use similar styles as register.php */
    </style>
</head>
<body>
    <div class="navbar">
        <h2>IV.</h2>
        <nav>
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="team.php">Team</a>
        </nav>
    </div>

    <div class="container">
        <h2>LOGIN</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Login As</label>
                <select name="user_type" required>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn">LOGIN</button>
            </div>

            <div style="text-align: center; margin-top: 1rem;">
                <p>Don't have an account? <a href="register.php" style="color: white;">Register</a> here.</p>
            </div>
        </form>
    </div>
</body>
</html>
