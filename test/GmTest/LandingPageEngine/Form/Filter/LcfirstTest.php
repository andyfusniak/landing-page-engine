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

use Gm\LandingPageEngine\Form\Filter\Lcfirst;
use PHPUnit\Framework\TestCase;

class LcfirstTest extends TestCase
{
    /**
     * @var Lcfirst
     */
    protected $lcfirst;

    protected function setUp()
    {
        $this->lcfirst = new Lcfirst();
    }

    public function testFilterLcfirstString()
    {
        $result = $this->lcfirst->filter('John');
        $this->assertEquals('john', $result);
        $this->assertInternalType('string', $result);
    }
}
