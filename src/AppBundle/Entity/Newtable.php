<?php

namespace AppBundle\Entity;

/**
 * Newtable
 */
class Newtable
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var integer
     */
    private $number;

    /**
     * @var \DateTime
     */
    private $sysdate = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     */
    private $iddate;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set text
     *
     * @param string $text
     *
     * @return Newtable
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Newtable
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set sysdate
     *
     * @param \DateTime $sysdate
     *
     * @return Newtable
     */
    public function setSysdate($sysdate)
    {
        $this->sysdate = $sysdate;

        return $this;
    }

    /**
     * Get sysdate
     *
     * @return \DateTime
     */
    public function getSysdate()
    {
        return $this->sysdate;
    }

    /**
     * Set iddate
     *
     * @param integer $iddate
     *
     * @return Newtable
     */
    public function setIddate($iddate)
    {
        $this->iddate = $iddate;

        return $this;
    }

    /**
     * Get iddate
     *
     * @return integer
     */
    public function getIddate()
    {
        return $this->iddate;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}

