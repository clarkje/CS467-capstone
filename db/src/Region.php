<?php
use Doctrine\ORM\Mapping as ORM;

// TODO: Refactor to eliminate redundant code between Admin/User

/**
* @Table(name="region", uniqueConstraints={@UniqueConstraint(name="name_idx",columns={"name"})})
* @Entity
**/
class Region {

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
  * @Column(name="name", type="string", nullable=false)
  **/
  protected $name;

  /**
  * @return int
  **/

  public function getId() {
    return $this->id;
  }

  /**
  * @return string
  **/
  public function getName() {
    return $this->name;
  }

  /**
  * @param string
  * @return null
  **/
  public function setName($name) {
    $this->name = $name;
  }
}
?>
