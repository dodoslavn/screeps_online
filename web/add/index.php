<?php
session_start();

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/security.php';
require_once __DIR__ . '/../../lib/helpers.php';

$msg = null;
$msgType = 'error';

// Handle server addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['server'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $msg = 'CSRF validation failed';
    } else {
        $serverAddress = trim($_POST['server']);

        // Validate server address
        if (!validate_server_address($serverAddress)) {
            $msg = 'Error: Server was not added due to incorrect address format!';
        } else {
            // Check if server already exists
            $pdo = db_connect();
            $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM server_list WHERE address = ?");
            $stmt->execute([$serverAddress]);
            $result = $stmt->fetch();

            if ($result['c'] != 0) {
                $msg = 'Error: Server is already in database!';
            } else {
                // Add server to database
                $stmt = $pdo->prepare("INSERT INTO server_list (address) VALUES (?)");
                if ($stmt->execute([$serverAddress])) {
                    $msg = 'Server added successfully!';
                    $msgType = 'success';
                } else {
                    $msg = 'Error: Failed to add server!';
                }
            }
        }
    }
}

// Set account link
if (empty($_SESSION['account'])) {
    $account = '<a href="/account/login">Log in</a>';
} else {
    $account = '<a href="/account">Account</a>';
}
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Add Screeps private community server into public list" >
	<meta name="keywords" content="screeps private server community add server" >
	<meta name="author" content="Dodoslav Novák" >
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title>Add Screeps server - Screeps private server list</title>
</head>

<body>

<div class="header">
	<div class="head">
		<div class="logo">
		</div>
		<div class="panel">
			<a href="/" >Server list</a>
			<a style="color: #ffe799;" href="/add" >Add server</a>
			<a href="/about" >About</a>
			<?= $account ?>
		</div>
	</div>
</div>

<div class="container">
	<h1>Add your Screeps private server into list:</h1>

<?php
if (!empty($msg)) {
    $color = $msgType === 'error' ? '#aa1111' : '#11aa11';
    echo '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: ' . $color . '; border: solid 1px #000"> ' . escape_html($msg) . ' </div>';
}
?>

	<h3><br>Enter domain or IP with port of your Screeps private server:</h3>
	<form action="" method="post">
		<input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
		<center>http://<input type="text" value="screeps.com:21025" name="server">/
		<input type="submit" value="Add server"> </center>
	</form>
	<div class="footer"><?= site_footer() ?></div>
</div>



</body>
</html>
