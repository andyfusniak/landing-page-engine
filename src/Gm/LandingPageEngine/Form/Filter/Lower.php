<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Filter
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 */
namespace Gm\LandingPageEngine\Form\Filter;

class Lower extends AbstractFilter
{
    /**
     * Make a string lowercase
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return strtolower($value);
    }
}
