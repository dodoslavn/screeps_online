<?

date_default_timezone_set("Europe/Bratislava");

$ini = parse_ini_file("../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");


if (!empty($_POST['server']))
	{
	$_POST['server'] = mysqli_real_escape_string($conn, $_POST['server']);
	mysqli_query($conn, "INSERT INTO server_list(id_server, address) VALUES (NULL, '".$_POST['server']."')");
	}


$datum = StrFTime("%d.%m.%Y", Time());
$time = StrFTime("%H:%M", Time());
$ip = $_SERVER['REMOTE_ADDR'];
$binfo = $_SERVER['HTTP_USER_AGENT'];
$stime = time();
session_start();
$sid = session_id();


$bot = 0;
if ( $binfo == "Zabbix" )
        $bot = 1;


$result = MySQLi_Query($conn, "SELECT COUNT(id) AS riadky FROM web_statistic WHERE sid = '$sid';");
$mysql = MySQLi_Fetch_Array($conn, $result);



if ( $mysql['riadky'] == 0 AND $bot == '0')
        MySQLi_Query($conn, "INSERT INTO web_statistic VALUES (NULL, '', '', '', '$binfo', '$datum', '$time', '$ip', '$stime', '$sid')");


?>

<html>
<head>
	<meta name="google-site-verification" content="aMoh4yu2FyyT2Ucrqmzb65wSSbD_Wp5xBKKrf9B2o3o" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Screeps private server list" >
	<meta name="keywords" content="screeps private community server list game" > 
	<meta name="author" content="Dodoslav Novák" > 
	<link rel="stylesheet" type="text/css" href="https://screeps.online/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="https://screeps.online/favicon.png"/>
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
		</div>
	</div>
</div>

<div class="container">

	<h1>Screeps - community private server list</h1>

	<table>
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
				echo "<td>Info</td>";
				echo "</tr>";
				}
			?>
	</table>
	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019</div>
</div>

</body>
</html>
