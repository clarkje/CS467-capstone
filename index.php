<?php
ini_set('display_errors', 'On');

/*
// Setup the MySQL connection
require('config/mysql.php');
$mysqli = new mysqli($db_host,$db_user,$db_pass,$db_schema);

if($mysqli->connect_errno){
	echo "Connection error " . $mysqli->connect_errno . " " . $mysqli->connect_error;
}
*/

// Setup the template engine
require('config/client/mustache.php');
$tpl = $mustache->loadTemplate('helloWorld');

// Mocking the logged in state
if($_GET['logged_in'] == true) {
  $data['user_info'] = true;
}

echo $tpl->render($data);
?>
