<?php
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

    public function setDeveloperMode(bool $value)
    {
        $this->developerMode = $value;
        return $this;
    }

    public function getDeveloperMode()
    {
        return $this->developerMode;
    }

    public function setSkipAutoVarDirSetup(bool $value)
    {
        $this->skipAutoVarDirSetup = $value;;
        return $this;
    }

    public function getSkipAutoVarDirSetup()
    {
        return $this->skipAutoVarDirSetup;
    }

    public function setSkipAutoThemeActivation(bool $value)
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
    public function setNoCapture($value)
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

    public function setProjectRoot($value)
    {
        $this->projectRoot = $value;
        return $this;
    }

    public function getProjectRoot()
    {
        return $this->projectRoot;
    }

    public function setWebRoot($value)
    {
        $this->webRoot = $value;
        return $this;
    }

    public function getWebRoot()
    {
        return $this->webRoot;
    }

    public function setThemesRoot($value)
    {
        $this->themesRoot = $value;
        return $this;
    }

    public function getThemesRoot()
    {
        return $this->themesRoot;
    }

    public function setTwigCacheDir($value)
    {
        $this->twigCacheDir = $value;
        return $this;
    }

    public function getTwigCacheDir()
    {
        return $this->twigCacheDir;
    }

    public function setLogFilePath($value)
    {
        $this->logFilePath = $value;
        return $this;
    }

    public function getLogFilePath()
    {
        return $this->logFilePath;
    }

    public function setLogLevel($value)
    {
        $this->logLevel = (int) $value;
        return $this;
    }

    public function getLogLevel()
    {
        return $this->logLevel;
    }
}
