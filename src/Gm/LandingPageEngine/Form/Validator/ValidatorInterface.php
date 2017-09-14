<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 */
namespace Gm\LandingPageEngine\Form\Validator;

interface ValidatorInterface
{
    /**
     * @param mixed $value
     * @param mixed $context
     * @return bool
     */
    public function isValid($value, $context);

    /**
     * @return array of error messages
     */
    public function getMessages();
}
