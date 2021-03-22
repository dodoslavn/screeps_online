<?
session_start();


date_default_timezone_set("Europe/Bratislava");

$ini = parse_ini_file("../../../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");


if (!empty($_GET['server']))
	{
    	$server = mysqli_real_escape_string($conn, $_GET['server']);
	}
else
    header("Location: /");

if (empty($_SESSION['account']))
    { header("Location: /account/login"); }
else 
    { $account = '<a href="/account">Account</a>'; }


if (!empty($_POST['claim']))
	{
	$_SESSION['claim_attempt']++;
	if ( $_SESSION['claim_attempt'] < 10 )
		{
		$sql = mysqli_query($conn, "SELECT COUNT(*) AS c FROM server_info JOIN server_list ON server_id = id_server WHERE address = '".$server."' ");
		$row = mysqli_fetch_array($sql);
		if ( $row['c'] != 1 ) 
			header("Location: /");

        	$url = "http://".$server."/api/version/";
	        $curl = curl_init();
	        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	        curl_setopt($curl, CURLOPT_URL, $url);
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        $output = curl_exec($curl);
        	curl_close($curl);		
		if ( strpos($output, "screeps_".$_SESSION['account']."_online") == false )
			{
			}
		else
			{
			echo "huraa";
			$sql = mysqli_query($conn, "SELECT id_user FROM users WHERE name = '".$_SESSION['account']."'");
			$row = mysqli_fetch_array($sql);
			$id_user = $row['id_user'];
			$sql = mysqli_query($conn, "UPDATE server_info JOIN server_list ON server_id = id_server SET user_id = ".$id_user." WHERE address = '".$server."'");
			}
		}
	}
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Claim Screeps private server <? echo $server; ?>" >
	<meta name="keywords" content="screeps private community server clam game" > 
	<meta name="author" content="Dodoslav Novák" > 
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title><? echo $server; ?> - claim Screeps private server</title>
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
			<? echo $account; ?>
		</div>
	</div>
</div>

<div class="container">

	<h1>Screeps server: <? echo $server ?></h1>

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
			<?
			$sql = mysqli_query($conn, "SELECT * FROM server_info JOIN server_list ON server_list.id_server = server_info.server_id WHERE address = '".$server."'");
			while($row = mysqli_fetch_array($sql))
				{
				if ( $row['online'] == 1  ) $color = "green";	
				else $color = "red";

				$array = explode(":", $row['address']);
				$address = $array[0];
				$port = $array[1];
				$description = $row['description'];
				$owner = $row['user_id'];
				
				
				$row['last_check'] = strtotime($row['last_check']);
				$row['last_check'] = date('H:i:s d.m.Y', $row['last_check']);
				
				if ($row['availability'] == '') $row['availability'] = 0;
				echo "<tr>";
				echo "<td>".$row['name']."</td>";
				echo "<td>".$address."</td>";
				echo "<td>".$port."</td>";
				echo "<td style='background-color:".$color."'> </td>";
				echo "<td>".$row['players_current']."</td>";
				echo "<td>".$row['version']."</td>";
				echo "<td>".$row['last_check']."</td>";
				echo "<td>".$row['availability']."%</td>";
				echo "</tr>";
				} 
			?>
	</table>
	
<? 
if (empty($owner))
	{
       echo '
        <h3>How to claim:</h3>
        <p>Modify the welcomeText of this Screeps server, and insert this value into it: </p>
        <b>screeps_'.$_SESSION['account'].'_online</b>
        <p>You can check the current value <a href="http://'.$server.'/api/version/">here</a>.<br>
        Value welcomeText should be inside of <i>~/world/node_modules/@screeps/backend/lib/game/server.js</i> <br><br></p>
	
	<form method="post" action="#">
		<table class="login"><tr><td>
		<input type="submit" name="claim" value="CLAIM NOW">
		</td></tr></table>
	</form>';
	}
else
    {
    $sql = mysqli_query($conn, "SELECT name FROM users WHERE id_user = '".$owner."'");
    $row = mysqli_fetch_array($sql);
    if ($row['name'] == $_SESSION['account'])
        echo '<br><br>This server is already claimed by you!';
    else
	{
	echo '
        <h3>How to claim:</h3>
        <p>Modify the welcomeText of this Screeps server, and insert this value into it: </p>
        <b>screeps_'.$_SESSION['account'].'_online</b>
        <p>You can check the current value <a href="http://'.$server.'/api/version/">here</a>.<br>
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
