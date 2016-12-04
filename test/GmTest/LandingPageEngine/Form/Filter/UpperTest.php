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

use Gm\LandingPageEngine\Form\Filter\Upper;
use PHPUnit\Framework\TestCase;

class UpperTest extends TestCase
{
    /**
     * @var Upper
     */
    protected $upper;

    protected function setUp()
    {
        $this->upper = new Upper();
    }

    public function testFilterUpperString()
    {
        $result = $this->upper->filter('andy');
        $this->assertEquals('ANDY', $result);
        $this->assertInternalType('string', $result);
    }
}
