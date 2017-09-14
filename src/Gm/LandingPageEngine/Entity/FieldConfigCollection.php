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

class FieldConfigCollection extends AbstractConfigCollection
{
    /**
     * @param FieldConfig $fieldConfig object
     * @return ValidatorConfigCollection
     */
    public function addFieldConfig(FieldConfig $fieldConfig)
    {
        $this->collection[] = $fieldConfig;
        return $this;
    }

    /**
     * @return array associative array of validator config name to ValidatorConfig objects
     */
    public function getAllFieldConfigs()
    {
        return $this->collection;
    }
}
