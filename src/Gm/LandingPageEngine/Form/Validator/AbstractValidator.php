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

    /**
     * @var array
     */
    public static $messageTemplates = [];

    /**
     * Get an associative array of message template strings
     *
     * @return array
     */
    public static function getMessageTemplates()
    {
        return static::$messageTemplates;
    }

    /**
     * Set the validation failure message for a given key
     *
     * @param string $key the message key e.g. 'string-length-min' etc
     * @param string $message the utf-8 encoded message to use
     */
    public static function setMessageTemplate($key, $message)
    {
        static::$messageTemplates[$key] = $message;
    }

    /**
     * Get the validation failure message for a given key
     *
     * @param string $key the message key
     * @return string
     */
    public static function getMessageTemplate($key)
    {
        if (!isset(static::$messageTemplates[$key])) {
            throw new Exception\InvalidArgumentException(
                "No message template exists for key '$key'"
            );
        }
        return static::$messageTemplates[$key];
    }

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

    /**
     * @return string name of the validator
     */
    public function __toString()
    {
        $parts = explode('\\', get_class($this));
        return $parts[count($parts) - 1];
    }
}
