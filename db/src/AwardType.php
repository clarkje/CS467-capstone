<?php
use Doctrine\ORM\Mapping as ORM;

/**
* @Table(name="awardType", uniqueConstraints={@UniqueConstraint(name="description_idx",columns={"description"})})
* @Entity
**/
class AwardType {

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
  * @Column(name="description", type="string", nullable=false)
  **/
  protected $description;


  /**
  * @var string
  *
  * @Column(name="template_file", type="string", nullable=false)
  **/
  protected $templateFile;


  /**
  * @return int
  **/

  public function getId() {
    return $this->id;
  }

  /**
  * @return string
  **/
  public function getDescription() {
    return $this->description;
  }

  /**
  * @return string
  **/
  public function getTemplateFile() {
    return $this->templateFile;
  }

  /**
  * @param string
  * @return null
  */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
  * @param string
  * @return null
  */
  public function setTemplateFile($templateFile) {
    $this->templateFile = $templateFile;
  }
}
?>
