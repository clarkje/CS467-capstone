<?php
// Based on https://www.safaribooksonline.com/library/view/phpunit-essentials/9781783283439/ch09s04.html
require_once(__DIR__ . "/../../config/admin/config.php");
require_once(__DIR__ . "/../../config/admin/doctrine.php");
require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/../src/User.php");
require_once(__DIR__ . "/../src/Award.php");
require_once(__DIR__ . "/../UserManager.php");
require_once(__DIR__ . "/../AwardManager.php");

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

class UserDoctrineTest extends TestCase
{

  /**
  * @var Doctrine\ORM\EntityManager
  */
  static protected $em = null;

  // Some testing strings...
  // If I barf these all over the "production" database on accident, they're unlikley to collide with real data
  private $testEmail1 = "___testUser1@jeromie.com";
  private $testEmail2 = "___testUser2@jeromie.com";
  private $testFirstName1 = "Bobson";
  private $testLastName1 = "Dugnutt";
  private $testFirstName2 = "Glenallen";
  private $testLastName2 = "Mixon";
  private $testPassword1 = "password1234";
  private $testPassword2 = "drowssap4321";
  private $testRecipientFirst1 = "____TEST_FIRST_NAME_1____";
  private $testRecipientFirst2 = "____TEST_FIRST_NAME_2____";
  private $testRecipientLast1 = "____TEST_LAST_NAME_1____";
  private $testRecipientLast2 = "____TEST_LAST_NAME_2____";
  private $testRecipientEmail1 = "____testRecipient1@jeromie.com";
  private $testRecipientEmail2 = "____testRecipient2@jeromie.com";

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
      $query = self::$em->createQuery('DELETE Award a WHERE a.recipientFirst IN (?0,?1)');
      $query->setParameters(array($this->testRecipientFirst1, $this->testRecipientFirst2));
      $query->getResult();

