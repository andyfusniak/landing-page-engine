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

class Ucwords extends AbstractFilter
{
    /**
     * Uppercase the first character of each word in a string
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return ucwords($value);
    }
}
