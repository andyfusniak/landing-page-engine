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

use Gm\LandingPageEngine\Form\Filter\Ucwords;
use PHPUnit\Framework\TestCase;

class UcwordsTest extends TestCase
{
    /**
     * @var Upwords
     */
    protected $ucwords;

    protected function setUp()
    {
        $this->ucwords = new Ucwords();
    }

    public function testFilterUcwordsString()
    {
        $result = $this->ucwords->filter('one two three');
        $this->assertEquals('One Two Three', $result);
        $this->assertInternalType('string', $result);
    }
}
