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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(180deg, #1a73e8 0%, #0d47a1 100%);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            animation: slideUp 0.5s ease;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e1e1;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #1a73e8;
            outline: none;
        }

        .btn {
            background: linear-gradient(45deg, #1a73e8, #0d47a1);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .error {
            background: #ffe3e3;
            color: #d63031;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
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
