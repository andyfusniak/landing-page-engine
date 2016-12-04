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

use Gm\LandingPageEngine\Form\Filter\StripNewlines;
use PHPUnit\Framework\TestCase;

class StripNewlinesTest extends TestCase
{
    /**
     * @var Newlines
     */
    protected $newlines;

    protected function setUp()
    {
        $this->stripNewlines = new StripNewlines();
    }

    public function testFilterStripNewlinesString()
    {
        $value = 'Line one' . PHP_EOL . 'Line Two' . PHP_EOL . PHP_EOL;
        $result = $this->stripNewlines->filter($value);
        $this->assertEquals('Line oneLine Two', $result);
        $this->assertInternalType('string', $result);
    }
}
