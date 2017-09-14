<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Entity
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 */
namespace Gm\LandingPageEngine\Entity;

class FormConfig
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var FieldConfigCollection
     */
    protected $fieldConfigCollection;

    public function __construct($name)
    {
        $this->name = $name;
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
