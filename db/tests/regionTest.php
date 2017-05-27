<?php
// Based on https://www.safaribooksonline.com/library/view/phpunit-essentials/9781783283439/ch09s04.html
require_once(__DIR__ . "/../../config/admin/config.php");
require_once(__DIR__ . "/../../config/admin/doctrine.php");
require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/../src/Region.php");
require_once(__DIR__ . "/../RegionManager.php");

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

// TODO: Test loadByName()

class RegionDoctrineTest extends TestCase
{

  /**
  * @var Doctrine\ORM\EntityManager
  */
  static protected $em = null;

  private $testRegion1 = "___TEST_REGION_1____";
  private $testRegion2 = "___TEST_REGION_2____";

  public static function setUpBeforeClass() {
    $emFactory = new EntityManagerFactory();
    self::$em = $emFactory->getEntityManager();
  }

  protected function setUp() {

    // For tests that don't have dependencies, clear out any test user entries
    // Borrowed from:
    // http://stackoverflow.com/questions/18426085/is-it-possible-to-use-phpunit-depends-without-calling-teardown-and-setup-betwee

    if (!$this->hasDependencies()) {
      $emFactory = new EntityManagerFactory();
      self::$em = $emFactory->getEntityManager();

      // Just blow away all instances of our test users between tests.
      // From example at:
      // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/native-sql.html
      $query = self::$em->createQuery('DELETE Region r WHERE r.name IN (?0,?1)');
      $query->setParameters(array($this->testRegion1, $this->testRegion2));
      $query->getResult();
    }
  }


  protected function tearDown() {
    self::$em->clear();
    parent::tearDown();
  }

  protected function createEntityManager() {
    return self::$em;
  }

  public function testEmptyConstructor() {
    $region = new Region();
    $this->assertEmpty($region->getName(), "Name should be empty at construction");
  }

  public function testCreateRegion() {
    $rm = new RegionManager(self::$em);
    $region = new Region();
    $region->setName($this->testRegion1);
    $rm->store($region);

    $this->assertNotEmpty($region->getId(), "Region ID should be populated at save");
    $this->assertEquals($region->getName(), $this->testRegion1, "Saved region should match input");
  }

  public function testLoadRegion() {
    $rm = new RegionManager(self::$em);
    $region = new Region();
    $region->setName($this->testRegion1);
    $rm->store($region);

    $this->assertNotEmpty($region->getId(), "Region ID should be populated at save");
    $this->assertEquals($region->getName(), $this->testRegion1, "Saved region should match input");

    $loadedRegion = $rm->load($region->getId());
    $this->assertEquals($loadedRegion, $region, "Loaded and in-memory Region objects should equate");
  }

  public function testSetAndStore() {

    $rm = new RegionManager(self::$em);
    $region = new Region();
    $region->setName($this->testRegion1);
    $rm->store($region);

    $region->setName($this->testRegion2);
    $this->assertEquals($region->getName(), $this->testRegion2, "Region name should match last input");
    $rm->store($region);

    $loadedRegion = $rm->load($region->getId());
    $this->assertEquals($loadedRegion->getName(), $region->getName(), "Loaded, updated region should match last input");
  }

  // Note: We intentionally skip testing loadAll() and delete() because of
  // complexity introduced if we ran these an an environment with existing data,
  // and the little value that they actually add.

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullName() {

    $rm = new Regionmanager(self::$em);
    $region = new Region();
    // $region->setName($testRegion1);
    $this->assertFalse($rm->store($region), "Regions must have a name to be saved.");
  }
}
?>
