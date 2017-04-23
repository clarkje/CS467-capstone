<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "root";
$db_schema = "phoenixdb";

$mysqli = mysqli_init();
$success = mysqli_real_connect(
   $link,
   $host,
   $user,
   $password,
   $db,
   $port
);

<!-- TODO: Turn error reporting off in production -->
if($mysqli->connect_errno){
	echo "Connection error " . $mysqli->connect_errno . " " . $mysqli->connect_error;
}
?>
