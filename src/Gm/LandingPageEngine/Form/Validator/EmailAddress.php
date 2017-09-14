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

class EmailAddress extends AbstractValidator
{
    const INVALID_EMAIL = 'invalid-email';

    /**
     * @var array
     */
    public static $messageTemplates = [
        self::INVALID_EMAIL => 'The email address is invalid'
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

        if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->messages[self::INVALID_EMAIL] = self::$messageTemplates[self::INVALID_EMAIL];
            return false;
        }

        return true;
    }
}
