<?php
session_start();

require_once __DIR__ . '/../../../lib/database.php';
require_once __DIR__ . '/../../../lib/auth.php';
require_once __DIR__ . '/../../../lib/security.php';
require_once __DIR__ . '/../../../lib/helpers.php';

// Redirect if already logged in
require_guest();

$err = null;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['submit'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'CSRF validation failed';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $err = 'Empty credentials!';
        } else {
            // Get user from database
            $pdo = db_connect();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && verify_password($password, $user['password'])) {
                // Login successful
                $_SESSION['account'] = $user['name'];
                regenerate_session();
                redirect('/account', 'Login successful!', 'success');
            } else {
                $err = 'Wrong credentials!';
            }
        }
    }
}

// Set account link for nav
$account = '<a style="color: #ffe799;" href="/account" >Log in</a>';

// Get flash message if any
$flash = get_flash();
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Login into screeps.dodoslav.eu" >
	<meta name="keywords" content="screeps private server community login" >
	<meta name="author" content="<?= config('site.author') ?>" >
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title>Login - Screeps private server list</title>
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
	<h1>Log in</h1>

<?php
if (!empty($err)) {
    echo '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: #aa1111; border: solid 1px red"> ' . escape_html($err) . ' </div> <br>';
}

if ($flash) {
    $color = $flash['type'] === 'error' ? '#aa1111' : '#11aa11';
    echo '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: ' . $color . '; border: solid 1px #000"> ' . escape_html($flash['message']) . ' </div> <br>';
}
?>


<form method="post" action="#">
    <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
    <table class="login">
        <tr>
            <td>Username: </td>
            <td><input type="text" value="" name="username"></td>
        <tr>
            <td>Password: </td>
            <td><input type="password" value="" name="password"></td>
        </tr>
        <tr> <td></td>
            <td><br><input type="submit" value="Submit" name="submit"></td>
        </tr>
    </table>
</form>

    <br><br><br><br>
	<h3>Dont have an account yet?</h3>
	<p>Create your account <a href="/account/create" alt="Create account">here</a>.</p>



	<div class="footer"><?= site_footer() ?></div>
</div>
</body>
</html>
