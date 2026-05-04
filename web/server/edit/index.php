<?php
session_start();

date_default_timezone_set("Europe/Bratislava");

require_once __DIR__ . '/../../../lib/database.php';
require_once __DIR__ . '/../../../lib/auth.php';
require_once __DIR__ . '/../../../lib/security.php';
require_once __DIR__ . '/../../../lib/helpers.php';

// Require authentication
require_auth();

// Get server address
$server = trim($_GET['server'] ?? '');
if (empty($server)) {
    redirect('/');
}

$pdo = db_connect();

// Verify server exists and user owns it
$stmt = $pdo->prepare("SELECT sl.id_server, u.name AS owner_name, si.name AS server_name, si.description
                       FROM server_list sl
                       JOIN server_info si ON si.server_id = sl.id_server
                       LEFT JOIN users u ON sl.user_id = u.id_user
                       WHERE sl.address = ?");
$stmt->execute([$server]);
$serverData = $stmt->fetch();

if (!$serverData) {
    redirect('/');
}

if ($serverData['owner_name'] !== $_SESSION['account']) {
    redirect('/');
}

$success = null;

// Handle save name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['save_name'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'CSRF validation failed';
    } else {
        $newName = trim($_POST['server_name'] ?? '');
        if (!empty($newName)) {
            $stmt = $pdo->prepare("UPDATE server_info si
                                   JOIN server_list sl ON si.server_id = sl.id_server
                                   SET si.name = ?
                                   WHERE sl.address = ?");
            $stmt->execute([$newName, $server]);
            $success = 'Server name updated!';
            $serverData['server_name'] = $newName;
        }
    }
}

// Handle save description
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['save_desc'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'CSRF validation failed';
    } else {
        $newDesc = trim($_POST['server_desc'] ?? '');
        $stmt = $pdo->prepare("UPDATE server_info si
                               JOIN server_list sl ON si.server_id = sl.id_server
                               SET si.description = ?
                               WHERE sl.address = ?");
        $stmt->execute([$newDesc, $server]);
        $success = 'Server description updated!';
        $serverData['description'] = $newDesc;
    }
}

// Get full server info for display
$stmt = $pdo->prepare("SELECT si.*, sl.address, sl.user_id
                       FROM server_info si
                       JOIN server_list sl ON sl.id_server = si.server_id
                       WHERE sl.address = ?");
$stmt->execute([$server]);
$serverInfo = $stmt->fetch();

$account = '<a href="/account">Account</a>';
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Edit decription of screeps private server" >
	<meta name="keywords" content="screeps private community server <?= escape_html($server) ?> game" >
	<meta name="author" content="Dodoslav Novák" >
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title><?= escape_html($server) ?> - edit Screeps private server</title>
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

<?php
if (!empty($err)) {
    echo '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: #aa1111; border: solid 1px red"> ' . escape_html($err) . ' </div> <br>';
}
if (!empty($success)) {
    echo '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: #11aa11; border: solid 1px #000"> ' . escape_html($success) . ' </div> <br>';
}
?>

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




    <form method="post" action="#">
        <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
        <br><h3>Server name:</h3> <input style="font-family: Times New Roman; background-color: #303030; border-color: black; color: white; width: 300px; height: 30px; font-size: 20px" type="text" value="<?= escape_html($serverData['server_name']) ?>" name="server_name">
        <input style="font-family: Times New Roman; background-color: #303030; border-color: black; color: white; font-size: 20px; height: 30px" type="submit" value="Save" name="save_name">
    </form>


    <form method="post" action="#">
        <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
        <h3>Server description: </h3>
        <textarea style="font-family: Times New Roman; background-color: #303030; border-color: black; color: white;" rows="20" cols="100" type="text" name="server_desc"><?= escape_html($serverData['description']) ?></textarea><br><br>
        <input style="font-family: Times New Roman; background-color: #303030; border-color: black; color: white; height: 30px; font-size: 20px" type="submit" value="Save" name="save_desc">
    </form>



	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019 </div>
</div>

</body>
</html>
