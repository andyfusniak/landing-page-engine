<?php
/**
 * Landing Page Engine
 *
 * @package GmTest\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2017
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace GmTest\LandingPageEngine\Form\Validator;

use Gm\LandingPageEngine\Form\Validator\ThaiPhone;
use Gm\LandingPageEngine\Form\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ThaiPhoneTest extends TestCase
{
    /**
     * @var ThaiPhone
     */
    protected $validator;

    protected function setUp()
    {
        $this->validator = new ThaiPhone();
    }

    public function testValidThaiMobile()
    {
        $result = $this->validator->isValid('0843206078');

        $this->assertEquals($this->validator->getValue(), '0843206078');

        $messages = $this->validator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(true, $result);
        $this->assertEquals([], $messages);
    }

    public function testInvalidShortThaiMobile()
    {
        $result = $this->validator->isValid('084320607');
        $messages = $this->validator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiPhone::INVALID_THAI_MOBILE
                => ThaiPhone::$messageTemplates[ThaiPhone::INVALID_THAI_MOBILE]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidDoubleZeroPrefixThaiMobile()
    {
        $result = $this->validator->isValid('0043206078');
        $messages = $this->validator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiPhone::INVALID_PHONE_NUMBER
                => ThaiPhone::$messageTemplates[ThaiPhone::INVALID_PHONE_NUMBER]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidLongThaiMobile()
    {
        $result = $this->validator->isValid('08432060781');
        $messages = $this->validator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiPhone::INVALID_THAI_MOBILE
                => ThaiPhone::$messageTemplates[ThaiPhone::INVALID_THAI_MOBILE]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidThaiMobileWithCharacters()
    {
        $result = $this->validator->isValid('08432x06078');
        $messages = $this->validator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiPhone::INVALID_THAI_MOBILE
                => ThaiPhone::$messageTemplates[ThaiPhone::INVALID_THAI_MOBILE]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidParameterType()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->validator->isValid((int) 1234);
    }

    public function testValidThaiLandline()
    {
        $result = $this->validator->isValid('021054133');
        $this->assertEquals($this->validator->getValue(), '021054133');

        $messages = $this->validator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(true, $result);
        $this->assertEquals([], $messages);
    }

    public function testInvalidShortThaiLandline()
    {
        $result = $this->validator->isValid('02105413');
        $messages = $this->validator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiPhone::INVALID_THAI_LANDLINE
                => ThaiPhone::$messageTemplates[ThaiPhone::INVALID_THAI_LANDLINE]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidGeneralPhoneNumber()
    {
        $result = $this->validator->isValid('01172308829');
        $messages = $this->validator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiPhone::INVALID_PHONE_NUMBER
                => ThaiPhone::$messageTemplates[ThaiPhone::INVALID_PHONE_NUMBER]
        ];
        $this->assertEquals($expected, $messages);
    }
}
