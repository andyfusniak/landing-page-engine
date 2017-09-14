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

abstract class AbstractValidatorFilterConfig
{
    /**
     * @var $name
     */
    protected $name;

    public function __construct($name, $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName()
    {
        $this->name = $name;
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
