<?php

// Based on Doctrine tutorial, here:
// http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once(__DIR__ . "/../config.php");

class EntityManagerFactory
{

  private $entityManager;

  public function __construct() {

    // Create a simple "default" Doctrone ORM config file for Annotations
    $isDevMode = true;

    // Database configuration pg_parameter_status
    $connectionParams = array(
      'url' => 'mysql://phoenixadmin:j94Qm$CZ^%$J@phoenixdb.jeromie.com/jeromiecom_phoenix');    

    $config = Setup::createAnnotationMetadataConfiguration(array($GLOBALS['DOCUMENT_ROOT'] . "/db/src"), $isDevMode);
    $this->entityManager = EntityManager::create($connectionParams, $config);
  }

  public function getEntityManager() {
    return $this->entityManager;
  }
}

$emf = new EntityManagerFactory();
$entityManager = $emf->getEntityManager();
?>
