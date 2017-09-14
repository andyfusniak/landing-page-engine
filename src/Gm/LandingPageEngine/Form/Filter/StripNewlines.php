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

class StripNewlines extends AbstractFilter
{
    /**
     * Strip newline control characters
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return str_replace(["\n", "\r"], '', $value);
    }
}
