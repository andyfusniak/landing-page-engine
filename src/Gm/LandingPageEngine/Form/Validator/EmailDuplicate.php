<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2017
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Form\Validator;

class EmailDuplicate extends AbstractValidator
{
    const INVALID_EMAIL_DUPLICATE = 'invalid-thai-mobile-duplicate';

    /**
     * @var DuplicateCheckerInterface
     */
    protected $duplicateChecker;

    /**
     * @var array
     */
    public static $messageTemplates = [
        self::INVALID_EMAIL_DUPLICATE   => 'อีเมล่ซ้ำ กรุณากดอีเมลใหม่'
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
            $this->messages[self::INVALID_EMAIL_DUPLICATE]
                    = self::$messageTemplates[self::INVALID_EMAIL_DUPLICATE];
            return false;
        }

        return true;
    }
}
