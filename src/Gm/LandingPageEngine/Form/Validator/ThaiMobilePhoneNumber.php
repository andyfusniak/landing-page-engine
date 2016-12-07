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

class ThaiMobilePhoneNumber extends AbstractValidator
{
    const INVALID_THAI_MOBILE = 'invalid-thai-mobile';
    
    /**
     * @var array
     */
    public static $messageTemplates = [
        self::INVALID_THAI_MOBILE => 'เบอร์มือถือไม่ถูกต้อง'
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
        
        // a valid phone number starts with 0
        // and has 9 more digits
        if (preg_match('/^0[1-9][0-9]{8}$/', $value)) {
            return true;
        }

        $this->messages[self::INVALID_THAI_MOBILE] = self::$messageTemplates[self::INVALID_THAI_MOBILE];
        return false;
    }
}
