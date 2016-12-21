<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Config
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Config;

use Monolog\Logger;

class ApplicationConfig
{
    const DEFAULT_DEVELOPER_MODE          = true;
    const DEFAULT_SKIP_AUTO_VAR_DIR_SETUP = false;
    const DEFAULT_SKIP_THEME_ACTIVATION   = false;
    const DEFAULT_NO_CAPTURE              = false;
    const DEFAULT_LOG_LEVEL               = Logger::DEBUG;

    // reletive paths from the project root dir
    const WEB_ROOT    = 'public';
    const THEMES_ROOT = 'themes';
    const VAR_ROOT    = 'var';

    /**
     * @var bool
     */
    protected $developerMode = self::DEFAULT_DEVELOPER_MODE;

    /**
     * @var bool
     */
    protected $skipAutoVarDirSetup = self::DEFAULT_SKIP_AUTO_VAR_DIR_SETUP;

    /**
     * @var bool
     */
    protected $skipAutoThemeActivation = false;

    /**
     * @var bool
     */
    protected $noCapture = false;

    /**
     * @var string
     */
    protected $projectRoot;

    /**
     * @var string
     */
    protected $webRoot;

    /**
     * @var string
     */
    protected $varDir;

    /**
     * @var string
     */
    protected $logDir;

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

    /**
     * Setup the applicaton config with defaults
     *
     * @param string      $projectRoot project root directory
     * @param string|null $webRoot     web root directory or null for default
     * @param string|null $themesRoot  themes root directory or null for default
     */
    public function __construct($projectRoot, $webRoot = null, $themesRoot = null)
    {
        $this->projectRoot = $projectRoot;

        if (null === $webRoot) {
            $this->webRoot = $projectRoot . '/' . self::WEB_ROOT;
        } else {
            $this->webRoot = (string) $webRoot;
        }

        if (null === $themesRoot) {
            $this->themesRoot = $projectRoot . '/' . self::THEMES_ROOT;
        } else {
            $this->themesRoot = (string) $themesRoot;
        }

        $this->varDir = $projectRoot . '/' . self::VAR_ROOT;
        $this->twigCacheDir = $this->varDir . '/twig_cache';

        $this->logDir = $this->varDir . '/log';
        $this->logFilePath  = $this->logDir . '/lpengine.log';
        $this->logLevel = self::DEFAULT_LOG_LEVEL;
    }

    /**
     * When developer mode is set to true, Twig caching will be disabled,
     * twig debug will be enabled and auto_reload will be enabled.  These
     * are convenient features whilst developing.
     *
     * Remember to switch developer_mode to false before going live.
     * Once developer mode is disabled (false), Twig templates will be
     * compiled to PHP and stored in the twig_cache_root directory
     * (see below).
     *
     * @param bool $value
     * @throws \InvalidArgumentException
     * @return ApplicationConfig
     */
    public function setDeveloperMode($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value %s passed',
                $value
            ));
        }
        $this->developerMode = $value;
        return $this;
    }

    /**
     * Get the application developerMode config
     *
     * @return bool
     */
    public function getDeveloperMode()
    {
        return $this->developerMode;
    }

    /**
     * If true, the file system checks for var/log, var/twig_cache will
     * be skipped.  This greatly improves performance and should be set
     * to true for production environment
     *
     * @param bool $value
     * @throws \InvalidArgumentException
     * @return ApplicationConfig
     */
    public function setSkipAutoVarDirSetup($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value %s passed',
                $value
            ));
        }
        $this->skipAutoVarDirSetup = $value;
        return $this;
    }

    /**
     * Get the application skipAutoVarDirSetup config
     *
     * @return bool
     */
    public function getSkipAutoVarDirSetup()
    {
        return $this->skipAutoVarDirSetup;
    }

    /**
     * If true, the application will skip automatic theme activation.
     * This improves performance and should be set to true for production
     * environments.  With no automatic theme activation, you should
     * use set up the symbolic links manually (see the LPE Developer Guide)
     *
     * @param bool $value true or false
     * @return ApplicationConfig
     */
    public function setSkipAutoThemeActivation($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value %s passed',
                $value
            ));
        }
        $this->skipAutoThemeActivation = $value;
        return $this;
    }

    /**
     * Get the application skipAutoThemeActivation config
     *
     * @return bool
     */
    public function getSkipAutoThemeActivation()
    {
        return $this->skipAutoThemeActivation;
    }

    /**
     * If true, the application will not attempt to capture any data
     * to the database.  Useful for developers who do not have access to
     * a local database or one on their LAN.
     *
     * If developer_mode is false, this option will be disregarded.  This
     * prevents production servers from not capturing.
     *
     * @param bool $value true or false
     * @return ApplicationConfig
     */
    public function setNoCapture($value)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value %s passed',
                $value
            ));
        }
        $this->noCapture = $value;
        return $this;
    }

    /**
     * Get the application noCapture config
     *
     * @return bool
     */
    public function getNoCapture()
    {
        return $this->noCapture;
    }

    /**
     * Get the full path to the project root
     *
     * @return string project root directory
     */
    public function getProjectRoot()
    {
        return $this->projectRoot;
    }

    /**
     * Get the var root dir path
     *
     * @return string var root directory
     */
    public function getVarDir()
    {
        return $this->varDir;
    }

    /**
     * Get the log root dir path
     *
     * @return string log root directory
     */
    public function getLogDir()
    {
        return $this->logDir;
    }

    /**
     * Get the full path to the web root
     *
     * @return string web root directory
     */
    public function getWebRoot()
    {
        return $this->webRoot;
    }

    /**
     * Get the full path to the themes root
     *
     * @return string themes root directory
     */
    public function getThemesRoot()
    {
        return $this->themesRoot;
    }

    /**
     * Set the twig cache directory
     *
     * @param string $value
     * @return ApplicationConfig
     */
    public function setTwigCacheDir($value)
    {
        $this->twigCacheDir = (string) $value;
        return $this;
    }

    /**
     * Get the twig cache directory
     *
     * @return string twig cache dir
     */
    public function getTwigCacheDir()
    {
        return $this->twigCacheDir;
    }

    /**
     * Set the log file path
     *
     * @param string $value the log fullpath
     * @return ApplicationConfig
     */
    public function setLogFilePath($logFilePath)
    {
        $this->logFilePath = (string) $logFilePath;
        return $this;
    }

    /**
     * Get the log filepath
     *
     * @return string lpengine log full file path
     */
    public function getLogFilePath()
    {
        return $this->logFilePath;
    }

    /**
     * 0  Emergency         Logger::EMERG
     * 1  Alert             Logger::ALERT
     * 2  Critical          Logger::CRITICAL
     * 3  Error             Logger::ERROR
     * 4  Warning           Logger::WARNING
     * 5  Notice            Logger::NOTICE
     * 6  Informational     Logger::INFO
     * 7  Debug             Logger::DEBUG
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = (int) $logLevel;
        return $this;
    }

    /**
     * Get the application config log level
     *
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }
}

