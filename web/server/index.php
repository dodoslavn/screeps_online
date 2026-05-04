<?php
session_start();

date_default_timezone_set("Europe/Bratislava");

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/security.php';

$pdo = db_connect();

// Get server address
$server = trim($_GET['server'] ?? '');
if (empty($server)) {
    header("Location: /");
    exit;
}

// Verify server exists
$stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM server_list WHERE address = ?");
$stmt->execute([$server]);
$result = $stmt->fetch();

if ($result['c'] != 1) {
    header("Location: /");
    exit;
}

// Set account link
if (empty($_SESSION['account'])) {
    $account = '<a href="/account/login">Log in</a>';
} else {
    $account = '<a href="/account">Account</a>';
}

// Get server info
$stmt = $pdo->prepare("SELECT si.*, sl.address, sl.user_id
                       FROM server_info si
                       JOIN server_list sl ON sl.id_server = si.server_id
                       WHERE sl.address = ?");
$stmt->execute([$server]);
$serverInfo = $stmt->fetch();

if (!$serverInfo) {
    header("Location: /");
    exit;
}
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="<?= escape_html("screeps private server info " . $server) ?>" >
	<meta name="keywords" content="screeps private community server list game " >
	<meta name="author" content="<?= config('site.author') ?>" >
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title><?= escape_html($server) ?> - Screeps private server information</title>
</head>

<body>

<div class="header">
	<div class="head">
		<div class="logo">

		</div>
		<div class="panel">
			<a style="color: #ffe799;" href="/" >Server list</a>
			<a href="/add" >Add server</a>
			<a href="/about" >About</a>
			<?= $account ?>
		</div>
	</div>
</div>

<div class="container">

	<h1>Screeps server: <?= escape_html($server) ?></h1>

	<table class="server_list">
		<tr>
			<th>Name</th>
			<th colspan=2 >Address</th>
			<th>Online</th>
			<th>Players</th>
			<th>Version</th>
			<th>Last check/online</th>
			<th>Availability</th>
		</tr>
<?php
$color = $serverInfo['online'] == 1 ? 'green' : 'red';
$parts = explode(':', $serverInfo['address']);
$address = $parts[0] ?? '';
$port = $parts[1] ?? '';
$lastCheck = date('H:i:s d.m.Y', strtotime($serverInfo['last_check']));
$availability = $serverInfo['availability'] ?? 0;
$description = $serverInfo['description'] ?? '';
$owner = $serverInfo['user_id'];

echo "<tr>";
echo "<td>" . escape_html($serverInfo['name']) . "</td>";
echo "<td>" . escape_html($address) . "</td>";
echo "<td>" . escape_html($port) . "</td>";
echo "<td style='background-color:" . $color . "'> </td>";
echo "<td>" . escape_html($serverInfo['players_current']) . "</td>";
echo "<td>" . escape_html($serverInfo['version']) . "</td>";
echo "<td>" . $lastCheck . "</td>";
echo "<td>" . escape_html($availability) . "%</td>";
echo "</tr>";
?>
	</table>

	<h3>Owner:</h3>
	<p>
<?php
if (empty($owner)) {
    echo "Server is not claimed yet! " . ' (<a class="server" href="/server/claim/?server=' . urlencode($address . ':' . $port) . '">claim</a>)';
} else {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id_user = ?");
    $stmt->execute([$owner]);
    $ownerUser = $stmt->fetch();

    if ($ownerUser) {
        echo escape_html($ownerUser['name']);

        if ($ownerUser['name'] == $_SESSION['account']) {
            echo ' (<a class="server" href="/server/edit/?server=' . urlencode($address . ':' . $port) . '">edit</a>)';
        }
    }
}

if (empty($description)) {
    $description = "No information available.";
}
?>
    </p>

	<h3>Description:</h3>
	<p><?= escape_html($description) ?></p>

	<div class="footer"><?= site_footer() ?></div>
</div>

</body>
</html>
