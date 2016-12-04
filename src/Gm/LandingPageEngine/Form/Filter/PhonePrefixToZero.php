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

class PhonePrefixToZero extends AbstractFilter
{
    /**
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $stringLength = mb_strlen($value);

        if (($stringLength >= 4)
            && (substr($value, 0, 1) === '+')) {
            
            // if the telephone
            if (substr($value, 3, 1) === '0') {
                return substr($value, 4, $stringLength);
            } else {
                return '0' . substr($value, 3, $stringLength);
            }
        }

        return $value;
    }
}
