<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>Lost and Found System</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar h2 {
            color: white;
            font-size: 24px;
        }

        nav {
            display: flex;
            gap: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        nav a:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .hero {
            text-align: center;
            padding: 60px 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 48px;
            color: #1a73e8;
            margin-bottom: 20px;
            animation: fadeIn 1s ease;
        }

        .hero p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .cta-btn {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .primary-btn {
            background: linear-gradient(45deg, #1a73e8, #0d47a1);
            color: white;
        }

        .secondary-btn {
            background: white;
            color: #1a73e8;
            border: 2px solid #1a73e8;
        }

        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn {
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

    <div class="hero">
        <h1>Welcome to Lost and Found</h1>
        <p>A simple and efficient way to manage lost and found items in our institution. 
           Report lost items or help others find their belongings.</p>
        <div class="cta-buttons">
            <a href="login.php" class="cta-btn primary-btn">Login</a>
            <a href="register.php" class="cta-btn secondary-btn">Register</a>
        </div>
    </div>
</body>
</html>