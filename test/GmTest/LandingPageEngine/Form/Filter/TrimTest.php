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

use Gm\LandingPageEngine\Form\Filter\Trim;
use PHPUnit\Framework\TestCase;

class TrimTest extends TestCase
{
    /**
     * @var Trim
     */
    protected $trim;

    protected function setUp()
    {
        $this->trim = new Trim();
    }

    public function testFilterTrimString()
    {
        $result = $this->trim->filter('Andy ');
        $this->assertEquals('Andy', $result);
        $this->assertInternalType('string', $result);
    }
}
