<?php
session_start();

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
	<meta name="description" content="About screeps.dodoslav.eu community private server list" >
	<meta name="keywords" content="screeps private server community about" >
	<meta name="author" content="Dodoslav Novák" >
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title>About - Screeps private server list</title>
</head>
<body>
<div class="header">
	<div class="head">
		<div class="logo">
		</div>
		<div class="panel">
			<a href="/" >Server list</a>
			<a href="/add" >Add server</a>
			<a style="color: #ffe799;" href="/about" >About</a>
			<?= $account ?>
		</div>
	</div>
</div>
<div class="container">
	<h1>About</h1>

    <br><br>
	<h3>What is Screeps?</h3>
	<p>
	    Screeps is an open-source MMO RTS sandbox game for programmers, wherein the core mechanic is programming your units AI. You control your colony by writing JavaScript which operate 24/7 in the single persistent real-time world filled by other players on par with you.
	</p>

	<h3>What is screeps.dodoslav.eu?</h3>
	<p>
        Screeps.dodoslav.eu is a community-driven directory for private Screeps servers. It helps players discover new servers and allows server owners to claim and manage their listings.
	</p>

    <h3>Features:</h3>
    <ul>
        <li>Browse active Screeps private servers</li>
        <li>Real-time player counts and availability tracking</li>
        <li>Server claiming system for owners</li>
        <li>Secure user authentication</li>
    </ul>

	<h3>Links:</h3>
	<p>
	    Official website: <a href="https://screeps.com/">https://screeps.com/</a><br>
	    Steam: <a href="https://store.steampowered.com/app/464350/Screeps/">https://store.steampowered.com/app/464350/Screeps/</a><br>
	    Community: <a href="https://chat.screeps.com/">https://chat.screeps.com/</a>
	</p>

	<h3>Contact:</h3>
	<p>
	    For questions or issues, contact: screeps@dodoslav.eu
	</p>

	<div class="footer">Dodoslav Novák | screeps@dodoslav.eu | PHP & MySQL | 2019</div>
</div>
</body>
</html>
