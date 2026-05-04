<?php
session_start();

date_default_timezone_set("Europe/Bratislava");

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/security.php';

// Require authentication
require_auth();

$pdo = db_connect();

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
$stmt->execute([$_SESSION['account']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header("Location: /account/login");
    exit;
}

// Get user's servers
$stmt = $pdo->prepare("SELECT sl.address, si.name, si.online, si.players_current
                       FROM server_list sl
                       JOIN server_info si ON si.server_id = sl.id_server
                       WHERE sl.user_id = ?
                       ORDER BY si.players_current DESC");
$stmt->execute([$user['id_user']]);
$userServers = $stmt->fetchAll();

$account = '<a href="/account">Account</a>';
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="My account" >
	<meta name="keywords" content="screeps private community server account" >
	<meta name="author" content="dodoslavn" >
	<link rel="stylesheet" type="text/css" href="/default.css?v=20260504154927" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title>Account - Screeps private server list</title>
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
	<h1>Account</h1>

	<h3>Username:</h3>
	<p><?= escape_html($user['name']) ?></p>

	<h3>Email:</h3>
	<p><?= escape_html($user['email']) ?></p>

	<h3>My servers:</h3>
<?php
if (count($userServers) > 0) {
    echo '<table class="server_list">
        <tr>
            <th>Name</th>
            <th colspan=2>Address</th>
            <th>Online</th>
            <th>Players</th>
            <th></th>
        </tr>';

    foreach ($userServers as $server) {
        $color = $server['online'] == 1 ? 'green' : 'red';
        $parts = explode(':', $server['address']);
        $address = $parts[0] ?? '';
        $port = $parts[1] ?? '';

        echo "<tr>";
        echo "<td>" . escape_html($server['name']) . "</td>";
        echo "<td>" . escape_html($address) . "</td>";
        echo "<td>" . escape_html($port) . "</td>";
        echo "<td style='background-color:" . $color . "'> </td>";
        echo "<td>" . escape_html($server['players_current']) . "</td>";
        echo '<td><a class="server" href="/server/?server=' . urlencode($address . ':' . $port) . '">Info</a></td>';
        echo "</tr>";
    }

    echo '</table>';
} else {
    echo '<p>You have not claimed any servers yet.</p>';
}
?>

	<br><br>
	<a href="/account/logout">Log out</a>

	<div class="footer"><?= site_footer() ?></div>
</div>
</body>
</html>
