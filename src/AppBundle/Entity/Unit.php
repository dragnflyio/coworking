<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
/**
 * @ORM\Entity
 * @ORM\Table(name="unit")
 */
class Unit
{
  /**
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @Assert\NotBlank()
   */
  public $id;
  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\NotBlank()
   */
  public $nameen;
  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\NotBlank()
   */
  public $namevi;
  /**
   * @ORM\Column(type = "integer")
   * @Assert\NotBlank()
   */
  public $value;
  /**
   * @ORM\Column(type = "integer", nullable=true)
   */
  public $baseUnit = null;
  /**
   * Get id
   *
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * Get nameen
   *
   * @return string
   */
  public function getNameen()
  {
    return $this->nameen;
  }
  /**
   * Get namevi
   *
   * @return string
   */
  public function getNamevi()
  {
    return $this->namevi;
  }
  /**
   * Get id
   *
   * @return int
   */
  public function getValue()
  {
    return $this->value;
  }
  /**
   * Get base unit
   *
   * @return $base_unit
   */
  public function getBaseUnit()
  {
    return $this->baseUnit;
  }
  /**
   * Set ID
   *
   * @param int $id
   *
   * @return Unit
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * Set nameen
   *
   * @param string $nameen
   *
   * @return Unit
   */
  public function setNameen($nameen)
  {
    $this->nameen = $nameen;
  }
  /**
   * Set namevi
   *
   * @param string $namevi
   *
   * @return Unit
   */
  public function setNamevi($namevi)
  {
    $this->namevi = $namevi;
  }
  /**
   * Set value
   *
   * @param int $value
   *
   * @return Unit
   */
  public function setValue($value)
  {
    $this->value = $value;
  }
  /**
   * Set base unit
   *
   * @param int $base_unit
   *
   * @return Unit
   */
  public function setBaseUnit($baseUnit)
  {
    $this->baseUnit = $baseUnit;
  }
}
