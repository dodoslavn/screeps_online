<?php
session_start();

require_once __DIR__ . '/../../../lib/database.php';
require_once __DIR__ . '/../../../lib/auth.php';
require_once __DIR__ . '/../../../lib/security.php';
require_once __DIR__ . '/../../../lib/helpers.php';

// Redirect if already logged in
require_guest();

$err = null;

// Handle account creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['submit'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'CSRF validation failed';
    } else {
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $p1 = $_POST['password1'] ?? '';
        $p2 = $_POST['password2'] ?? '';

        // Validation
        if (empty($email) || empty($username) || empty($p1) || empty($p2)) {
            $err = "Account creation failed! Some fields were empty!";
        } elseif (strlen($username) < 3) {
            $err = "Username is too short!";
        } elseif (strlen($p1) < 6) {
            $err = "Password is too short!";
        } elseif ($p1 !== $p2) {
            $err = "Password does not match!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Email format is incorrect!';
        } else {
            // Check if email/username already exists
            $pdo = db_connect();

            $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            if ($result['c'] != 0) {
                $err = 'Email is already in use!';
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM users WHERE name = ?");
                $stmt->execute([$username]);
                $result = $stmt->fetch();
                if ($result['c'] != 0) {
                    $err = 'Username is already in use!';
                } else {
                    // Create account with secure password hash
                    $passwordHash = hash_password($p1);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");

                    if ($stmt->execute([$username, $email, $passwordHash])) {
                        redirect('/account/login', 'Account created successfully! Please log in.', 'success');
                    } else {
                        $err = 'Account creation failed!';
                    }
                }
            }
        }
    }
}

$account = '<a style="color: #ffe799;" href="/account" >Log in</a>';
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Create account screeps.dodoslav.eu" >
	<meta name="keywords" content="screeps private server community create account" >
	<meta name="author" content="dodoslavn" >
	<link rel="stylesheet" type="text/css" href="/default.css?v=20260504154927" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title>Add account - Screeps private server list</title>
</head>
<body>
<div class="header">
	<div class="head">
		<div class="logo">
		</div>
		<div class="panel">
			<a href="/" >Server list</a>
			<a href="/add" >Add server</a>
			<a href="/about" >About</a>

			<?= $account ?>
		</div>
	</div>
</div>
<div class="container">
	<h1>Create account</h1>

<?php
if (!empty($err)) {
    echo '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: #aa1111; border: solid 1px red"> ' . escape_html($err) . ' </div> <br>';
}
?>

<form method="post" action="#">
    <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
    <table class="login">
        <tr>
        <tr>
            <td>Email: </td>
            <td><input type="text" value="" name="email"></td>
        </tr>
            <td>Username: </td>
            <td><input type="text" value="" name="username"></td>
        <tr>
            <td>Password: </td>
            <td><input type="password" value="" name="password1"></td>
        </tr>
        <tr>
            <td>Confirm password: </td>
            <td><input type="password" value="" name="password2"></td>
        </tr>
        <tr> <td></td>
            <td><br><input type="submit" value="Submit" name="submit"></td>
        </tr>
    </table>
</form>

	<div class="footer"><?= site_footer() ?></div>
</div>
</body>
</html>
