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

use Gm\LandingPageEngine\Form\Validator\DigitsOnly;
use Gm\LandingPageEngine\Form\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DigitsTest extends TestCase
{
    /**
     * @var DigitsOnlyTest 
     */
    protected $digitsOnlyTest;

    protected function setUp()
    {
        $this->digitsOnlyTest = new DigitsOnly();
    }

    public function testValidInput()
    {
        $result = $this->digitsOnlyTest->isValid('12345');

        // ensure setValue() is called
        $this->assertEquals($this->digitsOnlyTest->getValue(), '12345');

        $messages = $this->digitsOnlyTest->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(true, $result);
        $this->assertEquals([], $messages);
    }

    public function testInvalidInput()
    {
        $result = $this->digitsOnlyTest->isValid('123x45');
        $messages = $this->digitsOnlyTest->getMessages();
        
        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            DigitsOnly::NOT_DIGITS
                => DigitsOnly::$messageTemplates[DigitsOnly::NOT_DIGITS]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->digitsOnlyTest->isValid((int) 1234);
    }
}
