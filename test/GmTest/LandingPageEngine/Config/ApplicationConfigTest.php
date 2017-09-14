<?php
/**
 * Landing Page Engine
 *
 * @package GmTest\LandingPageEngine
 * @subpackage Config
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 */
namespace GmTest\LandingPageEngine\Config;


use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Gm\LandingPageEngine\Config\ApplicationConfig;

class ApplicationConfigTest extends TestCase
{
    /**
     * @var ApplicationConfig
     */
    protected $applicationConfig;

    protected function setUp()
    {
        $this->applicationConfig = new ApplicationConfig(
            '/var/www/projectroot'
        );
    }

    public function testConstructor()
    {
        $this->applicationConfig = new ApplicationConfig(
            '/var/www/projectroot_2',
            '/htdocs/webroot',
            '/home/ubuntu/themesroot'
        );

        $result = $this->applicationConfig->getProjectRoot();
        $this->assertEquals('/var/www/projectroot_2', $result);
        $this->assertInternalType('string', $result);

        $result = $this->applicationConfig->getWebRoot();
        $this->assertEquals('/htdocs/webroot', $result);
        $this->assertInternalType('string', $result);

        $result = $this->applicationConfig->getThemesRoot();
        $this->assertEquals('/home/ubuntu/themesroot', $result);
        $this->assertInternalType('string', $result);
    }

    public function testGetProjectWebRoot()
    {
        $result = $this->applicationConfig->getProjectRoot();
        $this->assertEquals('/var/www/projectroot', $result);
        $this->assertInternalType('string', $result);
    }

    public function testGetVarDir()
    {
        $result = $this->applicationConfig->getVarDir();
        $this->assertEquals('/var/www/projectroot/var', $result);
        $this->assertInternalType('string', $result);
    }

    public function testGetLogDir()
    {
        $result = $this->applicationConfig->getLogDir();
        $this->assertEquals('/var/www/projectroot/var/log', $result);
        $this->assertInternalType('string', $result);
    }

    public function testGetWebRoot()
    {
        $result = $this->applicationConfig->getWebRoot();
        $this->assertEquals('/var/www/projectroot/public', $result);
        $this->assertInternalType('string', $result);
    }

    public function testGetThemesRoot()
    {
        $result = $this->applicationConfig->getThemesRoot();
        $this->assertEquals('/var/www/projectroot/themes', $result);
        $this->assertInternalType('string', $result);
    }

    public function testDefaultGetTwigCacheDir()
    {
        $result = $this->applicationConfig->getTwigCacheDir();
        $this->assertEquals('/var/www/projectroot/var/twig_cache', $result);
        $this->assertInternalType('string', $result);
    }

    public function testSetAndGetTwigCacheDir()
    {
        $result = $this->applicationConfig->setTwigCacheDir('/tmp/twig_cache');
        $this->assertInstanceOf(ApplicationConfig::class, $result);

        $result = $this->applicationConfig->getTwigCacheDir();
        $this->assertEquals('/tmp/twig_cache', $result);
        $this->assertInternalType('string', $result);
    }

    public function testDefaultDeveloperModeDefault()
    {
        $result = $this->applicationConfig->getDeveloperMode();
        $this->assertEquals(true, $result);
        $this->assertInternalType('bool', $result);
    }

    public function testSetAndGetDeveloperMode()
    {
        $result = $this->applicationConfig->setDeveloperMode(false);
        $this->assertInstanceOf(ApplicationConfig::class, $result);

        $result = $this->applicationConfig->getDeveloperMode();
        $this->assertEquals(false, $result);
        $this->assertInternalType('bool', $result);
    }

    public function testSetDeveloperModeInvalidArugmentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->applicationConfig->setDeveloperMode(1);
    }

    public function testSkipAutoVarDirSetupDefault()
    {
        $result = $this->applicationConfig->getSkipAutoVarDirSetup();
        $this->assertEquals(false, $result);
        $this->assertInternalType('bool', $result);
    }

    public function testSetAndGetSkipAuthVarDirSetup()
    {
        $result = $this->applicationConfig->setSkipAutoVarDirSetup(true);
        $this->assertInstanceOf(ApplicationConfig::class, $result);

        $result = $this->applicationConfig->getSkipAutoVarDirSetup();
        $this->assertEquals(true, $result);
        $this->assertInternalType('bool', $result);
    }

    public function testSkipAutoVarDirSetupInvalidArugmentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->applicationConfig->setSkipAutoVarDirSetup(1);
    }

    public function testSkipAutoThemeActivationDefault()
    {
        $result = $this->applicationConfig->getSkipAutoThemeActivation();
        $this->assertEquals(false, $result);
        $this->assertInternalType('bool', $result);
    }

    public function testSetAndGetSkipAutoThemeActivation()
    {
        $result = $this->applicationConfig->setSkipAutoThemeActivation(true);
        $this->assertInstanceOf(ApplicationConfig::class, $result);

        $result = $this->applicationConfig->getSkipAutoThemeActivation();
        $this->assertEquals(true, $result);
        $this->assertInternalType('bool', $result);
    }

    public function testSetAndGetSkipAutoThemeActivationInvalidArugmentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->applicationConfig->setSkipAutoThemeActivation(1);
    }

    public function testGetNoCaptureDefault()
    {
        $result = $this->applicationConfig->getNoCapture();
        $this->assertEquals(false, $result);
        $this->assertInternalType('bool', $result);
    }

    public function testSetAndGetNoCapture()
    {
        $result = $this->applicationConfig->setNoCapture(true);
        $this->assertInstanceOf(ApplicationConfig::class, $result);

        $result = $this->applicationConfig->getNoCapture();
        $this->assertEquals(true, $result);
        $this->assertInternalType('bool', $result);
    }

    public function testSetAndGetNoCaptureInvalidArugmentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->applicationConfig->setNoCapture('test');
    }

    public function testGetLogLevelDefault()
    {
        $result = $this->applicationConfig->getLogLevel();
        $this->assertEquals(Logger::DEBUG, $result);
        $this->assertInternalType('int', $result);
    }

    public function testSetAndGetLogLevel()
    {
        $result = $this->applicationConfig->setLogLevel(Logger::ERROR);
        $this->assertInstanceOf(ApplicationConfig::class, $result);

        $result = $this->applicationConfig->getLogLevel();
        $this->assertEquals(Logger::ERROR, $result);
        $this->assertInternalType('int', $result);
    }

    public function testGetLogFilePathDefault()
    {
        $result = $this->applicationConfig->getLogFilePath();
        $this->assertEquals('/var/www/projectroot/var/log/lpengine.log', $result);
        $this->assertInternalType('string', $result);
    }

    public function testSetAndGetLogFilePath()
    {
        $result = $this->applicationConfig->setLogFilePath('/tmp/mylog.txt');
        $this->assertInstanceOf(ApplicationConfig::class, $result);

        $result = $this->applicationConfig->getLogFilePath();
        $this->assertEquals('/tmp/mylog.txt', $result);
        $this->assertInternalType('string', $result);
    }
}
