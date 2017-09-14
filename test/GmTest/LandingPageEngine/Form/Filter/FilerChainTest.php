<?php
/**
 * Landing Page Engine
 *
 * @package GmTest\LandingPageEngine
 * @subpackage Form\Filter
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 */
namespace GmTest\LandingPageEngine\Form\Filter;

use Gm\LandingPageEngine\Form\Filter\FilterChain;
use Gm\LandingPageEngine\Form\Filter\FilterInterface as Filter;
use Gm\LandingPageEngine\Form\Filter\PhonePrefixToZero;
use Gm\LandingPageEngine\Form\Filter\Trim;
use Gm\LandingPageEngine\Form\Filter\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FilterChainTest extends TestCase
{
    /**
     * @var FilterChain
     */
    protected static $filterChain;

    /**
     * @var Filter
     */
    protected static $filterOne;

    /**
     * @var Filter
     */
    protected static $filterTwo;

    public static function setUpBeforeClass()
    {
        self::$filterChain = new FilterChain();
        self::$filterOne = new Trim();
        self::$filterTwo = new PhonePrefixToZero();
    }

    public function testAttachOne()
    {
        $result = self::$filterChain->attach(self::$filterOne);
        $this->assertInstanceOf(FilterChain::class, $result);
    }

    public function testAttachTwo()
    {
        self::$filterChain->attach(self::$filterTwo);
    }

    /**
     * @depends testAttachOne
     * @depends testAttachTwo
     */
    public function testFileChainOne()
    {
        $result = self::$filterChain->filter('+66843206078');
        $this->assertEquals('0843206078', $result);
        $this->assertInternalType('string', $result);
    }

    /**
     * @depends testAttachOne
     * @depends testAttachTwo
     */
    public function testFileChainTwo()
    {
        $result = self::$filterChain->filter('   +66843206078   ');
        $this->assertEquals('0843206078', $result);
        $this->assertInternalType('string', $result);
    }

    public function testInvalidParameterType()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = self::$filterChain->filter((int) 1234);
    }

    public function testInvalidParameterTypeAttachMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = self::$filterChain->attach(new \stdClass());
    }
}

