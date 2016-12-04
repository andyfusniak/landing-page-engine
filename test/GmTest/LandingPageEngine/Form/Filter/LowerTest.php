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

use Gm\LandingPageEngine\Form\Filter\Lower;
use PHPUnit\Framework\TestCase;

class LowerTest extends TestCase
{
    /**
     * @var Lower
     */
    protected $lower;

    protected function setUp()
    {
        $this->lower = new Lower();
    }

    public function testFilterLowerString()
    {
        $result = $this->lower->filter('ANDY');
        $this->assertEquals('andy', $result);
        $this->assertInternalType('string', $result);
    }
}
