<?php
namespace Gm\LandingPageEngine\Service;

use Gm\LandingPageEngine\Mapper\TableMapper;
use Gm\LandingPageEngine\LpEngine;
use Gm\LandingPageEngine\Config\ApplicationConfig;
use Gm\LandingPageEngine\Config\DeveloperConfig;
use Gm\LandingPageEngine\Version\Version;

use Gm\LandingPageEngine\Service\Exception\ThemeConfigFileNotFound;

use Monolog\Logger;

class StatusService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var LpEngine
     */
    protected $lpEngine;

    /**
     * @var ApplicationConfig
     */
    protected $applicationConfig;

    /**
     * @var DeveloperConfig
     */
    protected $developerConfig;

    /**
     * @var TableMapper
     */
    protected $tableMapper;

    public function __construct(LpEngine $lpEngine)
    {
        $this->lpEngine          = $lpEngine;
        $this->logger            = $lpEngine->getLogger();
        $this->applicationConfig = $lpEngine->getApplicationConfig();
        $this->developerConfig   = $lpEngine->getDeveloperConfig();
    }

    public function systemSettings()
    {
        // PHP Version
        $this->lpEngine->addTwigGlobal(
            'php_version',
            phpversion()
        );

        // PHP extensions loaded
        $phpExtensions = [
            'pdo_mysql' => (true === extension_loaded('pdo_mysql')) ? 1 : 0,
            'intl'      => (true === extension_loaded('intl')) ? 1 : 0,
            'mbstring'  => (true === extension_loaded('mbstring')) ? 1 : 0,
            'curl'      => (true === extension_loaded('curl')) ? 1 : 0,
        ];

        $this->lpEngine->addTwigGlobal(
            'php_extensions',
            $phpExtensions
        );

        // Disk usage stats
        $freeSpace  = disk_free_space('/');
        $totalSpace = disk_total_space('/');

        $this->lpEngine->addTwigGlobal(
            'disk_free',
            $this->formatBytes($freeSpace)
        );

        $this->lpEngine->addTwigGlobal(
            'disk_total',
            $this->formatBytes($totalSpace)
        );

        $this->lpEngine->addTwigGlobal(
            'disk_used',
            $this->formatBytes($totalSpace - $freeSpace)
        );

        $this->lpEngine->addTwigGlobal(
            'disk_percentage_used',
            round(
                (($totalSpace - $freeSpace) / $totalSpace) * 100.00
            )
        );
    }

    public function landingPageEngine()
    {
        $appProfile = $this->developerConfig->getAppProfile();
        $lpeSettings = [
            'lpe_version'           => Version::VERSION,
            'lpe_release_date'      => Version::RELEASE_DATE,

            'lpe_app_project_root'  => $this->applicationConfig->getProjectRoot(),
            'lpe_dev_project_root'  =>
                (null !== $appProfile->getProjectRoot())
                    ? $appProfile->getProjectRoot() : '@default',

            'lpe_app_web_root' => $this->applicationConfig->getWebRoot(),
            'lpe_dev_web_root' =>
                (null !== $appProfile->getWebRoot())
                    ? $appProfile->getWebRoot() : '@default',

            'lpe_app_log_file_path' => $this->applicationConfig->getLogFilePath(),
            'lpe_dev_log_file_path' =>
                (null !== $appProfile->getLogFilePath())
                    ? $appProfile->getLogFilePath() : '@default',

            'lpe_app_log_level' =>
                $this->logger->getLevelName($this->applicationConfig->getLogLevel()),
            'lpe_dev_log_level' =>
                (null !== $appProfile->getLogLevel())
                    ? $this->logger->getLevelName($appProfile->getLogLevel()) : '@default',

            'lpe_app_developer_mode' =>
                (true === $this->applicationConfig->getDeveloperMode()) ? 'True' : 'False',
            'lpe_app_skip_auto_var_dir_setup' =>
                (true === $this->applicationConfig->getSkipAutoVarDirSetup()) ? 'True' : 'False',
            'lpe_app_skip_auto_theme_activation' =>
                (true === $this->applicationConfig->getSkipAutoThemeActivation()) ? 'True' : 'False',
            'lpe_app_no_capture' =>
                (true === $this->applicationConfig->getNoCapture()) ? 'True' : 'False'
        ];

        foreach ($lpeSettings as $name => $value) {
            $this->lpEngine->addTwigGlobal($name, $value);
        }
    }

    public function databaseSettings()
    {
        // database host, user and name
        foreach (['dbhost', 'dbuser', 'dbname'] as $key) {
            $this->lpEngine->addTwigGlobal(
                $key,
                isset($this->config['db'][$key])
                    ? $this->config['db'][$key] : null
            );
        }

        // database connection status

        if ((true === $this->applicationConfig->getDeveloperMode()) &&
            (true === $this->applicationConfig->getNoCapture())) {
            $this->lpEngine->addTwigGlobal('capturing_data', 1);
        } else {
            $this->lpEngine->addTwigGlobal('capturing_data', 0);
        }

        try {
            $pdo = $this->lpEngine->getPdoService()->getPdoObject();
            if ($pdo instanceof \PDO) {
                $this->lpEngine->addTwigGlobal('has_database_connection', 1);
            } else {
                $this->lpEngine->addTwigGlobal('has_database_connection', 0);
            }
        } catch (\Exception $e) {
            $this->lpEngine->addTwigGlobal('has_database_connection', 0);
            //throw $e;
        }
    }

    public function themeSettings()
    {
        $availableThemeDirs = $this->listOfDirs(
            $this->applicationConfig->getThemesRoot()
        );

        $activeThemeDirs = $this->listOfDirs(
            $this->applicationConfig->getWebRoot() . '/assets'
        );


        $themeConfigService = $this->lpEngine->getThemeConfigService();
        $themeSummary = [];
        foreach ($availableThemeDirs as $availableTheme) {
            try {
                $themeConfig = $themeConfigService->loadThemeConfig($availableTheme);
            } catch (ThemeConfigFileNotFound $e) {
                $themeConfig = null;
            }

            if (in_array($availableTheme, $activeThemeDirs)) {
                $themeSummary[$availableTheme] = [
                    'status' => 'Enabled',
                ];
            } else {
                $themeSummary[$availableTheme] = [
                    'status' => 'Disabled'
                ];
            }

            if (null === $themeConfig) {
                $themeSummary[$availableTheme]['name'] = 'theme.xml';
                $themeSummary[$availableTheme]['version'] = 'theme.xml';
            } else {
                $themeSummary[$availableTheme]['name']
                    = $themeConfig->getThemeName();
                $themeSummary[$availableTheme]['version']
                    = $themeConfig->getThemeVersion();
            }
        }

        $this->lpEngine->addTwigGlobal(
            'theme_summary',
            $themeSummary
        );
    }

    public function getTableMapper()
    {
        if (null === $this->tableMapper) {
            $pdo = $this->lpEngine->getPdoService()->getPdoObject();
            $this->tableMapper = new TableMapper($this->logger, $pdo);
        }
        return $this->tableMapper;
    }

    /**
     * For memory use oneK = 1024 (2^10 two to the power of ten)
     * Disk usage is typically measures in KB not KiB
     *
     * @return string the number of bytes as a human readable string
     */
    private function formatBytes($size, $precision = 2, $oneK = 1000)
    {
        $base = log($size, $oneK);
        if ($oneK === 1024) {
            $suffixes = ['', 'KiB', 'MiB', 'GiB', 'TiB'];
        } else {
            $suffixes = ['', 'KB', 'MB', 'GB', 'TB'];
        }

        return round(pow(
                    $oneK,
                    $base - floor($base)),
                    $precision
                )
                . ' '
                . $suffixes[floor($base)];
    }

    private function listOfDirs($dirRoot)
    {
        if (false === file_exists($dirRoot)) {
            return null;
        }

        if (false === is_dir($dirRoot)) {
            return null;
        }

        $scan = scandir($dirRoot, SCANDIR_SORT_NONE);
        $dirs = [];
        foreach ($scan as $fileOrDir) {
            if (in_array($fileOrDir, ['.', '..'])) {
                continue;
            }
            if (is_dir($this->applicationConfig->getThemesRoot() . '/' . $fileOrDir)) {
                $dirs[] = $fileOrDir;
            }
        }
        return $dirs;
    }
}
