<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Filter
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Form\Filter;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * @return string name of the filter
     */
    public function __toString()
    {
        $parts = explode('\\', get_class($this));
        return $parts[count($parts) - 1];
    }
}
