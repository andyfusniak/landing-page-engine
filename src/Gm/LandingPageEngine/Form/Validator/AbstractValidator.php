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
     * Display language
     *
     * @var string
     */
    protected $lang = 'en';

    /**
     * @var array
     */
    public static $messageTemplates = [];

    /**
     * Get the default display language
     *
     * @return 2-digit lower case languge string e.g. 'en', 'th' etc (ISO 639-1)
     */
    public function getLanguage()
    {
        return $this->lang;
    }

    /**
     * Set the default display language
     *
     * @param string $lang 2-digit lower case languge string e.g. 'en', 'th' etc (ISO 639-1)
     */
    public function setLanguage($lang)
    {
        $this->lang = $lang;
        return $this;
    }

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
     * @param string $lang 2-digit lower case languge string e.g. 'en', 'th' etc (ISO 639-1)
     * @param string $key the message key e.g. 'string-length-min' etc
     * @param string $message the utf-8 encoded message to use
     */
    public static function setMessageTemplate($lang, $key, $message)
    {
        static::$messageTemplates[$lang][$key] = $message;
    }

    /**
     * Override the default validator message template strings for all languages
     * Use the following array format
     * [
     *    'en' => [
     *        'is-empty' => 'new message'
     *    ],
     *    'th' => [
     *        'is-empty' => 'new message'
     *    ]
     * ]
     * @param array $messageTemplates templates array to use for overrides
     */
    public static function overrideMessageTemplates($messageTemplates)
    {
        foreach ($messageTemplates as $lang => $keyValuePair) {
            foreach ($keyValuePair as $key => $message) {
                static::$messageTemplates[$lang][$key] = $message;
            }
        }
    }

    /**
     * Get the validation failure message for a given key
     *
     * @param string $lang 2-digit lower case languge string e.g. 'en', 'th' etc (ISO 639-1)
     * @param string $key the message key
     * @return string
     */
    public static function getMessageTemplate($lang, $key)
    {
        if (!isset(static::$messageTemplates[$lang][$key])) {
            throw new Exception\InvalidArgumentException(
                "No message template exists for lang '$lang', key '$key'"
            );
        }
        return static::$messageTemplates[$lang][$key];
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
