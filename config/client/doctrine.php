<?php

// Based on Doctrine tutorial, here:
// http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once(__DIR__ . "/../config.php");

// TODO: It probably makes sense to have this be a Singleton, but I'm not 100% sure it matters.
class EntityManagerFactory
{

  private $entityManager;

  public function __construct() {

    // Create a simple "default" Doctrone ORM config file for Annotations
    $isDevMode = true;

    // Database configuration pg_parameter_status
    // TODO: Setup different accounts for client and admin on the database w/ appropriate perms.
    $connectionParams = array(
      'url' => 'mysql://root:root@127.0.0.1:8889/phoenix_admin'
    );

    $config = Setup::createAnnotationMetadataConfiguration(array($GLOBALS['docroot'] . "/db/src"), $isDevMode);
    $this->entityManager = EntityManager::create($connectionParams, $config);
  }

  public function getEntityManager() {
    return $this->entityManager;
  }
}

$emf = new EntityManagerFactory();
$entityManager = $emf->getEntityManager();
?>
