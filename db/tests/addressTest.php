<?php
// Based on https://www.safaribooksonline.com/library/view/phpunit-essentials/9781783283439/ch09s04.html
require_once(__DIR__ . "/../../config/admin/config.php");
require_once(__DIR__ . "/../../config/admin/doctrine.php");
require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/../src/Address.php");
require_once(__DIR__ . "/../AddressManager.php");

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

class AddressDoctrineTest extends TestCase
{

  /**
  * @var Doctrine\ORM\EntityManager
  */
  static protected $em = null;

  private $testDesc1 = "___TEST ADDRESS 1___";
  private $testDesc2 = "___TEST ADDRESS 2___";
  private $testAddress1a = "c/o GloboCorp Handlings Pty Ltd Gmbh";
  private $testAddress1b = "0989, 8/D, China World Tower";
  private $testAddress2a = "12345 Main Street";
  private $testAddress2b = "No 1. JianGuiMenWai Ave";
  private $testAddress3a = "Unit 1072-123";
  private $testAddress3b = "Sector 5-A, Kujun-kun Ring 3";
  private $testCity1 = "Portland";
  private $testCity2 = "Beijing";
  private $testState1 = "Oregon";
  private $testState2 = "N/A";
  private $testZipcode1 = "97206";
  private $testZipcode2 = "100004";
  private $testCountry1 = "United States";
  private $testCountry2 = "P.R. China";

