<?php
/**
 * Admin Login — Authentication
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

// Initialize secure session for CSRF
init_secure_session();

// If already logged in, redirect
$accessToken = $_COOKIE['admin_access_token'] ?? '';
if ($accessToken && verify_jwt($accessToken)) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = InputValidator::validateAlphaNum($_POST['username'] ?? '', 50);
        $password = is_string($_POST['password'] ?? null) ? trim($_POST['password']) : '';
        $clientIP = getClientIP();

        if (empty($username) || empty($password) || mb_strlen($password) > 255) {
            $error = 'Invalid username or password format.';
        } else {
            // Check rate limiting before processing
            if (!check_rate_limit($clientIP, $username)) {
                $remaining = get_remaining_lockout_time($clientIP);
                $minutes = ceil($remaining / 60);
                $error = "Too many failed attempts. Please try again in {$minutes} minute(s).";
            } else {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
                    $stmt->execute([$username]);
                    $admin = $stmt->fetch();

                    if ($admin && password_verify($password, $admin['password_hash'])) {
                        // Successful login — clear failed attempts and record success
                        clear_login_attempts($clientIP);
                        record_login_attempt($clientIP, $username, true);

                        // Regenerate session ID to prevent fixation
                        session_regenerate_id(true);

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

                        // Set secure cookies
                        set_secure_cookie('admin_access_token', $accessJwt, JWT_ACCESS_EXP);
                        set_secure_cookie('admin_refresh_token', $refreshJwt, JWT_REFRESH_EXP);

                        header('Location: index.php');
                        exit;
                    } else {
                        // Failed login — record attempt
                        record_login_attempt($clientIP, $username, false);
                        $error = 'Invalid username or password.';
                    }
                } catch (Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    $error = 'Server error. Please try again.';
                }
            }
        }
    }
}

// Generate CSRF token for the form
$csrfToken = generate_csrf_token();
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
                <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <?php echo csrf_hidden_field(); ?>
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
