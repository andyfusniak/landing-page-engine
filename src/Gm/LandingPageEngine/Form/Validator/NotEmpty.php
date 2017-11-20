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

class NotEmpty extends AbstractValidator
{
    const IS_EMPTY = 'is-empty';

    /**
     * @var array
     */
    public static $messageTemplates = [
        'en' => [
            self::IS_EMPTY => 'This field is required'
        ],
        'th' => [
            self::IS_EMPTY => 'กรุณากรอกข้อมูล'
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

        if (strlen($value) < 1) {
            $this->messages[self::IS_EMPTY] = self::$messageTemplates[$this->lang][self::IS_EMPTY];
            return false;
        }

        return true;
    }
}
