<?php
/**
 * Landing Page Engine
 *
 * @package GmTest\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 */
namespace GmTest\LandingPageEngine\Form\Validator;

use Gm\LandingPageEngine\Form\Validator\ValidatorChain;
use Gm\LandingPageEngine\Form\Validator\NotEmpty;
use Gm\LandingPageEngine\Form\Validator\EmailAddress;
use Gm\LandingPageEngine\Form\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValidatorChainTest extends TestCase
{
    const LANG = 'th';

    /**
     * @var ValidatorChain
     */
    protected static $validatorChain;

    /**
     * @var Validator
     */
    protected static $v1;

    /**
     * @var Validator
     */
    protected static $v2;

    public static function setUpBeforeClass()
    {
        self::$validatorChain = new ValidatorChain();
        self::$v1 = new NotEmpty();
        self::$v1->setLanguage(self::LANG);
        self::$v2 = new EmailAddress();
        self::$v2->setLanguage(self::LANG);
    }

    public function testAttachV1()
    {
        $result = self::$validatorChain->attach(self::$v1);
        $this->assertInstanceOf(ValidatorChain::class, $result);
    }

    public function testAttachV2()
    {
        self::$validatorChain->attach(self::$v2);
    }

    /**
     * @depends testAttachV1
     * @depends testAttachV2
     */
    public function testOne()
    {
        $result = self::$validatorChain->isValid('');

        $messages = self::$validatorChain->getMessages();
        $this->assertInternalType('array', $messages);
        $this->assertEquals(2, count($messages));

        $expectedResult = [
            NotEmpty::IS_EMPTY => NotEmpty::$messageTemplates[self::LANG][NotEmpty::IS_EMPTY],
            EmailAddress::INVALID_EMAIL => EmailAddress::$messageTemplates[self::LANG][EmailAddress::INVALID_EMAIL],
        ];
        $this->assertEquals($expectedResult, $messages);

        $this->assertInternalType('bool', $result);
    }

    public function testInvalidParameterTypeAttachMethod()
    {
       $this->expectException(InvalidArgumentException::class);
       $result = self::$validatorChain->attach(new \stdClass());
    }
}
