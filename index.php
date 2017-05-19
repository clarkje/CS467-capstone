<?php
ini_set('display_errors', 'On');

// Setup the template engine
require(__DIR__ . "/config/client/config.php");

$tpl = $mustache->loadTemplate('index');

// If the user is logged in, proceed.  Otherwise, show the login screen.
if(array_key_exists('logged_in',$_SESSION) && $_SESSION['logged_in'] == "true") {
  $data['user_info'] = true;
  $data['page_title'] = 'Employee Recognition System';
} else {
  $data['page_title'] = 'Log In';
}

$data['title'] = 'Project Phoenix - Employee Recognition System';

echo $tpl->render($data);
?>
