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

class Digits extends AbstractFilter
{
    /**
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return preg_replace('/[^[:digit:]]/', '', $value);
    }
}
