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
    const LANG = 'th';

    /**
     * @var StringLength
     */
    protected $stringLengthTest;

    protected function setUp()
    {
        $this->stringLengthTest = new StringLength();
        $this->stringLengthTest->setLanguage(self::LANG);
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
                StringLength::$messageTemplates[self::LANG][StringLength::STRING_LENGTH_MIN],
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
                StringLength::$messageTemplates[self::LANG][StringLength::STRING_LENGTH_MIN],
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
                StringLength::$messageTemplates[self::LANG][StringLength::STRING_LENGTH_MAX],
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

    public function testConfigurableMessageTemplate()
    {
        $stringLengthMin = 'ต้องกรอกข้อความอย่างน้อย %s ตัวอักษร';
        $stringLengthMax = 'ข้อความต้องน้อยกว่า %s ตัวอักษร';

        $stringLengthValidator = new StringLength();
        $stringLengthValidator->setLanguage('th');

        StringLength::setMessageTemplate(
            'th',
            StringLength::STRING_LENGTH_MIN,
            $stringLengthMin
        );

        StringLength::setMessageTemplate(
            'th',
            StringLength::STRING_LENGTH_MAX,
            $stringLengthMax
        );

        $this->assertEquals(
            $stringLengthMin,
            StringLength::getMessageTemplate('th', StringLength::STRING_LENGTH_MIN)
        );

        $this->assertEquals(
            $stringLengthMax,
            StringLength::getMessageTemplate('th', StringLength::STRING_LENGTH_MAX)
        );

        // too short
        $stringLengthValidator->setMin(3);
        $stringLengthValidator->setMax(5);

        $result = $stringLengthValidator->isValid('12');
        $messages = $stringLengthValidator->getMessages();

        $expected = [
            StringLength::STRING_LENGTH_MIN => sprintf(
                $stringLengthMin,
                $stringLengthValidator->getMin()
            )
        ];
        $this->assertEquals($expected, $messages);
    }
}
