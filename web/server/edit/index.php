<?
session_start();

date_default_timezone_set("Europe/Bratislava");

$ini = parse_ini_file("../../../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");


if (!empty($_GET['server']))
	{
    $server = mysqli_real_escape_string($conn, $_GET['server']);
    
    $sql = mysqli_query($conn, "SELECT COUNT(*) AS c FROM server_list WHERE address = '".$server."'");
	$row = mysqli_fetch_array($sql);
	if ($row['c'] != 1)
        header("Location: /");
        
    $sql = mysqli_query($conn, "SELECT users.name AS name FROM server_list JOIN server_info ON server_id = id_server JOIN users ON user_id = id_user WHERE address = '".$server."'");
	$row = mysqli_fetch_array($sql);
	if ($row['name'] != $_SESSION['account'])
        header("Location: /");   
	}
else
    header("Location: /");

if (empty($_SESSION['account']))
    { $account = '<a href="/account/login">Log in</a>'; }
else 
    { $account = '<a href="/account">Account</a>'; }
    
    
if (!empty($_POST['save_name']))
    {
    $server = mysqli_real_escape_string($conn, $_GET['server']);
    $new_name = mysqli_real_escape_string($conn, $_POST['server_name']);
    $sql = mysqli_query($conn, "UPDATE server_info JOIN server_list ON id_server = server_id SET name = '".$new_name."' WHERE address = '".$server."'");
    }
    
if (!empty($_POST['save_desc']))
    {
    $server = mysqli_real_escape_string($conn, $_GET['server']);
    $new_desc = mysqli_real_escape_string($conn, $_POST['server_desc']);
    $sql = mysqli_query($conn, "UPDATE server_info JOIN server_list ON id_server = server_id SET description = '".$new_desc."' WHERE address = '".$server."'");
    }


$sql = mysqli_query($conn, "SELECT description FROM server_list JOIN server_info ON server_id = id_server WHERE address = '".$server."'");
$row = mysqli_fetch_array($sql);
$description = $row['description'];
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Edit decription of screeps private server" >
	<meta name="keywords" content="screeps private community server <? echo $server; ?> game" > 
	<meta name="author" content="Dodoslav Novák" > 
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title><? echo $server; ?> - edit Screeps private server</title>
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
				$server_name = $row['name'];
				
				
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

	
	

    <form method="post" action="#">
        <br><h3>Server name:</h3> <input style="font-family: Times New Roman; background-color: #303030; border-color: black; color: white; width: 300px; height: 30px; font-size: 20px" type="text" value="<? echo $server_name; ?>" name="server_name">
        <input style="font-family: Times New Roman; background-color: #303030; border-color: black; color: white; font-size: 20px; height: 30px" type="submit" value="Save" name="save_name">
    </form>


    <form method="post" action="#">
        <h3>Server description: </h3>
        <textarea style="font-family: Times New Roman; background-color: #303030; border-color: black; color: white;" rows="20" cols="100" type="text" name="server_desc"><? echo $description; ?></textarea><br><br>
        <input style="font-family: Times New Roman; background-color: #303030; border-color: black; color: white; height: 30px; font-size: 20px" type="submit" value="Save" name="save_desc">
    </form>


	
	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019 </div>
</div>
 
</body>
</html>
