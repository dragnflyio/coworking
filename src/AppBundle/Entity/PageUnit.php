<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class PageUnit
{
  /**
   * @var ArrayCollection
   */
  protected $units;

  public function __construct()
  {
    $this->units = new ArrayCollection();
  }

  /**
   * Get units
   *
   * @return ArrayCollection
   */
  public function getUnits()
  {
    return $this->units;
  }

  /**
   * Set units
   *
   * @param ArrayCollection $units
   *
   * @return PageUnit
   */
  public function setUnits($units)
  {
    $this->units = $units;
  }

  /**
   * Remove unit
   *
   * @param Unit $unit
   *
   * @return PageUnit
   */
  public function removeUnit(Unit $unit)
  {
    $this->$units->removeElement($unit);
  }

  /**
   * Add unit into PageUnit.
   *
   * @param Unit $unit
   *
   * @return PageUnit
   */
  public function addUnit(Unit $unit)
  {
    $this->units->add($unit);
  }
}

