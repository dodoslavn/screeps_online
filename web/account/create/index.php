<?
session_start();
$ini = parse_ini_file("../../../backend/mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");


if (empty($_SESSION['account']))
    { $account = '<a style="color: #ffe799;" href="/account" >Log in</a>'; }
else 
    { header("location: /account"); }
    
    
if (! empty($_POST['submit']))
    {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $p1 = mysqli_real_escape_string($conn, $_POST['password1']);
    $p2 = mysqli_real_escape_string($conn, $_POST['password2']);
    
    if (!empty($email) and !empty($username) and !empty($p1) and !empty($p2))
        {
        $p1 = str_replace(' ', '', $p1);
        $username = str_replace(' ', '', $username);
        $email = str_replace(' ', '', $email);
        $password = crypt($p1,'screeps');
        
        if (strlen($username) < 3)
            $err = "Username is too short!";
        if (strlen($p1) < 6)
            $err = "Password is too short!";
        if ($p1 != $p2)
            $err = "Password does not match!";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
            $err = 'Email format is incorrect!'; 
            
        $sql = mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE email = '".$email."';" );
		$sql = mysqli_fetch_array($sql);
		if ($sql['c'] != 0)
            $err = 'Email is already in use!'; 
            
        $sql = mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE name = '".$username."';" );
        echo "SELECT COUNT(*) AS c FROM users WHERE name = '".$username."';";
		$sql = mysqli_fetch_array($sql);
		echo $sql['c'];
		if ($sql['c'] != 0)
            $err = 'Username is already in use!'; 
            
        $sql = mysqli_query($conn, "INSERT INTO users VALUES (null,'".$username."','".$email."','".$password."')" );
        
        

        if (empty($err))
            header('Location: /account/login');
        }
    else $err = "Account creation failed! Some fields were empty!";
    }
    
    

?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta name="description" content="Create account screeps.online" >
	<meta name="keywords" content="screeps private server community create account" > 
	<meta name="author" content="Dodoslav Novák" > 
	<link rel="stylesheet" type="text/css" href="/default.css" media="screen" >
	<link rel="shortcut icon" type="image/png" href="/favicon.png"/>
	<title>Add account - Screeps private server list</title>
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
	<h1>Create account</h1>
	
<? 
if (!empty($err))
    echo '<div style="width: 50%; margin:0 auto 0 auto; padding: 5px; text-align: center; background-color: #aa1111; border: solid 1px red"> '.$err.' </div> <br>';
?>

<form method="post" action="#">
    <table class="login">
        <tr>
        <tr>
            <td>Email: </td>
            <td><input type="text" value="" name="email"></td>
        </tr>
            <td>Username: </td>
            <td><input type="text" value="" name="username"></td>
        <tr>
            <td>Password: </td>
            <td><input type="password" value="" name="password1"></td>
        </tr>
        <tr>
            <td>Confirm password: </td>
            <td><input type="password" value="" name="password2"></td>
        </tr>
        <tr> <td></td>
            <td><br><input type="submit" value="Submit" name="submit"></td>
        </tr>
    </table>
</form>

	<div class="footer">Dodoslav Novák | screeps@fordo.sk | PHP & MySQL | 2019</div>
</div>
</body>
</html>
