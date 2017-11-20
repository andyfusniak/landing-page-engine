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

class DigitsOnly extends AbstractValidator
{
    const NOT_DIGITS = 'not-digits';

    /**
      * @var array
      */
    public static $messageTemplates = [
        'en' => [
            self::NOT_DIGITS => 'Must contain digits only'
        ],
        'th' => [
            self::NOT_DIGITS => 'กรอกเป็นตัวเลขเท่านั้น'
        ]
    ];

    public function isValid($value, $context = null)
    {
        if (!is_string($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string value',
                __METHOD__
            ));
        }

        $this->setValue($value);

        if (ctype_digit($value)) {
            return true;
        }

        $this->messages[self::NOT_DIGITS]
            = self::$messageTemplates[$this->lang][self::NOT_DIGITS];
        return false;
    }
}
