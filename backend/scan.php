<?
error_reporting(E_ALL);
ini_set('display_errors', 1);


$ini = parse_ini_file("mysql.ini");
$conn = mysqli_connect($ini['host'], $ini['user'], $ini['password'], $ini['database']);
if (!$conn) die("database");


$sql = mysqli_query($conn, "SELECT * FROM server_list LEFT JOIN server_info ON id_server = server_id ORDER BY online");
while($row = mysqli_fetch_array($sql))
	{
	$url = "http://".$row['address']."/api/version/";

        $curl = curl_init(); 
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 10); 
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($curl); 
        curl_close($curl);      
	
	$sql1 = mysqli_query($conn, "SELECT COUNT(*) AS count FROM server_info WHERE server_id = ".$row['id_server']." ");
	$sql1 = mysqli_fetch_array($sql1);
	if ($sql1['count'] == 0)
		{
		$sql1 = mysqli_query($conn, "INSERT INTO server_info(id_info, server_id) VALUES (NULL,".$row['id_server'].")");
		$sql1 = mysqli_query($conn, "INSERT INTO server_availability(id_availability, server_id, checks, successful) VALUES (NULL,".$row['id_server'].", 0, 0)");
		echo "\nAdding: ".$row['address']." ";
		}
	else
		{ echo "\nAlready exist (".$row['address'].") "; }

	if (empty($output))
		{
		echo "ERROR";
		mysqli_query($conn, "UPDATE server_info SET players_current = '0', online = '2' WHERE server_id = '".$row['id_server']."';");
		mysqli_query($conn, "UPDATE server_availability SET checks = checks + 1 WHERE server_id = '".$row['id_server']."';");
		}
	else
		{
		echo "OK";
        	$array = json_decode($output, true);
   		$version = mysqli_real_escape_string($conn, $array["packageVersion"]);
        	$users = mysqli_real_escape_string($conn, $array["users"]);
		

		mysqli_query($conn, "UPDATE server_availability SET checks = checks + 1, successful = successful + 1 WHERE server_id = '".$row['id_server']."';");
        	mysqli_query($conn, "UPDATE server_info SET version = '".$version."', players_current = '".$users."', online = '1', last_check = now() WHERE server_id = '".$row['id_server']."';");

		$pom = mysqli_query($conn, "SELECT checks, successful FROM server_availability WHERE server_id = '".$row['id_server']."';");
		$pom = mysqli_fetch_array($pom);
		$pom = ( $pom['successful'] * 100) / $pom['checks'];
		mysqli_query($conn, "UPDATE server_info SET availability = '".$pom."' WHERE server_id = '".$row['id_server']."';");
		}
	}
echo "\n";
?>
