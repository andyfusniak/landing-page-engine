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

 class Trim extends AbstractFilter
 {
     /**
      * Strip whitespace (or other characters) from the beginning and
      * end of a string.
      *
      * @param string $value
      * @return string
      */
     public function filter($value)
     {
         return trim($value);
     }
 }
