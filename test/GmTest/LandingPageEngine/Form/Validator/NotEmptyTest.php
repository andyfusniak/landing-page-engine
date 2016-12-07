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

use Gm\LandingPageEngine\Form\Validator\NotEmpty;
use Gm\LandingPageEngine\Form\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NotEmptyTest extends TestCase
{
    /**
     * @var NotEmpty 
     */
    protected $notEmpty;

    protected function setUp()
    {
        $this->notEmpty = new NotEmpty();
    }

    public function testValidInput()
    {
        $result = $this->notEmpty->isValid('something');

        // ensure setValue() is implemented
        $this->assertEquals($this->notEmpty->getValue(), 'something');

        $messages = $this->notEmpty->getMessages();

        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(true, $result);
        $this->assertEquals([], $messages);
    }

    public function testInvalidInput()
    {
        $result = $this->notEmpty->isValid('');
        $messages = $this->notEmpty->getMessages();
        
        $this->assertInternalType('bool', $result);
        $this->assertInternalType('array', $messages);

        $this->assertEquals(false, $result);

        $expected = [
            NotEmpty::IS_EMPTY
                => NotEmpty::$messageTemplates[NotEmpty::IS_EMPTY]
        ];
        $this->assertEquals($expected, $messages);
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->notEmpty->isValid((int) 1234);
    }
}
