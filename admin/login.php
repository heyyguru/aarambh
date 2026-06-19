<?php
/**
 * Admin Login — Authentication
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/auth.php';

// If already logged in, redirect
$accessToken = $_COOKIE['admin_access_token'] ?? '';
if ($accessToken && verify_jwt($accessToken)) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Issue JWT Tokens
                $accessJwt = create_jwt([
                    'type' => 'access',
                    'username' => $username,
                    'exp' => time() + JWT_ACCESS_EXP
                ]);
                $refreshJwt = create_jwt([
                    'type' => 'refresh',
                    'username' => $username,
                    'exp' => time() + JWT_REFRESH_EXP
                ]);

                // Set HttpOnly Cookies
                setcookie('admin_access_token', $accessJwt, time() + JWT_ACCESS_EXP, '/', '', false, true);
                setcookie('admin_refresh_token', $refreshJwt, time() + JWT_REFRESH_EXP, '/', '', false, true);

                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'Server error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Aarambh by HeyyGuru</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>🔐 Admin Panel</h1>
                <p>Aarambh by HeyyGuru</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;">Login →</button>
            </form>
        </div>
    </div>
</body>
</html>
