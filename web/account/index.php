<?
session_start();
$ini = parse_ini_file("../../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");

if (!empty($_SESSION['account']))
    { $account = '<a style="color: #ffe799;" href="/account" >Account</a>'; }
else 
    { header("Location: /account/login"); }

$sql = mysqli_query($conn, "SELECT * FROM users WHERE name = '".$_SESSION['account']."'" );
$sql = mysqli_fetch_array($sql);
$email = $sql['email'];
$id_user = $sql['id_user'];
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="add server to list screeps community private server list" >
	<meta name="keywords" content="screeps private server community add server" > 
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
			<a href="/about" >About</a>
			<a style="color: #ffe799;" href="/account" >Account</a>
		</div>
	</div>
</div>
<div class="container">
	<h1>Your account</h1>



    <br><br>
	<h3>Account information:</h3>
	<p>
        Username:  <? echo $_SESSION['account']; ?><br>
        Email: <? echo $email; ?>
	</p>
	
    <h3>Claimed servers:</h3>
	<p>
<?
$sql = mysqli_query($conn, "SELECT * FROM server_info JOIN server_list ON server_id = id_server WHERE user_id = '".$id_user."'" );
while($row = mysqli_fetch_array($sql))
    {
    if ( empty($row['name']) )
        echo '<a class="server" href="/server/?server='.$row["address"].'">'.$row['address'].'</a><br>';
    else
        echo '<a class="server" href="/server/?server='.$row["address"].'">'.$row['name']." (".$row['address'].")</a><br>";
    
    }
?>
	</p>
	<p>Click <a href="/account/logout" alt="logout">here</a> to logout.</p>



	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019</div>
</div>
</body>
</html>
