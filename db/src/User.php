<?php
use Doctrine\ORM\Mapping as ORM;

// TODO: Refactor to eliminate redundant code between Admin/User

/**
* @Table(name="user", uniqueConstraints={@UniqueConstraint(name="email_idx",columns={"email"})})
* @Entity
**/
class User {

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
  * @Column(name="signature_path", type="string", nullable=true)
  **/
  protected $signaturePath;

  /**
  * @var string
  *
  * @Column(name="first_name", type="string", nullable=false)
  **/
  protected $firstName;

  /**
  * @var string
  *
  * @Column(name="last_name", type="string", nullable=false)
  **/
  protected $lastName;

  // TODO: Setup the One To Many relationship for Addresses
  /**
  * @var Array of Addresses
  *
  **/
  protected $addresses;

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
  // TODO: Define the signature path at construction
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
  * @return string
  **/
  public function getFirstName() {
    return $this->firstName;
  }

  /**
  * @return string
  **/
  public function getLastName() {
    return $this->lastName;
  }

  // TODO: Return actual address records...
  /**
  * @return Array of Addresses
  **/
  public function getAddresses() {
    return null;
  }

  /**
  * @return string
  **/
  public function getSignaturePath() {
    return $this->signaturePath;
  }

  /**
  * @return string
  *
  * Provides the full URL to the signature image
  **/
  public function getSignatureURL() {

    if (empty($this->signaturePath)) {
      return null;
    }

    $basePath = "http";
    if(!empty($_SERVER['HTTPS'])) {
      $basePath .= "s";
    }
    $basePath .= "://" . $GLOBALS['STATIC_HOST'] . $GLOBALS['SIG_PATH'];

    return $basePath . $this->signaturePath;

  }


  /**
  * @param string
  * @return null
  **/
  public function setEmail($email) {
    $this->email = $email;
  }

  /**
  * @param string
  * @return null
  **/
  public function setFirstName($firstName) {
    $this->firstName = $firstName;
  }

  /**
  * @param string
  * @return null
  **/
  public function setLastName($lastName) {
    $this->lastName = $lastName;
  }

  /**
  * Calcaulates a salted password hash using PHP's built-in password hashing functions
  * PHP automatically selects a secure salt and encodes everything in the hash for future verification
  * @param password
  * @return null
  **/
  public function setPassword($password) {

    // We don't want to store a hash of null if the user supplies a null password
    // Just set it as null and let the database object
    // TODO: More elegant code would probably throw an exception

    if(!$password) {
      $this->password = null;
    } else {
      $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
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
    if ($GLOBALS['PASSWORD_RESET_TIMEOUT'] >= $delta) {
      // The reset token matches the expected value
      if ($resetHash === $this->resetHash) {
        return true;
      }
    }
    // Otherwise, fail
    return false;
  }

  /**
  * @return string
  * Generates a random path to store a signature
  */

  private function createSignaturePath() {

    // Borrowed from random_compat usage example, here:
    // https://github.com/paragonie/random_compat
    try {
      $string = random_bytes(16);
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

    // We're going to build a random filename and path for the user's signature
    // to prevent a malicious user from planting files predictably on the filesystem
    $filename = bin2hex($string) . ".jpg";
    $basePath = $GLOBALS['STATIC_ROOT'] . $GLOBALS['SIG_PATH'];

    // Since it's possible to exhaust the number of files in a single directory,
    // we'll partition the space by the first three letters of the hash, which is
    // hex, so we get 4096 directory entries.

    // Assuming a reasonably random distribution, this should let us scale pretty far
    $filePath = substr($filename, 0, 3) . "/";

    if(!is_dir($filePath)) {

      // Took the chmod-after-the-fact trick from:
      // http://stackoverflow.com/questions/12267889/create-writable-directories
      $dir = $basePath . $filePath;
      $dirMode = 0777;                // Make the directory writab

      var_dump($dir);

      if(!mkdir($dir, $dirMode, true)) {
        return null;
      }
      chmod($dir, $dirMode);
    }

    $this->signaturePath = $filePath . $filename;
    return $this->signaturePath;
  }

  /**
  * @param File uploaded file object
  * @return boolean
  * Adds a signature to the user account
  */
  public function setSignature($uploadedFile) {

    // If the current signature path isn't writable, create one that is.
    if(!($this->signaturePath) || !is_writable($this->getSignaturePath())) {
      // Create a location to store it
      if($this->createSignaturePath()) {

      } else {
        die("Error: Could not write the uploaded file.");
      }
    }

    // Move the specified file from the temporary location to the final location
   if(move_uploaded_file($uploadedFile, $GLOBALS['STATIC_ROOT'] . $GLOBALS['SIG_PATH'] . $this->getSignaturePath())) {
     chmod($GLOBALS['STATIC_ROOT'] . $GLOBALS['SIG_PATH'] . $this->getSignaturePath(), 0777);
     return true;
   }
   return false;
  }
}
?>
