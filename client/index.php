<?php

require_once(__DIR__ . "/../config/client/config.php");

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/client/mustache.php');
$tpl = $mustache->loadTemplate('adamTestPage');

$data['title'] = 'Project Phoenix - Client';

echo $tpl->render($data);

?>