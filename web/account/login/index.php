<?
session_start();
$ini = parse_ini_file("../../../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");


if (empty($_SESSION['account']))
    { $account = '<a style="color: #ffe799;" href="/account" >Log in</a>'; }
else 
    { header("Location: /account"); }

    
    
if (! empty($_POST['submit']))
    {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    if (!empty($password) and !empty($username) )
        {
        $password = crypt($password,'screeps');
        
        $sql = mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE name = '".$username."' and password = '".$password."';" );
	$sql = mysqli_fetch_array($sql);
	if ($sql['c'] != 1)
            $err = 'Wrong cedentials!';
        else
            {
            $_SESSION['account'] = $username;
            }
        }
    else
        $err = "Empty credentials!";
        
    }
    
    

if (empty($_SESSION['account']))
    { $account = '<a style="color: #ffe799;" href="/account" >Log in</a>'; }
else 
    { header("Location: /account"); }
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Login into screeps.online" >
	<meta name="keywords" content="screeps private server community login" > 
	<meta name="author" content="Dodoslav Novák" > 
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title>Login - Screeps private server list</title>
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
			<? echo $account; ?>
		</div>
	</div>
</div>
<div class="container">
	<h1>Log in</h1>

<? 
if (!empty($err))
    echo '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: #aa1111; border: solid 1px red"> '.$err.' </div> <br>';
?>
	
	
<form method="post" action="#">
    <table class="login">
        <tr>
            <td>Username: </td>
            <td><input type="text" value="" name="username"></td>
        <tr>
            <td>Password: </td>
            <td><input type="password" value="" name="password"></td>
        </tr>
        <tr> <td></td>
            <td><br><input type="submit" value="Submit" name="submit"></td>
        </tr>
    </table>
</form>

    <br><br><br><br>
	<h3>Dont have an account yet?</h3>
	<p>Create your account <a href="/account/create" alt="Create account">here</a>.</p>



	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019</div>
</div>
</body>
</html>