  public static function setUpBeforeClass() {
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
      $query = self::$em->createQuery('DELETE Address a WHERE a.description IN (?0,?1)');
      $query->setParameters(array($this->testDesc1, $this->testDesc2));
      $query->getResult();
    }
  }

  protected function tearDown() {
    parent::tearDown();
  }

  protected function createEntityManager() {
    return self::$em;
  }

  /*
  // Uncomment this to make a default administrator
  // TODO: Make a utility or something to do this.
  public function testMakeDefaultAdmin() {
    $am = new AdminManager(self::$em);
    $admin = new Admin();
    $admin->setEmail("admin@jeromie.com");
    $admin->setPassword("password");
    $am->store($admin);
  }
  */

  public function testEmptyConstructor() {
    $addr = new Address();
    $this->assertEmpty($addr->getId());
  }

  public function testCreateAddr() {

    // Setup a new Admin object
    $addr = new Address();
    $am = new AddressManager(self::$em);

    // Provide some basic inputs
    $addr->setDescription($this->testDesc1);
    $addr->setAddress1($this->testAddress1a);
    $addr->setAddress2($this->testAddress2a);
    $addr->setAddress3($this->testAddress3a);
    $addr->setCity($this->testCity1);
    $addr->setState($this->testState1);
    $addr->setCountry($this->testCountry1);
    $addr->setZipcode($this->testZipcode1);

    $this->assertEmpty($addr->getId(), "A new Address object should not have an ID before it is stored");

    // Make a shallow copy of the pre-storage object
    $addr2 = clone $addr;

    $this->assertTrue($am->store($addr));
    $this->assertNotEmpty($addr->getId(), "The ORM should assign an ID to the object once stored");

    // Everything else should equate
    $this->assertEquals($addr->getDescription(), $addr2->getDescription(), "Stored and Retrieved values should equate.");
    $this->assertEquals($addr->getAddress1(), $addr2->getAddress1(), "Stored and Retrieved values should equate.");
    $this->assertEquals($addr->getAddress2(), $addr2->getAddress2(), "Stored and Retrieved values should equate.");
    $this->assertEquals($addr->getAddress3(), $addr2->getAddress3(), "Stored and Retrieved values should equate.");
    $this->assertEquals($addr->getCity(), $addr2->getCity(), "Stored and Retrieved values should equate.");
    $this->assertEquals($addr->getCountry(), $addr2->getCountry(), "Stored and Retrieved values should equate.");
    $this->assertEquals($addr->getState(), $addr2->getState(), "Stored and Retrieved values should equate.");
    $this->assertEquals($addr->getZipcode(), $addr2->getZipcode(), "Stored and Retrieved values should equate.");

    // Save this Admin object for the next test
    return $addr;
  }

  /**
  * @depends testCreateAddr
  */
  public function testLoadAddr(Address $oldAddr) {

    $am = new AddressManager(self::$em);

    // Load a copy of the admin object we have in the class scope from the database
    $loadedAddr = $am->load($oldAddr->getId());

    $this->assertEquals($loadedAddr, $oldAddr, "The loaded and in-memory Address objects should be identical");
  }

  public function testSetAndLoad() {

    $am = new AddressManager(self::$em);
    $addr = new Address();

    // Provide some basic inputs
    $addr->setDescription($this->testDesc1);
    $addr->setAddress1($this->testAddress1a);
    $addr->setAddress2($this->testAddress2a);
    $addr->setAddress3($this->testAddress3a);
    $addr->setCity($this->testCity1);
    $addr->setState($this->testState1);
    $addr->setCountry($this->testCountry1);
    $addr->setZipcode($this->testZipcode1);
    $am->store($addr);

    $addr->setDescription($this->testDesc2);
    $addr->setAddress1($this->testAddress1b);
    $addr->setAddress2($this->testAddress2b);
    $addr->setAddress3($this->testAddress3b);
    $addr->setCity($this->testCity2);
    $addr->setState($this->testState2);
    $addr->setCountry($this->testCountry2);
    $addr->setZipcode($this->testZipcode2);
    $am->store($addr);

    $loadedAddr = $am->load($addr->getId());

    $this->assertEquals($loadedAddr, $addr, "Loaded and Set objects should equate");
  }


  public function testNullableFields() {

    $am = new AddressManager(self::$em);
    $addr = new Address();

    // Some fields are nullable
    $addr->setDescription($this->testDesc1);
    $addr->setAddress1($this->testAddress1a);
    // $addr->setAddress2($this->testAddress2a);
    // $addr->setAddress3($this->testAddress3a);
    $addr->setCity($this->testCity1);
    // $addr->setState($this->testState1);
    $addr->setCountry($this->testCountry1);
    $addr->setZipcode($this->testZipcode1);
    $this->assertTrue($am->store($addr), "Some fields should accept null values");
  }

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullDescription() {
    $am = new AddressManager(self::$em);
    $addr = new Address();

    // The description field is required
    // $addr->setDescription($this->testDesc1);
    $addr->setAddress1($this->testAddress1a);
    $addr->setAddress2($this->testAddress2a);
    $addr->setAddress3($this->testAddress3a);
    $addr->setCity($this->testCity1);
    $addr->setState($this->testState1);
    $addr->setZipcode($this->testZipcode1);
    $addr->setCountry($this->testCountry1);
    $this->assertTrue($am->store($addr), "The description field must not be null");

    $admin->setEmail(null);
    $this->assertFalse($am->store($admin));
  }

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullAddress1() {
    $am = new AddressManager(self::$em);
    $addr = new Address();

    // The description field is required
    $addr->setDescription($this->testDesc1);
    // $addr->setAddress1($this->testAddress1a);
    $addr->setAddress2($this->testAddress2a);
    $addr->setAddress3($this->testAddress3a);
    $addr->setCity($this->testCity1);
    $addr->setState($this->testState1);
    $addr->setZipcode($this->testZipcode1);
    $addr->setCountry($this->testCountry1);
    $this->assertTrue($am->store($addr), "The address1 field must not be null");

    $admin->setEmail(null);
    $this->assertFalse($am->store($admin));
  }

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullCity() {
    $am = new AddressManager(self::$em);
    $addr = new Address();

    // The city field is required
    $addr->setDescription($this->testDesc1);
    $addr->setAddress1($this->testAddress1a);
    $addr->setAddress2($this->testAddress2a);
    $addr->setAddress3($this->testAddress3a);
    // $addr->setCity($this->testCity1);
    $addr->setState($this->testState1);
    $addr->setZipcode($this->testZipcode1);
    $addr->setCountry($this->testCountry1);
    $this->assertTrue($am->store($addr), "The city field must not be null");

    $admin->setEmail(null);
    $this->assertFalse($am->store($admin));
  }

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullCountry() {
    $am = new AddressManager(self::$em);
    $addr = new Address();

    // The country field is required
    $addr->setDescription($this->testDesc1);
    $addr->setAddress1($this->testAddress1a);
    $addr->setAddress2($this->testAddress2a);
    $addr->setAddress3($this->testAddress3a);
    $addr->setCity($this->testCity1);
    $addr->setState($this->testState1);
    $addr->setZipcode($this->testZipcode1);
    // $addr->setCountry($this->testCountry1);
    $this->assertTrue($am->store($addr), "The city field must not be null");

    $admin->setEmail(null);
    $this->assertFalse($am->store($admin));
  }

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullZipcode() {
    $am = new AddressManager(self::$em);
    $addr = new Address();

    // The country field is required
    $addr->setDescription($this->testDesc1);
    $addr->setAddress1($this->testAddress1a);
    $addr->setAddress2($this->testAddress2a);
    $addr->setAddress3($this->testAddress3a);
    $addr->setCity($this->testCity1);
    $addr->setState($this->testState1);
    // $addr->setZipcode($this->testZipcode1);
    $addr->setCountry($this->testCountry1);
    $this->assertTrue($am->store($addr), "The city field must not be null");

    $admin->setEmail(null);
    $this->assertFalse($am->store($admin));
  }

  // The unique description constraint should prevent us from creating multiple
  // addresses with identical descriptions
  /**
  * @expectedException Doctrine\DBAL\Exception\UniqueConstraintViolationException
  */
  public function testUniqueDescriptionConstraint() {

    // Setup a new Admin object
    $am = new AddressManager(self::$em);
    $addr = new Address();
    $addr2 = new Address();

    // The country field is required
    $addr->setDescription($this->testDesc1);
    $addr->setAddress1($this->testAddress1a);
    $addr->setAddress2($this->testAddress2a);
    $addr->setAddress3($this->testAddress3a);
    $addr->setCity($this->testCity1);
    $addr->setState($this->testState1);
    $addr->setZipcode($this->testZipcode1);
    $addr->setCountry($this->testCountry1);
    $am->store($addr);

    // The country field is required
    $addr2->setDescription($this->testDesc1);
    $addr2->setAddress1($this->testAddress1a);
    $addr2->setAddress2($this->testAddress2a);
    $addr2->setAddress3($this->testAddress3a);
    $addr2->setCity($this->testCity1);
    $addr2->setState($this->testState1);
    $addr2->setZipcode($this->testZipcode1);
    $addr2->setCountry($this->testCountry1);
    $this->assertFalse($am->store($addr2), "You should not be able to store two addresses with the same description");
  }
}
?>
