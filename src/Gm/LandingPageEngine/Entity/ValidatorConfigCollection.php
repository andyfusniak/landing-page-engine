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

class ValidatorConfigCollection extends AbstractConfigCollection
{
    /**
     * @param ValidatorConfig $validatorConfig object
     * @return ValidatorConfigCollection
     */
    public function addValidatorConfig(ValidatorConfig $validatorConfig)
    {
        $this->collection[] = $validatorConfig;
        return $this;
    }

    /**
     * @return array associative array of validator config name to ValidatorConfig objects
     */
    public function getAllValidatorConfigs()
    {
        return $this->collection;
    }
}
