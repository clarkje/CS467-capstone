<?php
// Based on https://www.safaribooksonline.com/library/view/phpunit-essentials/9781783283439/ch09s04.html
require_once(__DIR__ . "/../../config/admin/doctrine.php");
require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/../src/Admin.php");
require_once(__DIR__ . "/../AdminManager.php");

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

class AdminDoctrineTest extends TestCase
{

  /**
  * @var Doctrine\ORM\EntityManager
  */
  static protected $em = null;

  private $testEmail1 = "___testAdmin1@jeromie.com";
  private $testEmail2 = "___testAdmin2@jeromie.com";

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
    $admin = new Admin();
    $this->assertEmpty($admin->getId());
  }

  public function testCreateAdmin() {

    // Setup a new Admin object
    $admin = new Admin();
    $am = new AdminManager(self::$em);

    // Provide some basic inputs
    $admin->setEmail($this->testEmail1);
    $admin->setPassword("password");

    $this->assertEmpty($admin->getId(), "A new Admin object should not have an ID before it is stored");
    $this->assertNotEmpty($admin->getCreated());

    $this->assertTrue($admin->verifyPassword("password"), "Password should validate before storage");

    // Make a shallow copy of the pre-storage object
    $admin2 = clone $admin;

    $this->assertTrue($am->store($admin));
    $this->assertTrue($admin->verifyPassword("password"), "Password should validate after storage");
    $this->assertNotEmpty($admin->getId(), "The ORM should assign an ID to the object once stored");

    // Everything else should equate
    $this->assertEquals($admin->getCreated(), $admin2->getCreated());
    $this->assertEquals($admin->getEmail(), $admin2->getEmail());

    // Save this Admin object for the next test
    return $admin;
  }

  /**
  * @depends testCreateAdmin
  */

  public function testLoadAdmin(Admin $oldAdmin) {

    $am = new AdminManager(self::$em);

    // Load a copy of the admin object we have in the class scope from the database
    $loadedAdmin = $am->load($oldAdmin->getId());

    $this->assertEquals($loadedAdmin, $oldAdmin, "The loaded and in-memory Admin objects should be identical");

    return $loadedAdmin;
  }

  /**
  * @depends testLoadAdmin
  */

  public function testSetPassword(Admin $admin) {

    $am = new AdminManager(self::$em);

    $admin->setPassword("New Password");
    $this->assertTrue($admin->verifyPassword("New Password"), "The new password should validate correctly.");
    $this->assertFalse($admin->verifyPassword("Wrong Password"), "An incorrect password should return false.");

    // Store the object to the database
    $am->store($admin);
    // Load the object by it's ID from the database
    $loadedAdmin = $am->load($admin->getId());

    // Verify that the loaded object's password validates correctly
    $this->assertTrue($loadedAdmin->verifyPassword("New Password"), "The new password should validate correctly.");

    return $admin;
  }

  /**
  * @depends testSetPassword
  */

  public function testSetPasswordUTF8(Admin $admin) {

    $am = new AdminManager(self::$em);

    // Found a nifty UTF test string generator here: https://www.tienhuis.nl/utf8-generator
    $admin->setPassword("Ħệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđ");

    $this->assertTrue($admin->verifyPassword("Ħệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđ"), "The new UTF-8 password should validate correctly.");
    $this->assertFalse($admin->verifyPassword("Wrong Password"), "An incorrect password should return false.");

    // Store the object to the database
    $am->store($admin);
    // Load the object by it's ID from the database
    $loadedAdmin = $am->load($admin->getId());

    // Verify that the loaded object's password validates correctly
    $this->assertTrue($loadedAdmin->verifyPassword("Ħệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđĦệřё\'ŝ α čřǻźỹ ÚŦ₣-8 ρẵŝśẅǒяđ"), "The new UTF-8 password should validate correctly.");

    return $admin;

  }

  /**
  * @depends testSetPassword
  */

  public function testSetEmail(Admin $admin) {

    $am = new AdminManager(self::$em);
    $admin->setEmail($this->testEmail2);

    // Verify that the email got updated
    $this->assertEquals($admin->getEmail(), $this->testEmail2);

    $am->store($admin);
    $loadedAdmin = $am->load($admin->getId());

    $this->assertEquals($loadedAdmin->getEmail(), $this->testEmail2);

    return $admin;
  }

  /**
  * @depends testSetEmail
  */

  public function testFindByEmail(Admin $admin) {

    $am = new AdminManager(self::$em);
    $loadedAdmin = $am->loadByEmail($this->testEmail2);

    // The first result should match the object that was returned in the previous test
    $this->assertEquals($loadedAdmin[0]->getEmail(), $admin->getEmail(), "Email for Admin and LoadedAdmin[0] should match");

    return $admin;
  }

  /**
  * @depends testFindByEmail
  */

  public function testFindByEmailAndDelete(Admin $admin) {

    $am = new AdminManager(self::$em);
    $loadedAdmin = $am->loadByEmail($this->testEmail2, "We should begin this test with entities to delete.");

    $this->assertNotEmpty($loadedAdmin);

    foreach ($loadedAdmin as &$admin) {
        $am->delete($admin);
    }

    $loadedAdmin = $am->loadByEmail($this->testEmail2);
    $this->assertEmpty($loadedAdmin, "After deleting all entities associated with the email address, there shouldn't be any left.");

    // Just cleaning up our mess in the database...

    $am = new AdminManager(self::$em);
    $loadedAdmin = $am->loadByEmail($this->testEmail1, "We should begin this test with entities to delete.");

    $this->assertNotEmpty($loadedAdmin);

    foreach ($loadedAdmin as &$admin) {
        $am->delete($admin);
    }

    $loadedAdmin = $am->loadByEmail($this->testEmail1);
    $this->assertEmpty($loadedAdmin, "After deleting all entities associated with the email address, there shouldn't be any left.");
    }

    /**
    * @expectedException Doctrine\DBAL\Exception\NotNullConstraintViolationException
    */
    public function testStoreNullEmail() {

      $am = new AdminManager(self::$em);
      $admin = new Admin();
      $admin->setEmail(null);
      $this->assertFalse($am->store($admin));
    }

    /**
    * @expectedException Doctrine\ORM\ORMException
    */
    public function testStoreNullPassword() {

      $am = new AdminManager(self::$em);
      $admin = new Admin();
      $admin->setEmail($this->testEmail1);
      $admin->setPassword(null);
      $this->assertFalse($am->store($admin));
    }


}
?>
