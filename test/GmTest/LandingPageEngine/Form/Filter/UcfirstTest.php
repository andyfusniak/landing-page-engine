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

use Gm\LandingPageEngine\Form\Filter\Ucfirst;
use PHPUnit\Framework\TestCase;

class UcfirstTest extends TestCase
{
    /**
     * @var Upfirst
     */
    protected $ucfirst;

    protected function setUp()
    {
        $this->ucfirst = new Ucfirst();
    }

    public function testFilterUcfirstString()
    {
        $result = $this->ucfirst->filter('john');
        $this->assertEquals('John', $result);
        $this->assertInternalType('string', $result);
    }
}
