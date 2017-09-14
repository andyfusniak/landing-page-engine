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

class ValidatorConfig extends AbstractValidatorFilterConfig
{
    /**
     * @var array
     */
    protected $options = [];

    public function getOptions()
    {
        return $this->options;
    }
}
