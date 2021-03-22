<?
session_start();
$ini = parse_ini_file("../../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");


if (!empty($_POST['server']))
	{
	$_POST['server'] = mysqli_real_escape_string($conn, $_POST['server']);

	$array = explode(":", $_POST['server']);
	$address = $array[0];	
	$port = $array[1];	
	$msg == "";

	$check = true;
	if (!is_numeric($port)) $check = false;
	else
		{ if (($port < 1000) or ($port > 50000)) $check = false; }

	if ( (!filter_var($address, FILTER_VALIDATE_IP)) and (!filter_var(gethostbyname($address), FILTER_VALIDATE_IP)) )
		$check = false;
	

        if ($check == false)
                $msg = '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: #aa1111; border: solid 1px red"> Error: Server was not added due to incorrect address format! </div>';
	else
		{
		$sql = mysqli_query($conn, "SELECT COUNT(*) AS c FROM server_list WHERE address = '".$_POST['server']."'");
		$sql = mysqli_fetch_array($sql);
		if ( $sql['c'] != 0 ) 
			{
			$check = false;
			$msg = '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: #aa1111; border: solid 1px red"> Error: Server is already in database! </div>';
			}
		}

	if ($check != false )
		mysqli_query($conn, "INSERT INTO server_list(id_server, address) VALUES (NULL, '".$_POST['server']."')");
	}

if (empty($_SESSION['account']))
    { $account = '<a href="/account/login">Log in</a>'; }
else  
    { $account = '<a href="/account">Account</a>'; }
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
			<? echo $account; ?>
		</div>
	</div>
</div>

<div class="container">
	<h1>Add your Screeps private server into list:</h1>
<? echo $msg; ?>

	<h3><br>Enter domain or IP with port of your Screeps private server:</h3>
	<form action="" method="post">
		<center>http://<input type="text" value="screeps.com:21025" name="server">/ 
		<input type="submit" value="Add server"> </center>
	</form>
	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019 </div>
</div>



</body>
</html>
