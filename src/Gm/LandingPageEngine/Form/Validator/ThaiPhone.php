<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2017
 */
namespace Gm\LandingPageEngine\Form\Validator;

class ThaiPhone extends AbstractValidator
{
    const INVALID_THAI_MOBILE   = 'invalid-thai-mobile';
    const INVALID_THAI_LANDLINE = 'invalid-thai-landline';
    const INVALID_PHONE_NUMBER  = 'invalid-phone-number';

    /**
     * @var array
     */
    public static $messageTemplates = [
        'en' => [
            self::INVALID_THAI_MOBILE   => 'Invalid mobile number',
            self::INVALID_THAI_LANDLINE => 'Invalid telephone number',
            self::INVALID_PHONE_NUMBER  => 'Please use Thai phone numbers only'
        ],
        'th' => [
            self::INVALID_THAI_MOBILE   => 'เบอร์มือถือไม่ถูกต้อง',
            self::INVALID_THAI_LANDLINE => 'เบอร์โทรศัพท์ไม่ถูกต้อง',
            self::INVALID_PHONE_NUMBER  => 'กรอกเบอร์มือถิอไทยเเละเบอร์บ้านไทยเท่านั้น'
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

        // thai mobile phone numbers start with 0
        // followed by a 6, 8 or 9
        //
        // thai land line numbers start with 0
        // folowed by a 2, 3, 4, 5 or 7
        if (preg_match('/^0[689](.*)$/', $value)) {
            if (preg_match('/^0[689][0-9]{8}$/', $value)) {
                return true;
            } else {
                $this->messages[self::INVALID_THAI_MOBILE]
                    = self::$messageTemplates[$this->lang][self::INVALID_THAI_MOBILE];
                return false;
            }
        } else if (preg_match('/^0[23457](.*)$/', $value)) {
            if (preg_match('/^0[23457][0-9]{7}$/', $value)) {
                return true;
            } else {
                $this->messages[self::INVALID_THAI_LANDLINE]
                    = self::$messageTemplates[$this->lang][self::INVALID_THAI_LANDLINE];
                return false;
            }
        } else {
            $this->messages[self::INVALID_PHONE_NUMBER]
                    = self::$messageTemplates[$this->lang][self::INVALID_PHONE_NUMBER];
            return false;
        }
    }
}
