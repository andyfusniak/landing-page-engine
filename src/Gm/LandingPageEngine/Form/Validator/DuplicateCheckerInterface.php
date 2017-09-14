<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2017
 */
namespace Gm\LandingPageEngine\Form\Validator;

interface DuplicateCheckerInterface
{
    public function isDuplicate($value);
}
