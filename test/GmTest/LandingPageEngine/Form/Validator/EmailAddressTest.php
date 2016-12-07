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

use Gm\LandingPageEngine\Form\Validator\EmailAddress;
use Gm\LandingPageEngine\Form\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailAddressTest extends TestCase
{
    /**
     * @var EmailAddress 
     */
    protected $emailAddress;

    protected function setUp()
    {
        $this->emailAddress = new EmailAddress();
    }

    public function testValidInput()
    {
        $result = $this->emailAddress->isValid('andy@greycatmedia.co.uk');

        // ensure setValue() is implemented
        $this->assertEquals($this->emailAddress->getValue(), 'andy@greycatmedia.co.uk');

        $messages = $this->emailAddress->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(true, $result);
        $this->assertEquals([], $messages);
    }

    public function testInvalidInput()
    {
        $result = $this->emailAddress->isValid('andy@greycatmedia');
        $messages = $this->emailAddress->getMessages();
        
        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            EmailAddress::INVALID_EMAIL
                => EmailAddress::$messageTemplates[EmailAddress::INVALID_EMAIL]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->emailAddress->isValid((int) 1234);
    }
}
