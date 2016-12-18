<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Entity
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Entity;

class FormConfig
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $dbTable;

    /**
     * @var FieldConfigCollection
     */
    protected $fieldConfigCollection;

    public function __construct($name, $dbTable)
    {
        $this->name    = $name;
        $this->dbTable = $dbTable;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getDbTable()
    {
        return $this->dbTable;
    }

    public function setDbTable($dbTable)
    {
        $this->dbTable = $dbTable;
        return $this;
    }

    public function getFieldsConfigCollection()
    {
        return $this->fieldConfigCollection;
    }

    public function setFieldConfigCollection(FieldConfigCollection $fieldConfigCollection)
    {
        $this->fieldConfigCollection = $fieldConfigCollection;
        return $this;
    }
}