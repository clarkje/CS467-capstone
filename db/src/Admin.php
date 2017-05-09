<?php
use Doctrine\ORM\Mapping as ORM;

/**
* @Table(name="admin")
* @Entity
**/
class Admin {

  /**
  * @var integer
  *
  * @Id
  * @GeneratedValue(strategy="IDENTITY")
  * @Column(name="id", type="integer", nullable=false)
  **/
  private $id;


  /**
  * @var string
  *
  * @Column(name="email", type="string", nullable=false)
  **/
  protected $email;


  /**
  * @var string
  *
  * @Column(name="password", type="string", nullable=false)
  **/
  protected $password;


  /**
  * @var datetime
  *
  * @Column(name="created", type="datetime", nullable=false)
  **/
  protected $created;


  /**
  * @var string
  *
  * @Column(name="resetHash", type="string", nullable=true)
  */
  protected $resetHash;

  /**
  * @var datetime
  * @Column(name="resetTimestamp", type="datetime", nullable=true)
  */
  protected $resetTimestamp;

  /**
  * Sets the creation timestamp automatically, in the format MySQL DATEIME wants
  **/
  public function __construct() {
    // Set the default timezone for our application
    date_default_timezone_set('America/Los_Angeles');
    $this->created = new DateTime("now");
  }

  /**
  * @return int
  **/

  public function getId() {
    return $this->id;
  }

  /**
  * @return string
  **/
  public function getEmail() {
    return $this->email;
  }

  /**
  * @return date
  **/
  public function getCreated() {
    return $this->created;
  }

  /**
  * @param string
  * @return null
  **/

  public function setEmail($email) {
    $this->email = $email;
  }

  /**
  * Calcaulates a salted password hash using PHP's built-in password hashing functions
  * PHP automatically selects a secure salt and encodes everything in the hash for future verification
  * @param password
  * @return null
  **/
  public function setPassword($password) {
    $this->password = password_hash($password, PASSWORD_DEFAULT);
  }

  /**
  * Validates that the provided password matches the persisted hash
  * @param password
  * @return bool
  **/
  public function verifyPassword($password) {
    return password_verify($password, $this->password);
  }

  /**
  * Sets a reset hash on the user account.
  */
  public function createResetHash() {

    // Borrowed from random_compat usage example, here:
    // https://github.com/paragonie/random_compat
    try {
      $string = random_bytes(32);
    } catch (TypeError $e) {
        // Well, it's an integer, so this IS unexpected.
        die("An unexpected error has occurred");
    } catch (Error $e) {
        // This is also unexpected because 32 is a reasonable integer.
        die("An unexpected error has occurred");
    } catch (Exception $e) {
        // If you get this message, the CSPRNG failed hard.
        die("Could not generate a random string. Is our OS secure?");
    }
    $this->resetHash = bin2hex($string);
    $this->resetTimestamp = new DateTime("now");
  }

  /**
  * @param string
  * Returns the password reset hash
  */

  public function getResetHash() {
    return $this->resetHash;
  }

  // TODO: Consider implementing an attempt counter and timeout
  // If user gets <n> failed password hash validation attempts in a timeout period,
  // lock the accout out for an extended period of time
  public function validateResetHash($resetHash) {
    
    date_default_timezone_set('America/Los_Angeles');
    $curTime = new DateTime("now");
    $delta = $curTime->getTimestamp() - $this->resetTimestamp->getTimestamp();

    // The reset token has not expired
    // http://stackoverflow.com/questions/1519228/get-interval-seconds-between-two-datetime-in-php
    if ($GLOBALS['password_reset_timeout'] >= $delta) {
      // The reset token matches the expected value
      if ($resetHash === $this->resetHash) {
        return true;
      }
    }
    // Otherwise, fail
    return false;
  }
}
?>
