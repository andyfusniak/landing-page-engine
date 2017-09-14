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

class FilterConfigCollection extends AbstractConfigCollection
{
    /**
     * @param FilterConfig $filterConfig object
     * @return FilterConfigCollection
     */
    public function addFilterConfig(FilterConfig $filterConfig)
    {
        $this->collection[] = $filterConfig;
        return $this;
    }

    /**
     * @return array associative array of filter config name to FilterConfig objects
     */
    public function getAllFilterConfigs()
    {
        return $this->collection;
    }
}
