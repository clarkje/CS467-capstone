<?php

// Setup the template engine
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/mustache/mustache/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();

$mustache = new Mustache_Engine(array(
    'template_class_prefix' => 'templates',
    'cache' => dirname(__FILE__).'tmp/cache',
    'cache_file_mode' => 0666,
    'cache_lambda_templates' => true,
    'loader' => new Mustache_Loader_FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/views/client'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/views/client/partials'),
    'escape' => function($value) {
        return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
    },
    'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
    'strict_callables' => true,
    'pragmas' => [Mustache_Engine::PRAGMA_FILTERS],
));
?>
