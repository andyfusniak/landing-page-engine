<?php declare(strict_types=1);
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Entity
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Entity;

class AppProfile
{
    const DEFAULT_VALUE = '@default';

    /**
     * @var string
     */
    protected $connectionProfile;

    /**
     * @var bool
     */
    protected $developerMode;

    /**
     * @var bool
     */
    protected $skipAutoVarDirSetup;

    /**
     * @var bool
     */
    protected $skipAutoThemeActivation;

    /**
     * @var bool
     */
    protected $noCapture;

    /**
     * @var bool
     */
    protected $projectRoot;

    /**
     * @var string
     */
    protected $webRoot;

    /**
     * @var string
     */
    protected $themesRoot;

    /**
     * @var string
     */
    protected $twigCacheDir;

    /**
     * @var string
     */
    protected $logFilePath;

    /**
     * @var int
     */
    protected $logLevel;

    public function setConnectionProfile(string $value) : AppProfile
    {
        $this->connectionProfile = $value;
        return $this;
    }

    public function getConnectionProfile()
    {
        return $this->connectionProfile;
    }

    public function setDeveloperMode(bool $value) : AppProfile
    {
        $this->developerMode = $value;
        return $this;
    }

    public function getDeveloperMode()
    {
        return $this->developerMode;
    }

    public function setSkipAutoVarDirSetup(bool $value) : AppProfile
    {
        $this->skipAutoVarDirSetup = $value;;
        return $this;
    }

    public function getSkipAutoVarDirSetup()
    {
        return $this->skipAutoVarDirSetup;
    }

    public function setSkipAutoThemeActivation(bool $value) : AppProfile
    {
        $this->skipAutoThemeActivation = $value;
        return $this;
    }

    public function getSkipAutoThemeActivation()
    {
        return $this->skipAutoThemeActivation;
    }

    /**
     * @param bool|null $value
     */
    public function setNoCapture($value) : AppProfile
    {
        $this->noCapture = $value;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getNoCapture()
    {
        return $this->noCapture;
    }

    public function setProjectRoot(string $value) : AppProfile
    {
        $this->projectRoot = $value;
        return $this;
    }

    public function getProjectRoot()
    {
        return $this->projectRoot;
    }

    public function setWebRoot(string $value) : AppProfile
    {
        $this->webRoot = $value;
        return $this;
    }

    public function getWebRoot()
    {
        return $this->webRoot;
    }

    public function setThemesRoot(string $value) : AppProfile
    {
        $this->themesRoot = $value;
        return $this;
    }

    public function getThemesRoot()
    {
        return $this->themesRoot;
    }

    public function setTwigCacheDir(string $value) : AppProfile
    {
        $this->twigCacheDir = $value;
        return $this;
    }

    public function getTwigCacheDir()
    {
        return $this->twigCacheDir;
    }

    public function setLogFilePath(string $value) : AppProfile
    {
        $this->logFilePath = $value;
        return $this;
    }

    public function getLogFilePath()
    {
        return $this->logFilePath;
    }

    public function setLogLevel(int $value) : AppProfile
    {
        $this->logLevel = $value;
        return $this;
    }

    public function getLogLevel()
    {
        return $this->logLevel;
    }
}
