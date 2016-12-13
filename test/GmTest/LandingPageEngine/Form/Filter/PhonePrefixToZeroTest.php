<?php
/**
 * Landing Page Engine
 *
 * @package GmTest\LandingPageEngine
 * @subpackage Form\Filter
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace GmTest\LandingPageEngine\Form\Filter;

use Gm\LandingPageEngine\Form\Filter\PhonePrefixToZero;
use PHPUnit\Framework\TestCase;

class PhonePrefixToZeroTest extends TestCase
{
    /**
     * @var PhonePrefixToZero
     */
    protected $phonePrexifToZero;

    protected function setUp()
    {
        $this->phonePrefixToZero = new PhonePrefixToZero();
    }

    public function testFilterWithPrefix()
    {
        $result = $this->phonePrefixToZero->filter('+66843206078');
        $this->assertEquals('0843206078', $result);
        $this->assertInternalType('string', $result);
    }

    public function testFilterWithPrexixAndAdditionalZero()
    {
        $result = $this->phonePrefixToZero->filter('+660843206078');
        $this->assertEquals('0843206078', $result);
        $this->assertInternalType('string', $result);
    }

    public function testFilterWithNormalNumber()
    {
        $result = $this->phonePrefixToZero->filter('0843206078');
        $this->assertEquals('0843206078', $result);
        $this->assertInternalType('string', $result);
    }
}