      $query = self::$em->createQuery('DELETE User u WHERE u.email IN (?0,?1)');
      $query->setParameters(array($this->testEmail1, $this->testEmail2));
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
    $award = new Award();
    $this->assertEmpty($award->getId(), "ID should be empty at construction");
    $this->assertEmpty($award->getGranter(), "Granter should be empty at construction");
    $this->assertEmpty($award->getRecipientFirst(), "RecipientFirst should be empty at construction");
    $this->assertEmpty($award->getRecipientLast(), "RecipientLastshould be empty at construction");
    $this->assertEmpty($award->getRecipientEmail(), "RecipientEmail should be empty at construction");
    $this->assertEmpty($award->getCertPath(), "CertPath should be empty at construction");
    $this->assertEmpty($award->getCertURL(), "CertUrl should be empty at construction");
    $this->assertEmpty($award->getGrantDate(), "Grant Date should be empty at construction");
  }

  public function testCreateAward() {
    $um = new UserManager(self::$em);
    $user = new User();
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);
    $user->setEmail($this->testEmail1);
    $user->setPassword($this->testPassword1);
    $um->store($user);

    $am = new AwardManager(self::$em);
    $award = new Award();
    $award->setGranter($user);
    $award->setRecipientFirst($this->testRecipientFirst1);
    $award->setRecipientLast($this->testRecipientLast1);
    $award->setRecipientEmail($this->testRecipientEmail1);

    date_default_timezone_set('America/Los_Angeles');
    $award->setGrantDate(new DateTime("now"));

    $this->assertEquals($award->getRecipientFirst(), $this->testRecipientFirst1, "Recipient First should match test value");
    $this->assertEquals($award->getRecipientLast(), $this->testRecipientLast1, "Recipient Last should match test value");
    $this->assertEquals($award->getRecipientEmail(), $this->testRecipientEmail1, "Recipient First should match test value");
    $this->assertEquals($award->getGranter(), $user, "Granter should match the user that granted it");
  }

  public function testLoadAward() {
    $um = new UserManager(self::$em);
    $user = new User();
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);
    $user->setEmail($this->testEmail1);
    $user->setPassword($this->testPassword1);
    $um->store($user);

    $am = new AwardManager(self::$em);
    $award = new Award();
    $award->setGranter($user);
    $award->setRecipientFirst($this->testRecipientFirst1);
    $award->setRecipientLast($this->testRecipientLast1);
    $award->setRecipientEmail($this->testRecipientEmail1);

    date_default_timezone_set('America/Los_Angeles');
    $award->setGrantDate(new DateTime("now"));

    $this->assertEquals($award->getRecipientFirst(), $this->testRecipientFirst1, "Recipient First should match test value");
    $this->assertEquals($award->getRecipientLast(), $this->testRecipientLast1, "Recipient Last should match test value");
    $this->assertEquals($award->getRecipientEmail(), $this->testRecipientEmail1, "Recipient First should match test value");
    $this->assertEquals($award->getGranter(), $user, "Granter should match the user that granted it");

    $am->store($award);

    $loadedAward = $am->load($award->getId());
    $this->assertEquals($loadedAward, $award, "The loaded and in-memory Award objects should be identical");
  }

  public function testSetAndStore() {

    $um = new UserManager(self::$em);
    $user = new User();
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);
    $user->setEmail($this->testEmail1);
    $user->setPassword($this->testPassword1);
    $um->store($user);

    $am = new AwardManager(self::$em);
    $award = new Award();
    $award->setGranter($user);
    $award->setRecipientFirst($this->testRecipientFirst1);
    $award->setRecipientLast($this->testRecipientLast1);
    $award->setRecipientEmail($this->testRecipientEmail1);

    date_default_timezone_set('America/Los_Angeles');
    $award->setGrantDate(new DateTime("now"));

    $this->assertEquals($award->getRecipientFirst(), $this->testRecipientFirst1, "Recipient First should match test value");
    $this->assertEquals($award->getRecipientLast(), $this->testRecipientLast1, "Recipient Last should match test value");
    $this->assertEquals($award->getRecipientEmail(), $this->testRecipientEmail1, "Recipient First should match test value");
    $this->assertEquals($award->getGranter(), $user, "Granter should match the user that granted it");

    $am->store($award);

    $award->setRecipientFirst($this->testRecipientFirst2);
    $award->setRecipientLast($this->testRecipientLast2);
    $award->setRecipientEmail($this->testRecipientEmail2);
    date_default_timezone_set('America/Los_Angeles');

    $newDate = new DateTime("now");
    $award->setGrantDate($newDate);

    $user2 = new User();
    $user2->setFirstName($this->testFirstName2);
    $user2->setLastName($this->testLastName2);
    $user2->setEmail($this->testEmail2);
    $user2->setPassword($this->testPassword2);
    $um->store($user2);

    $this->assertEquals($award->getRecipientFirst(), $this->testRecipientFirst2, "Recipient First should match test value after update");
    $this->assertEquals($award->getRecipientLast(), $this->testRecipientLast2, "Recipient Last should match test value after update");
    $this->assertEquals($award->getRecipientEmail(), $this->testRecipientEmail2, "Recipient First should match test value after update");
    $this->assertEquals($award->getGranter(), $user, "Granter should match the user that granted it");
    $this->assertEquals($award->getGrantDate(), $newDate, "Grant Date should match the updated value");

    $loadedAward = $am->load($award->getId());
    $this->assertEquals($loadedAward, $award, "The loaded and in-memory Award objects should be identical");
  }

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullEmail() {
    $um = new UserManager(self::$em);
    $user = new User();
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);
    $user->setEmail($this->testEmail1);
    $user->setPassword($this->testPassword1);
    $um->store($user);

    $am = new AwardManager(self::$em);
    $award = new Award();
    $award->setGranter($user);
    $award->setRecipientFirst($this->testRecipientFirst1);
    $award->setRecipientLast($this->testRecipientLast1);

    $this->assertFalse($am->store($award), "Awards must have a Recipient Email to be saved.");
  }

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullRecipientFirstName() {
    $um = new UserManager(self::$em);
    $user = new User();
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);
    $user->setEmail($this->testEmail1);
    $user->setPassword($this->testPassword1);
    $um->store($user);

    $am = new AwardManager(self::$em);
    $award = new Award();
    $award->setGranter($user);
    $award->setRecipientLast($this->testRecipientLast1);
    $award->setRecipientEmail($this->testRecipientEmail1);

    $this->assertFalse($am->store($award), "Awards must have a Recipient First Name to be saved.");
  }

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreNullRecipientLastName() {
    $um = new UserManager(self::$em);
    $user = new User();
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);
    $user->setEmail($this->testEmail1);
    $user->setPassword($this->testPassword1);
    $um->store($user);

    $am = new AwardManager(self::$em);
    $award = new Award();
    $award->setGranter($user);
    $award->setRecipientLast($this->testRecipientFirst1);
    $award->setRecipientEmail($this->testRecipientEmail1);

    $this->assertFalse($am->store($award), "Awards must have a Recipient Last Name to be saved.");
  }

  // The doctrine association in Award.php for Granter is set to cascade persist() calls
  // So, if the award is granted a by a user that's valid but not yet persisted to the database
  // doctine will detect and handle that automatically as a prerequisite step.

  public function testStoreUnsavedGranterWithCascadingPersist() {
    $um = new UserManager(self::$em);
    $user = new User();
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);
    $user->setEmail($this->testEmail1);
    $user->setPassword($this->testPassword1);

    $am = new AwardManager(self::$em);
    $award = new Award();
    $award->setGranter($user);
    $award->setRecipientFirst($this->testRecipientFirst1);
    $award->setRecipientLast($this->testRecipientLast1);
    $award->setRecipientEmail($this->testRecipientEmail1);

    $this->assertTrue($am->store($award), "Awards must have a valid associated User in the database to be saved.");
  }

  // If the cascading persist encounters an invalid user object, it should throw and appropriate exeception
  // and storage of both objects should fail.

  /**
  * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
  */
  public function testStoreUnsavedInvalidGranterWithCascadingPersist() {
    $um = new UserManager(self::$em);
    $user = new User();
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);
    $user->setPassword($this->testPassword1);

    $am = new AwardManager(self::$em);
    $award = new Award();
    $award->setGranter($user);
    $award->setRecipientFirst($this->testRecipientFirst1);
    $award->setRecipientLast($this->testRecipientLast1);
    $award->setRecipientEmail($this->testRecipientEmail1);

    $this->assertFalse($am->store($award), "Awards must have a valid associated User in the database to be saved.");
  }

}
?>
