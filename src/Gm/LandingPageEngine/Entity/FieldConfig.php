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

class FieldConfig
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $dbColumn;

    /**
     * @var bool
     */
    protected $optional;

    /**
     * @var FilterConfigCollection
     */
    protected $filterConfigCollection;

    /**
     * @var ValidatorConfigCollection
     */
    protected $validatorConfigCollection;

    public function __construct($name, $dbColumn, $optional = false)
    {
        $this->name = $name;
        $this->dbColumn = $dbColumn;
        $this->optional = (bool) $optional;
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

    public function getDbColumn()
    {
        return $this->dbColumn;
    }

    public function setDbColumn($dbColumn)
    {
        $this->dbColumn = $dbColumn;
        return $this;
    }

    public function setOptional(bool $optional)
    {
        $this->optional = $optional;
        return $this;
    }

    /**
     * @return bool
     */
    public function getOptional()
    {
        return $this->optional;
    }

    public function setFilterConfigCollection(FilterConfigCollection $filterConfigCollection)
    {
        $this->filterConfigCollection = $filterConfigCollection;
        return $this;
    }

    /**
     * @return array associative array of name to FilterConfig objects
     */
    public function getFilterConfigCollection()
    {
        return $this->filterConfigCollection;
    }

    public function hasFilters()
    {
        return (null !== $this->filterConfigCollection);
    }

    public function setValidatorConfigCollection(ValidatorConfigCollection $validatorConfigCollection)
    {
        $this->validatorConfigCollection = $validatorConfigCollection;
        return $this;
    }

    /**
     * @return array associative array of name to ValidatorConfig objects
     */
    public function getValidatorConfigCollection()
    {
        return $this->validatorConfigCollection;
    }

    public function hasValidators()
    {
        return (null !== $this->validatorConfigCollection);
    }
}
