<?php

// Based on Doctrine tutorial, here:
// http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require_once 'config/config.php';
require_once 'config/admin/config.php';
require_once 'config/admin/doctrine.php';

$emf = new EntityManagerFactory();
$entityManager = $emf->getEntityManager();


//return ConsoleRunner::createHelperSet($entityManager);
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
