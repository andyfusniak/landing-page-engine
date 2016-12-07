<?php
/**
 * Landing Page Engine
 *
 * @package GmTest\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace GmTest\LandingPageEngine\Form\Validator;

use Gm\LandingPageEngine\Form\Validator\ThaiMobilePhoneNumber;
use Gm\LandingPageEngine\Form\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ThaiMobilePhoneNumberTest extends TestCase
{
    /**
     * @var ThaiMobilePhoneNumber 
     */
    protected $thaiMobilePhoneNumberValidator;

    protected function setUp()
    {
        $this->thaiMobilePhoneNumberValidator = new ThaiMobilePhoneNumber();
    }

    public function testValidThaiMobile()
    {
        $result = $this->thaiMobilePhoneNumberValidator->isValid('0843206078');

        // ensure setValue() is implemented
        $this->assertEquals($this->thaiMobilePhoneNumberValidator->getValue(), '0843206078');

        $messages = $this->thaiMobilePhoneNumberValidator->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(true, $result);
        $this->assertEquals([], $messages);
    }

    public function testInvalidShortThaiMobile()
    {
        $result = $this->thaiMobilePhoneNumberValidator->isValid('084320607');
        $messages = $this->thaiMobilePhoneNumberValidator->getMessages();
        
        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiMobilePhoneNumber::INVALID_THAI_MOBILE
                => ThaiMobilePhoneNumber::$messageTemplates[ThaiMobilePhoneNumber::INVALID_THAI_MOBILE]
        ];
        $this->assertEquals($expected, $messages);
    }
    
    public function testInvalidDoubleZeroPrefixThaiMobile()
    {
        $result = $this->thaiMobilePhoneNumberValidator->isValid('0043206078');
        $messages = $this->thaiMobilePhoneNumberValidator->getMessages();
        
        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiMobilePhoneNumber::INVALID_THAI_MOBILE
                => ThaiMobilePhoneNumber::$messageTemplates[ThaiMobilePhoneNumber::INVALID_THAI_MOBILE]
        ];
        $this->assertEquals($expected, $messages);
    }
    
    public function testInvalidLongThaiMobile()
    {
        $result = $this->thaiMobilePhoneNumberValidator->isValid('08432060781');
        $messages = $this->thaiMobilePhoneNumberValidator->getMessages();
        
        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiMobilePhoneNumber::INVALID_THAI_MOBILE
                => ThaiMobilePhoneNumber::$messageTemplates[ThaiMobilePhoneNumber::INVALID_THAI_MOBILE]
        ];
        $this->assertEquals($expected, $messages);
    }
    
    public function testInvalidThaiMobileWithCharacters()
    {
        $result = $this->thaiMobilePhoneNumberValidator->isValid('08432x06078');
        $messages = $this->thaiMobilePhoneNumberValidator->getMessages();
        
        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            ThaiMobilePhoneNumber::INVALID_THAI_MOBILE
                => ThaiMobilePhoneNumber::$messageTemplates[ThaiMobilePhoneNumber::INVALID_THAI_MOBILE]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidParameterType()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->thaiMobilePhoneNumberValidator->isValid((int) 1234);        
    }
}
