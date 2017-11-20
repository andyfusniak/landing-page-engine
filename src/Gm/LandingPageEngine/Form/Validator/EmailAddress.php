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
        'en' => [
            self::INVALID_EMAIL => 'The email address is invalid'
        ],
        'th' => [
            self::INVALID_EMAIL => 'กรอกอีเมลไม่ถูกต้อง'
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

        if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->messages[self::INVALID_EMAIL] = self::$messageTemplates[$this->lang][self::INVALID_EMAIL];
            return false;
        }

        return true;
    }
}
