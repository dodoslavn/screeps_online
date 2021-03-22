<?
session_start();

date_default_timezone_set("Europe/Bratislava");

$ini = parse_ini_file("../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");


if (!empty($_POST['server']))
	{
	$_POST['server'] = mysqli_real_escape_string($conn, $_POST['server']);
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
	<meta name="description" content="screeps priavte community server list" >
	<meta name="keywords" content="screeps private community server list game" > 
	<meta name="author" content="Dodoslav Novák" > 
	<link rel="stylesheet" type="text/css" href="default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="favicon.png"/>
	<title>Screeps private server list</title>
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

	<h1>Screeps - community private server list</h1>

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
			<?
			$sql = mysqli_query($conn, "SELECT * FROM server_info JOIN server_list ON server_list.id_server = server_info.server_id WHERE last_check > DATE(NOW() - INTERVAL 3 DAY) ORDER BY players_current DESC");
			while($row = mysqli_fetch_array($sql))
				{
				if ( $row['online'] == 1  ) $color = "green";	
				else $color = "red";

				$array = explode(":", $row['address']);
				$address = $array[0];
				$port = $array[1];
				
				$row['last_check'] = strtotime($row['last_check']);
				$row['last_check'] = date('H:i:s d.m.Y', $row['last_check']);
				
				if ($row['availability'] == '') $row['availability'] = 0;
				echo "<tr>";
				echo "<td>".$row['name']."</td>";
				echo "<td>".$address."</td>";
				echo "<td>".$port."</td>";
				#echo "<td>".$row['name']."</td>";
				echo "<td style='background-color:".$color."'> </td>";
				echo "<td>".$row['players_current']."</td>";
				echo "<td>".$row['version']."</td>";
				echo "<td>".$row['last_check']."</td>";
				echo "<td>".$row['availability']."%</td>";
				echo '<td><a class="server" href="/server/?server='.$address.':'.$port.'">Info</a></td>';
				echo "</tr>";
				} 
			?>
	</table>
	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019 | Timezone Europe/Bratislava</div>
</div>
 
</body>
</html>
