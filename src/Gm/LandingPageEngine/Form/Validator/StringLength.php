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

class StringLength extends AbstractValidator
{
    const STRING_LENGTH_MIN = 'string-length-min';
    const STRING_LENGTH_MAX = 'string-length-max';

    /**
     * @var int
     */
    protected $min = 5;

    /**
     * @var int
     */
    protected $max = 10;

    /**
     * @var array
     */
    public static $messageTemplates = [
        self::STRING_LENGTH_MIN  => 'The input must be at least %s characters',
        self::STRING_LENGTH_MAX  => 'The input must be shorter than %s characters'
    ];

    public function __construct()
    {
    }

    /**
     * Set the minimal string length
     *
     * @param int $min the minimal string length in character
     * @return StringLength
     */
    public function setMin($min)
    {
        $this->min = (int) $min;
        return $this;
    }

    /**
     * Get the minimal string length
     *
     * @return int the minimal string length
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Set the maximum string length
     *
     * @param int $max maximum string length in characters
     * @return StringLength
     */
    public function setMax($max)
    {
        $this->max = (int) $max;
        return $this;
    }

    /**
     * Get the maximum string length
     *
     * @return int the maximum string length
     */
    public function getMax()
    {
        return $this->max;
    }

    public function isValid($value, $context = null)
    {
        if (!is_string($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string value',
                 __METHOD__
            ));
        }

        $this->setValue($value);

        if (mb_strlen($value) < $this->min) {
            $this->messages[self::STRING_LENGTH_MIN] = sprintf(
                self::$messageTemplates[self::STRING_LENGTH_MIN],
                $this->min
            );
            return false;
        }

        if (mb_strlen($value) > $this->max) {
            $this->messages[self::STRING_LENGTH_MAX] = sprintf(
                self::$messageTemplates[self::STRING_LENGTH_MAX],
                $this->max
            );
            return false;

        }

        return true;
    }
}
