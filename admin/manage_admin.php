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
require($_SERVER['DOCUMENT_ROOT'] . '/config/admin/mustache.php');
$tpl = $mustache->loadTemplate('manage_admin');

// Mocking the logged in state
if( array_key_exists('logged_in',$_GET) && $_GET['logged_in'] == "true") {
  $data['user_info'] = true;
  $data['page_title'] = 'Manage Administrators';
} else {
  $data['page_title'] = 'Log In';
}
$data['title'] = 'Project Phoenix - Admin';

echo $tpl->render($data);
?>
