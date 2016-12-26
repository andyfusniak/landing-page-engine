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
    protected $logFullpath;

    /**
     * @var int
     */
    protected $logLevel;

    public function setConnectionProfile(string $value) : AppProfile
    {
        $this->connectionProfile = $value;
        return $this;
    }

    public function getConnectionProfile() : string
    {
        return $this->connectionProfile;
    }

    public function setDeveloperMode(bool $value) : AppProfile
    {
        $this->developerMode = $value;
        return $this;
    }

    public function getDeveloperMode() : bool
    {
        return $this->developerMode;
    }

    public function setSkipAutoVarDirSetup(bool $value) : AppProfile
    {
        $this->skipAutoVarDirSetup = $value;;
        return $this;
    }

    public function getSkipAutoVarDirSetup() : bool
    {
        return $this->skipAutoVarDirSetup;
    }

    public function setSkipAutoThemeActivation(bool $value) : AppProfile
    {
        $this->skipAutoThemeActivation = $value;
        return $this;
    }

    public function getSkipAutoThemeActivation() : bool
    {
        return $this->skipAutoThemeActivation;
    }

    public function setNoCapture(bool $value) : AppProfile
    {
        $this->noCapture = $value;
        return $this;
    }

    public function getNoCapture() : string
    {
        return $this->noCapture;
    }

    public function setProjectRoot(string $value) : AppProfile
    {
        $this->projectRoot = $value;
        return $this;
    }

    public function getProjectRoot() : string
    {
        return $this->projectRoot;
    }

    public function setWebRoot(string $value) : AppProfile
    {
        $this->webRoot = $value;
        return $this;
    }

    public function getWebRoot() : string
    {
        return $this->webRoot;
    }

    public function setThemesRoot(string $value) : AppProfile
    {
        $this->themesRoot = $value;
        return $this;
    }

    public function getThemesRoot() : string
    {
        return $this->themesRoot;
    }

    public function setTwigCacheDir(string $value) : AppProfile
    {
        $this->twigCacheDir = $value;
        return $this;
    }

    public function getTwigCacheDir() : string
    {
        return $this->twigCacheDir;
    }

    public function setLogFullpath(string $value) : AppProfile
    {
        $this->logFullpath = $value;
        return $this;
    }

    public function getLogFullpath() : string
    {
        return $this->logFullpath;
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
