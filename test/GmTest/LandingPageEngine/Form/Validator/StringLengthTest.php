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

use Gm\LandingPageEngine\Form\Validator\StringLength;
use Gm\LandingPageEngine\Form\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StringLengthTest extends TestCase
{
    /**
     * @var StringLength
     */
    protected $stringLengthTest;

    protected function setUp()
    {
        $this->stringLengthTest = new StringLength();
    }

    public function testValidInput()
    {
        // 8 characters (default min=5, max=10)
        $result = $this->stringLengthTest->isValid('12345678');

        // ensure setValue() is implemented
        $this->assertEquals($this->stringLengthTest->getValue(), '12345678');

        $messages = $this->stringLengthTest->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(true, $result);
        $this->assertEquals([], $messages);
    }

    public function testInvalidMinInput()
    {
        // 4 is too short as the default minimal is 5
        $result = $this->stringLengthTest->isValid('1234');
        $messages = $this->stringLengthTest->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            StringLength::STRING_LENGTH_MIN => sprintf(
                StringLength::$messageTemplates[StringLength::STRING_LENGTH_MIN],
                $this->stringLengthTest->getMin()
            )
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidSetMinInput()
    {
        $this->stringLengthTest->setMin(3);
        $this->stringLengthTest->setMax(5);

        $result = $this->stringLengthTest->isValid('12');
        $messages = $this->stringLengthTest->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            StringLength::STRING_LENGTH_MIN => sprintf(
                StringLength::$messageTemplates[StringLength::STRING_LENGTH_MIN],
                $this->stringLengthTest->getMin()
            )
        ];
        $this->assertEquals($expected, $messages);
    }


    public function testInvalidMaxInput()
    {
        // 11 is too long as the default maximum is 10
        $result = $this->stringLengthTest->isValid('12345678901');
        $messages = $this->stringLengthTest->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            StringLength::STRING_LENGTH_MAX => sprintf(
                StringLength::$messageTemplates[StringLength::STRING_LENGTH_MAX],
                $this->stringLengthTest->getMax()
            )
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidParameterTypeAttachMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->stringLengthTest->isValid((int) 12345);
    }
}
