<?php

namespace AppBundle\Entity;

/**
 * Ddfields
 */
class Ddfields
{
    /**
     * @var string
     */
    private $objectName;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $colName;

    /**
     * @var string
     */
    private $colCode;

    /**
     * @var string
     */
    private $colLabel;

    /**
     * @var string
     */
    private $dataType;

    /**
     * @var string
     */
    private $dataSource;

    /**
     * @var integer
     */
    private $valueDefault;

    /**
     * @var integer
     */
    private $colPosition;

    /**
     * @var boolean
     */
    private $valueReadonly;

    /**
     * @var string
     */
    private $triggerUrl;

    /**
     * @var string
     */
    private $triggerTarget;

    /**
     * @var integer
     */
    private $valueMaxlength;

    /**
     * @var boolean
     */
    private $colActive = '1';

    /**
     * @var string
     */
    private $searchOpt;

    /**
     * @var string
     */
    private $zero;

    /**
     * @var \DateTime
     */
    private $sysdate = 'CURRENT_TIMESTAMP';

    /**
     * @var boolean
     */
    private $hidden;

    /**
     * @var string
     */
    private $attributes;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set objectName
     *
     * @param string $objectName
     *
     * @return Ddfields
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;

        return $this;
    }

    /**
     * Get objectName
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Set tableName
     *
     * @param string $tableName
     *
     * @return Ddfields
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get tableName
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set colName
     *
     * @param string $colName
     *
     * @return Ddfields
     */
    public function setColName($colName)
    {
        $this->colName = $colName;

        return $this;
    }

    /**
     * Get colName
     *
     * @return string
     */
    public function getColName()
    {
        return $this->colName;
    }

    /**
     * Set colCode
     *
     * @param string $colCode
     *
     * @return Ddfields
     */
    public function setColCode($colCode)
    {
        $this->colCode = $colCode;

        return $this;
    }

    /**
     * Get colCode
     *
     * @return string
     */
    public function getColCode()
    {
        return $this->colCode;
    }

    /**
     * Set colLabel
     *
     * @param string $colLabel
     *
     * @return Ddfields
     */
    public function setColLabel($colLabel)
    {
        $this->colLabel = $colLabel;

        return $this;
    }

    /**
     * Get colLabel
     *
     * @return string
     */
    public function getColLabel()
    {
        return $this->colLabel;
    }

    /**
     * Set dataType
     *
     * @param string $dataType
     *
     * @return Ddfields
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set dataSource
     *
     * @param string $dataSource
     *
     * @return Ddfields
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    /**
     * Get dataSource
     *
     * @return string
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Set valueDefault
     *
     * @param integer $valueDefault
     *
     * @return Ddfields
     */
    public function setValueDefault($valueDefault)
    {
        $this->valueDefault = $valueDefault;

        return $this;
    }

    /**
     * Get valueDefault
     *
     * @return integer
     */
    public function getValueDefault()
    {
        return $this->valueDefault;
    }

    /**
     * Set colPosition
     *
     * @param integer $colPosition
     *
     * @return Ddfields
     */
    public function setColPosition($colPosition)
    {
        $this->colPosition = $colPosition;

        return $this;
    }

    /**
     * Get colPosition
     *
     * @return integer
     */
    public function getColPosition()
    {
        return $this->colPosition;
    }

    /**
     * Set valueReadonly
     *
     * @param boolean $valueReadonly
     *
     * @return Ddfields
     */
    public function setValueReadonly($valueReadonly)
    {
        $this->valueReadonly = $valueReadonly;

        return $this;
    }

    /**
     * Get valueReadonly
     *
     * @return boolean
     */
    public function getValueReadonly()
    {
        return $this->valueReadonly;
    }

    /**
     * Set triggerUrl
     *
     * @param string $triggerUrl
     *
     * @return Ddfields
     */
    public function setTriggerUrl($triggerUrl)
    {
        $this->triggerUrl = $triggerUrl;

        return $this;
    }

    /**
     * Get triggerUrl
     *
     * @return string
     */
    public function getTriggerUrl()
    {
        return $this->triggerUrl;
    }

    /**
     * Set triggerTarget
     *
     * @param string $triggerTarget
     *
     * @return Ddfields
     */
    public function setTriggerTarget($triggerTarget)
    {
        $this->triggerTarget = $triggerTarget;

        return $this;
    }

    /**
     * Get triggerTarget
     *
     * @return string
     */
    public function getTriggerTarget()
    {
        return $this->triggerTarget;
    }

    /**
     * Set valueMaxlength
     *
     * @param integer $valueMaxlength
     *
     * @return Ddfields
     */
    public function setValueMaxlength($valueMaxlength)
    {
        $this->valueMaxlength = $valueMaxlength;

        return $this;
    }

    /**
     * Get valueMaxlength
     *
     * @return integer
     */
    public function getValueMaxlength()
    {
        return $this->valueMaxlength;
    }

    /**
     * Set colActive
     *
     * @param boolean $colActive
     *
     * @return Ddfields
     */
    public function setColActive($colActive)
    {
        $this->colActive = $colActive;

        return $this;
    }

    /**
     * Get colActive
     *
     * @return boolean
     */
    public function getColActive()
    {
        return $this->colActive;
    }

    /**
     * Set searchOpt
     *
     * @param string $searchOpt
     *
     * @return Ddfields
     */
    public function setSearchOpt($searchOpt)
    {
        $this->searchOpt = $searchOpt;

        return $this;
    }

    /**
     * Get searchOpt
     *
     * @return string
     */
    public function getSearchOpt()
    {
        return $this->searchOpt;
    }

    /**
     * Set zero
     *
     * @param string $zero
     *
     * @return Ddfields
     */
    public function setZero($zero)
    {
        $this->zero = $zero;

        return $this;
    }

    /**
     * Get zero
     *
     * @return string
     */
    public function getZero()
    {
        return $this->zero;
    }

    /**
     * Set sysdate
     *
     * @param \DateTime $sysdate
     *
     * @return Ddfields
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
     * Set hidden
     *
     * @param boolean $hidden
     *
     * @return Ddfields
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set attributes
     *
     * @param string $attributes
     *
     * @return Ddfields
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get attributes
     *
     * @return string
     */
    public function getAttributes()
    {
        return $this->attributes;
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

