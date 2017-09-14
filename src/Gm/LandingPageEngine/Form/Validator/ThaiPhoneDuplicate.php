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

class ThaiPhoneDuplicate extends AbstractValidator
{
    const INVALID_THAI_MOBILE_DUPLICATE = 'invalid-thai-mobile-duplicate';

    /**
     * @var DuplicateCheckerInterface
     */
    protected $duplicateChecker;

    /**
     * @var array
     */
    public static $messageTemplates = [
        self::INVALID_THAI_MOBILE_DUPLICATE   => 'เบอร์โทรศัพท์ซ้ำ กรุณากดเบอร์ใหม่'
    ];

    public function __construct(DuplicateCheckerInterface $duplicateChecker)
    {
        $this->duplicateChecker = $duplicateChecker;
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

        if (true === $this->duplicateChecker->isDuplicate($value)) {
            $this->messages[self::INVALID_THAI_MOBILE_DUPLICATE]
                    = self::$messageTemplates[self::INVALID_THAI_MOBILE_DUPLICATE];
            return false;
        }

        return true;
    }
}
