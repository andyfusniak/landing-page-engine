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

class Lcfirst extends AbstractFilter
{
    /**
     *  Make a string's first character lowercase
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return lcfirst($value);
    }
}
