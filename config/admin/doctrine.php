<?php

// Based on Doctrine tutorial, here:
// http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

require_once(__DIR__ . "/../config.php");

// Borrowed From:
// https://stackoverflow.com/questions/33899120/symfony-doctrine-get-entries-count-by-day-on-a-datetime-field

/**
* Extracts the date form a timestamp (Mysql function)
* DQLDate :: DATE ( date as Y-m-d )
*/
class DQLDate extends FunctionNode
{
  protected $date;
  public function getSql(SqlWalker $sqlWalker) {
    return 'DATE(' . $sqlWalker->walkArithmeticPrimary($this->date) .')';
  }

  public function parse(Parser $parser)
  {
    $parser->match(Lexer::T_IDENTIFIER);
    $parser->match(Lexer::T_OPEN_PARENTHESIS);
    $this->date = $parser->ArithmeticPrimary();
    $parser->match(Lexer::T_CLOSE_PARENTHESIS);
   }
}

class EntityManagerFactory
{

  private $entityManager;

  public function __construct() {

    // Create a simple "default" Doctrone ORM config file for Annotations
    $isDevMode = true;

    // Database configuration pg_parameter_status
    $connectionParams = array(
    'url' => 'mysql://root:root@127.0.0.1:8889/phoenix_admin'
    );

    $config = Setup::createAnnotationMetadataConfiguration(array($GLOBALS['DOCUMENT_ROOT'] . "/db/src"), $isDevMode);
    $config->addCustomDateTimeFunction('DATE', 'DQLDate');
    
    $this->entityManager = EntityManager::create($connectionParams, $config);
  }

  public function getEntityManager() {
    return $this->entityManager;
  }
}

$emf = new EntityManagerFactory();
$entityManager = $emf->getEntityManager();
?>
