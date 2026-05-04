<?php
session_start();

date_default_timezone_set("Europe/Bratislava");

require_once __DIR__ . '/../../lib/database.php';
require_once __DIR__ . '/../../lib/security.php';
require_once __DIR__ . '/../../lib/helpers.php';

$pdo = db_connect();

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
	<meta name="description" content="screeps priavte community server list" >
	<meta name="keywords" content="screeps private community server list game" >
	<meta name="author" content="dodoslavn" >
	<link rel="stylesheet" type="text/css" href="/default.css?v=20260504154927" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title>All servers - Screeps private server list</title>
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

	<h1>All Screeps servers</h1>

	<table class="server_list">
		<tr>
			<th>Name</th>
			<th colspan=2 >Address</th>
			<th>Online</th>
			<th>Players</th>
			<th>Version</th>
			<th>Last check/online</th>
			<th>Availability</th>
			<th></th>
		</tr>
<?php
$stmt = $pdo->prepare("SELECT si.*, sl.address
                       FROM server_info si
                       JOIN server_list sl ON sl.id_server = si.server_id
                       ORDER BY si.players_current DESC");
$stmt->execute();

while ($row = $stmt->fetch()) {
    $color = $row['online'] == 1 ? 'green' : 'red';
    $parts = explode(':', $row['address']);
    $address = $parts[0] ?? '';
    $port = $parts[1] ?? '';
    $lastCheck = date('H:i:s d.m.Y', strtotime($row['last_check']));
    $availability = $row['availability'] ?? 0;

    echo "<tr>";
    echo "<td>" . escape_html($row['name']) . "</td>";
    echo "<td>" . escape_html($address) . "</td>";
    echo "<td>" . escape_html($port) . "</td>";
    echo "<td style='background-color:" . $color . "'> </td>";
    echo "<td>" . escape_html($row['players_current']) . "</td>";
    echo "<td>" . escape_html($row['version']) . "</td>";
    echo "<td>" . $lastCheck . "</td>";
    echo "<td>" . escape_html($availability) . "%</td>";
    echo '<td><a class="server" href="/server/?server=' . urlencode($address . ':' . $port) . '">Info</a></td>';
    echo "</tr>";
}
?>
	</table>
	<div class="footer"><?= site_footer() ?></div>
</div>

</body>
</html>
