<?php
// Based on https://www.safaribooksonline.com/library/view/phpunit-essentials/9781783283439/ch09s04.html
require_once(__DIR__ . "/../../config/admin/doctrine.php");
require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/../src/User.php");
require_once(__DIR__ . "/../UserManager.php");

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

  // Found a nifty UTF test string generator here: https://www.tienhuis.nl/utf8-generator
  private $testUTF8Password1 = "Ħệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđ";
  private $testUTF8Password2 = "Ħệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđ123";

  private $testSignaturePath1 = "/dev/null";
  private $testSignaturePath2 = "/var/signatures/asdf";

  public static function setUpBeforeClass() {
    $emFactory = new EntityManagerFactory();
    self::$em = $emFactory->getEntityManager();
  }

  protected function tearDown() {
    self::$em->clear();
    parent::tearDown();
  }

  protected function createEntityManager() {
    return self::$em;
  }

  public function testEmptyConstructor() {
    $user = new User();
    $this->assertEmpty($user->getId(), "ID should be empty at construction");
    $this->assertEmpty($user->getFirstName(), "First Name should be empty at construction");
    $this->assertEmpty($user->getLastName(), "Last Name should be empty at construction");
    $this->assertEmpty($user->getEmail(), "Email should be empty at construction");
    $this->assertNotEmpty($user->getSignaturePath(), "Signature should not be empty at construction");
    $this->assertNotEmpty($user->getCreated(), "Created should not be empty at construction");
  }

  public function testCreatedPopulatedOnConstruction() {
    $user = new User();
    $this->assertNotEmpty($user->getCreated());
  }

  // TODO: Test population/association of addresses...

  public function testCreateUser() {

    // Setup a new User object
    $user = new User();
    $um = new UserManager(self::$em);

    // Provide some basic inputs
    $user->setEmail($this->testEmail1);
    $user->setPassword($this->testPassword1);
    $user->setFirstName($this->testFirstName1);
    $user->setLastName($this->testLastName1);

    $this->assertEquals($user->getEmail(), $this->testEmail1, "User email should match test value");
    $this->assertEquals($user->getFirstName(), $this->testFirstName1, "User first name should match test value");
    $this->assertEquals($user->getLastName(), $this->testLastName1, "User last name should match test value");

    return $user;
  }

  /**
  * @depends testCreateUser
  */

  public function testLoadUser(User $oldUser) {

    $um = new UserManager(self::$em);
    $um->store($oldUser);

    // Load a copy of the user object we have in the class scope from the database
    $loadedUser = $um->load($oldUser->getId());

    $this->assertEquals($loadedUser, $oldUser, "The loaded and in-memory User objects should be identical");

    return $loadedUser;
  }

  /**
  * @depends testLoadUser
  */

  public function testSetPassword(User $user) {

    $um = new UserManager(self::$em);

    $user->setPassword($this->testPassword1);
    $this->assertTrue($user->verifyPassword($this->testPassword1), "The new password should validate correctly");
    $this->assertFalse($user->verifyPassword($this->testPassword2), "An incorrect password should return false.");

    // Change the password
    $user->setPassword($this->testPassword2);
    $this->assertTrue($user->verifyPassword($this->testPassword2), "The updated password should validate correctly");

    // Store it in the database
    $um->store($user);

    // Load it from the database
    $loadedUser = $um->load($user->getId());
    $this->assertTrue($loadedUser->verifyPassword($this->testPassword2), "The loaded password should validate correclty");

    return $user;
  }

  /**
  * @depends testSetPassword
  */

  public function testSetPasswordUTF8(User $user) {
    $um = new UserManager(self::$em);

    $user->setPassword($this->testUTF8Password1);
    $this->assertTrue($user->verifyPassword($this->testUTF8Password1), "The new password should validate correctly");
    $this->assertFalse($user->verifyPassword($this->testUTF8Password2), "An incorrect password should return false.");

    // Change the password
    $user->setPassword($this->testUTF8Password2);
    $this->assertTrue($user->verifyPassword($this->testUTF8Password2), "The updated password should validate correctly");

    // Store it in the database
    $um->store($user);

    // Load it from the database
    $loadedUser = $um->load($user->getId());
    $this->assertTrue($loadedUser->verifyPassword($this->testUTF8Password2), "The loaded password should validate correclty");

    return $user;
  }

  /**
  * @depends testSetPassword
  */

  public function testSetEmail(User $user) {

    $um = new UserManager(self::$em);
    $user->setEmail($this->testEmail2);

    // Verify that the email got updated
    $this->assertEquals($user->getEmail(), $this->testEmail2);

    // Store and retrieve
    $um->store($user);
    $loadedUser = $um->load($user->getId());

    $this->assertEquals($loadedUser->getEmail(), $this->testEmail2);

    return $user;
  }

  /**
  * @depends testSetEmail
  */
  public function testSetFirstName(User $user) {

    $um = new UserManager(self::$em);
    $user->setFirstName($this->testFirstName2);

    // Verify that the email got updated
    $this->assertEquals($user->getFirstName(), $this->testFirstName2);

    // Store and retrieve
    $um->store($user);
    $loadedUser = $um->load($user->getId());

    $this->assertEquals($loadedUser->getFirstName(), $this->testFirstName2);

    return $user;
  }

  /**
  * @depends testSetFirstName
  */
  public function testSetLastName(User $user) {

    $um = new UserManager(self::$em);
    $user->setLastName($this->testLastName2);

    // Verify that the email got updated
    $this->assertEquals($user->getLastName(), $this->testLastName2);

    // Store and retrieve
    $um->store($user);
    $loadedUser = $um->load($user->getId());

    $this->assertEquals($loadedUser->getLastName(), $this->testLastName2);
    return $user;
  }

  /**
  * @depends testSetLastName
  */

  public function testFindByEmail(User $user) {

    $um = new UserManager(self::$em);
    $loadedUser = $um->loadByEmail($this->testEmail2);

    // The first result should match the object that was returned in the previous test
    $this->assertEquals($loadedUser[0]->getEmail(), $user->getEmail(), "Email for Admin and LoadedUser[0] should match");

    return $user;
  }

  /**
  * @depends testFindByEmail
  */

  public function testFindByEmailAndDelete(User $user) {

    $um = new UserManager(self::$em);
    $loadedUser = $um->loadByEmail($this->testEmail2, "We should begin this test with entities to delete.");

    $this->assertNotEmpty($loadedUser);

    foreach ($loadedUser as &$user) {
        $um->delete($user);
    }

    $loadedUser = $um->loadByEmail($this->testEmail2);
    $this->assertEmpty($loadedUser, "After deleting all entities associated with the email address, there shouldn't be any left.");

    // Just cleaning up our mess in the database...
    $loadedUser = $um->loadByEmail($this->testEmail1, "We should begin this test with entities to delete.");

    $this->assertNotEmpty($loadedUser);

    foreach ($loadedUser as &$user) {
        $um->delete($user);
    }

    $loadedUser = $um->loadByEmail($this->testEmail1);
    $this->assertEmpty($loadedUser, "After deleting all entities associated with the email address, there shouldn't be any left.");
    }

    /**
    * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
    */
    public function testStoreNullEmail() {

      $um = new UserManager(self::$em);
      $user = new User();
      $user->setEmail(null);
      $user->setPassword($this->testPassword1);
      $user->setFirstName($this->testFirstName1);
      $user->setLastName($this->testLastName1);

      $this->assertFalse($um->store($user));
    }

    /**
    * @expectedException Doctrine\ORM\ORMException
    */
    public function testStoreNullPassword() {

      $um = new UserManager(self::$em);
      $user = new User();
      $user->setEmail($this->testEmail1);
      $user->setPassword(null);
      $user->setFirstName($this->testFirstName1);
      $user->setLastName($this->testLastName1);
      $this->assertFalse($um->store($user));
    }

    /**
    * @expectedException Doctrine\ORM\ORMException
    */
    public function testStoreNullFirstName() {

      $um = new UserManager(self::$em);
      $user = new User();
      $user->setEmail($this->testEmail1);
      $user->setPassword($this->testPassword1);
      $user->setFirstName(null);
      $user->setLastName($this->testLastName1);
      $this->assertFalse($um->store($user));
    }

    /**
    * @expectedException Doctrine\ORM\ORMException
    */
    public function testStoreNullLastName() {

      $um = new UserManager(self::$em);
      $user = new User();
      $user->setEmail($this->testEmail1);
      $user->setPassword($this->testPassword1);
      $user->setFirstName($this->testFirstName1);
      $user->setLastName(null);
      $this->assertFalse($um->store($user));
    }

}
?>
