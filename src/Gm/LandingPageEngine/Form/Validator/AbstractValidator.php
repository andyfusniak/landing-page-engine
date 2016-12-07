<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Form\Validator;

abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var array|null
     */
    protected $messages = [];

    /**
     * @var mixed
     */
    protected $value;

    public function setValue($value)
    {
        $this->value = (string) $value;
        $this->messages = [];
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array of error messages
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
