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

use Gm\LandingPageEngine\Form\Filter\Digits;
use PHPUnit\Framework\TestCase;

class DigitsTest extends TestCase
{
    /**
     * @var Digits
     */
    protected $digits;

    protected function setUp()
    {
        $this->digits = new Digits();
    }

    public function testFilterDigitsString()
    {
        $result = $this->digits->filter('John1234 Doe5678');
        $this->assertEquals('12345678', $result);
        $this->assertInternalType('string', $result);
    }
}
