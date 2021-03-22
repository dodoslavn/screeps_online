<?

$ini = parse_ini_file("../../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");



?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="About Screeps community private servers list" >
	<meta name="keywords" content="screeps private server community add server" > 
	<meta name="author" content="Dodoslav Novák" > 
	<link rel="stylesheet" type="text/css" href="https://screeps.online/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="https://screeps.online/favicon.png"/>
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
		</div>
	</div>
</div>
<div class="container">
	<h1>About this web page</h1>

	<h3>Why?</h3>
	<p>Well, in time when this project was created, game Screeps, had list of private server in main manu of game.
		However, those were added by developers manually, and server owners didnt have much control of it. 
		For example, you cant even define the name of your server, which will be used on the list.</p>
	<p>There is also forum, where owners can add they own server and share some details, but most of the servers are dead / not working.</p>

	<h3>Who?</h3>
	<p>Iam just owner of few Screeps(and other) servers, sometimes I do some webpage, and I think this project, will help community and game overall. 
		Players who cant play on Official server anymore, can still experiment on those private / community server, and eventualy come back to the "Master - official" server.</p>

	<h3>How?</h3>
	<p>The way this page works, it is doing simple HTTP requests via PHP to the API of Screeps, every minute. If server is added and online, it will appear on server list. 
		If server is offline for more then 3 days, it will disapear, however it will stay in database and will be scanned. So once it comes online, it will appear in list again automatically. </p>
	<p>If you want, I can share code of this project, as I dont have that much time to tune it, and also Iam not the best web dev. :-) . Any suggestion and help are welcome. For community!</p>
	
	<h3>Plan</h3>
	<p>In future version, I am planning to add "Info" page of each server, where can be comments section for discussion, list of applied mods on the server, if is password protected.
		Another thing is logging into this page, where people could claim the server is their, and can manually edit server name or anything else about their server.</p>

	<h4>screeps.online</h4>
	Version: RC2<br>
	Creator: Dodoslav Novak<br>
	Contact: screeps@fordo.sk<br>
	
        <h4>Screeps</h4>
	Web: https://screeps.com/ <br>
	Steam: https://store.steampowered.com/app/464350/Screeps/ <br>
	Slack: https://chat.screeps.com/ <br>
	Forum: https://screeps.com/forum/ 

	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019</div>
</div>
</body>
</html>
