<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require_once 'config/admin/doctrine.php';

//return ConsoleRunner::createHelperSet($entityManager);
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
