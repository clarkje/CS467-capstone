<?php
use Doctrine\ORM\Mapping as ORM;

/**
* @Table(name="award");
* @Entity
**/

class Award {


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
  * Many Awards have one User -- If the associated user is valid but unsaved, add it to the database.
  * @ManyToOne(targetEntity="User", cascade={"persist"})
  * @JoinColumn(name="granter_id", referencedColumnName="id", nullable=false)
  */
  private $granter;

  /**
  * @var string
  * @Column(name="recipient_first", type="string", nullable=false)
  */
  private $recipientFirst;

  /**
  * @var string
  * @Column(name="recipient_last", type="string", nullable=false)
  */
  private $recipientLast;

  /**
  * @var string
  * @Column name="recipient_email", type="string", nullable=false)
  */
  private $recipientEmail;

  /**
  * @var datetime
  * @Column(name="grant_date", type="datetime", nullable=true)
  **/
  private $grantDate;


  // TODO: Setup relationship with AwardType column
  /**
  * @var integer
  * @Column(name="awardType", type="integer", nullable=false)
  */
  private $awardType = 1;

  /**
  * @var string
  *
  * @Column(name="cert_path", type="string", nullable=true)
  **/
  protected $certPath;


  /**
  * @return int
  **/
  public function getId() {
    return $this->id;
  }

  /**
  * @return User user object of granter
  **/
  public function getGranter() {
    return $this->granter;
  }

  /**
  * @return string
  **/
  public function getRecipientFirst() {
    return $this->recipientFirst;
  }

  /**
  * @return string
  **/
  public function getRecipientLast() {
    return $this->recipientLast;
  }

  /**
  * @return string
  **/
  public function getRecipientEmail() {
    return $this->recipientEmail;
  }

  /**
  * @return datetime
  **/
  public function getGrantDate() {
    return $this->grantDate;
  }

  /**
  * @return string
  * Returns the path of the certificate from the document root
  **/
  public function getCertPath() {
    return $this->certPath;
  }

  /**
  * @return string
  * Returns the full URL to the certificate
  */

  public function getCertURL() {
    if (empty($this->certPath)) {
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
  * @return int
  **/
  public function getAwardType() {
    return $this->awardType;
  }


  /**
  * @param int
  * @return null
  **/
  public function setGranter($granter) {
    $this->granter = $granter;
  }

  /**
  * @param string
  * @return null
  **/
  public function setRecipientFirst($first) {
    $this->recipientFirst = $first;
  }

  /**
  * @param string
  * @return null
  **/
  public function setRecipientLast($last) {
    $this->recipientLast = $last;
  }

  /**
  * @param string
  * @return null
  **/
  public function setRecipientEmail($email) {
    $this->recipientEmail = $email;
  }

  /**
  * @param datetime
  * @return null
  **/
  public function setGrantDate($grantDate) {
    $this->grantDate = $grantDate;
  }

  /**
  * @param int
  * @return null
  **/
  public function setAwardType($awardType) {
    $this->awardType = $awardType;
  }

  /**
  * @return string
  * Generates a random path to store a signature
  */

  private function createCertPath() {

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
    $filename = bin2hex($string) . ".pdf";
    $basePath = $GLOBALS['STATIC_ROOT'] . $GLOBALS['CERT_PATH'];

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

      if(!mkdir($dir, $dirMode, true)) {
        return null;
      }
      chmod($dir, $dirMode);
    }

    $this->certPath = $filePath . $filename;
    return $this->certPath;
  }

  /**
  * @param File uploaded file object
  * @return boolean
  * Adds a signature to the user account
  */
  public function setCert($certFile) {

    // If the current signature path isn't writable, create one that is.
    if(!($this->signaturePath) || !is_writable($this->getSignaturePath())) {
      // Create a location to store it
      if($this->createSignaturePath()) {

      } else {
        die("Error: Could not write the uploaded file.");
      }
    }
    // Move the generated file into the correct location
   return false;
  }
}
?>
