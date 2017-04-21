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
$tpl = $mustache->loadTemplate('index');

$data['title'] = 'Project Phoenix - Admin';
$data['page_title'] = 'Hello World - Now With Cool Layout';

echo $tpl->render($data);
?>
