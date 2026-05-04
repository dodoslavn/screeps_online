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

// Handle claim attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['claim'])) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $err = 'CSRF validation failed';
    } else {
        // Rate limiting - max 10 attempts per session
        if (!isset($_SESSION['claim_attempt'])) {
            $_SESSION['claim_attempt'] = 0;
        }

        $_SESSION['claim_attempt']++;

        if ($_SESSION['claim_attempt'] >= 10) {
            $err = 'Too many claim attempts. Please try again later.';
        } else {
            // Check if server exists
            $stmt = $pdo->prepare("SELECT sl.id_server, sl.user_id FROM server_list sl
                                   JOIN server_info si ON si.server_id = sl.id_server
                                   WHERE sl.address = ?");
            $stmt->execute([$server]);
            $serverData = $stmt->fetch();

            if (!$serverData) {
                redirect('/');
            }

            // Check if already claimed
            if ($serverData['user_id']) {
                $err = 'Server is already claimed!';
            } else {
                // Verify ownership via API
                $url = "http://{$server}/api/version/";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                $verificationString = "screeps_" . $_SESSION['account'] . "_online";

                if ($output && strpos($output, $verificationString) !== false) {
                    // Claim successful
                    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE name = ?");
                    $stmt->execute([$_SESSION['account']]);
                    $user = $stmt->fetch();

                    if ($user) {
                        $stmt = $pdo->prepare("UPDATE server_list SET user_id = ? WHERE id_server = ?");
                        $stmt->execute([$user['id_user'], $serverData['id_server']]);

                        $success = 'Server claimed successfully!';
                        $_SESSION['claim_attempt'] = 0; // Reset on success
                    }
                } else {
                    $err = 'Verification failed. Please ensure the verification string is in your server welcomeText.';
                }
            }
        }
    }
}

// Get server info
$stmt = $pdo->prepare("SELECT si.*, sl.address, sl.user_id
                       FROM server_info si
                       JOIN server_list sl ON sl.id_server = si.server_id
                       WHERE sl.address = ?");
$stmt->execute([$server]);
$serverInfo = $stmt->fetch();

if (!$serverInfo) {
    redirect('/');
}

$account = '<a href="/account">Account</a>';
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Claim Screeps private server <?= escape_html($server) ?>" >
	<meta name="keywords" content="screeps private community server claim game" >
	<meta name="author" content="Dodoslav Novák" >
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title><?= escape_html($server) ?> - claim Screeps private server</title>
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

<?php
$owner = $serverInfo['user_id'];

if (empty($owner)) {
    // Server not claimed yet
    echo '
    <h3>How to claim:</h3>
    <p>Modify the welcomeText of this Screeps server, and insert this value into it: </p>
    <b>screeps_' . escape_html($_SESSION['account']) . '_online</b>
    <p>You can check the current value <a href="http://' . escape_html($server) . '/api/version/">here</a>.<br>
    Value welcomeText should be inside of <i>~/world/node_modules/@screeps/backend/lib/game/server.js</i> <br><br></p>

	<form method="post" action="#">
		<input type="hidden" name="csrf_token" value="' . escape_html(generate_csrf_token()) . '">
		<table class="login"><tr><td>
		<input type="submit" name="claim" value="CLAIM NOW">
		</td></tr></table>
	</form>';
} else {
    // Server already claimed
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id_user = ?");
    $stmt->execute([$owner]);
    $ownerUser = $stmt->fetch();

    if ($ownerUser && $ownerUser['name'] == $_SESSION['account']) {
        echo '<br><br>This server is already claimed by you!';
    } else {
        echo '
        <h3>How to claim:</h3>
        <p>Modify the welcomeText of this Screeps server, and insert this value into it: </p>
        <b>screeps_' . escape_html($_SESSION['account']) . '_online</b>
        <p>You can check the current value <a href="http://' . escape_html($server) . '/api/version/">here</a>.<br>
        Value welcomeText can be inside of <i>~/world/node_modules/@screeps/backend/lib/game/server.js</i> </p>';

        echo '<br><br>This server is already claimed by someone else!';
    }
}
?>
    </p>


	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019 </div>
</div>

</body>
</html>
