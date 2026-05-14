<?php
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            header("Location: dashboard");
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Marrthings Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #1a56db;
            --orange: #f97316;
            --dark: #0f172a;
            --white: #ffffff;
            --gray-light: #f8fafc;
            --gray-border: #e2e8f0;
            --text-muted: #64748b;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --font-display: 'Plus Jakarta Sans', sans-serif;
            --font-body: 'Outfit', sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-body);
            background: var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--dark);
        }

        .login-card {
            background: var(--white);
            padding: 3rem;
            border-radius: 30px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--gray-border);
            text-align: center;
        }

        .logo {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 2rem;
            color: var(--dark);
        }

        .logo span { color: var(--orange); }

        h1 {
            font-family: var(--font-display);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        p { color: var(--text-muted); margin-bottom: 2rem; }

        .form-group {
            text-align: left;
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--gray-border);
            border-radius: 12px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--blue);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background: var(--orange);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.2);
        }

        .error-msg {
            background: #fef2f2;
            color: #ef4444;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .back-link {
            display: block;
            margin-top: 2rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .back-link:hover { color: var(--blue); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">Marr<span>things</span></div>
        <h1>Welcome Back</h1>
        <p>Log in to manage your professional portfolio.</p>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn-login">Login to Dashboard</button>
        </form>

        <a href="../" class="back-link">← Back to Website</a>
    </div>
</body>
</html>
