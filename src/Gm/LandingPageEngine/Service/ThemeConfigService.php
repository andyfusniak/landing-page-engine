<?php
namespace Gm\LandingPageEngine\Service;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Monolog\Logger;

use Gm\LandingPageEngine\Config\ApplicationConfig;
use Gm\LandingPageEngine\Config\ConfigLoader\XmlThemeConfigLoader;
use Gm\LandingPageEngine\Config\ThemeConfig;
use Gm\LandingPageEngine\Service\Exception\ThemeConfigFileNotFound;

class ThemeConfigService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ThemeConfig
     */
    protected $themeConfig;

    /**
     * @var ApplicationConfig
     */
    protected $applicationConfig;

    /**
     *
     * @param $config application config
     */
    public function __construct(Logger $logger, $applicationConfig)
    {
        $this->logger = $logger;
        $this->applicationConfig = $applicationConfig;
    }

    /**
     *
     * @return ThemeConfig
     */
    public function loadThemeConfig($theme)
    {
        $themesRoot = $this->applicationConfig->getThemesRoot();
        $directories = [
            $themesRoot . '/' . $theme
        ];

        $locator = new FileLocator($directories);
        $loader = new XmlThemeConfigLoader($locator);

        try {
            $themeDomDoc = $loader->load($locator->locate('theme.xml'));
        } catch (FileLocatorFileNotFoundException $e) {
            throw new ThemeConfigFileNotFound($e);
        }

        return $this->themeConfig = new ThemeConfig(
            $this->logger,
            $themeDomDoc
        );
    }

    public function activateThemes($config)
    {
        if (true === $this->applicationConfig->getSkipAutoThemeActivation()) {
            $logger->info('Skipping theme activation checks as skip_auto_theme_activation=true');
            return;
        }

        $hostsConfig = isset($config['hosts']) ? $config['hosts'] : null;
        if (null === $hostsConfig) {
            throw new \Exception(
                'config.php contains no \'hosts\' configuration.  You must have at least one valid host-to-theme mapping.'
            );
        }

        $publicAssets = $this->applicationConfig->getWebRoot() . '/assets';
        if (!is_dir($publicAssets)) {
            throw new \Exception(sprintf(
                '%s directory does not exist.  You should create this directory and make it writeable by the web server',
                $publicAssets
            ));
        }

        if (!is_writeable($publicAssets)) {
            throw new \Exception(sprintf(
                '%s directory is not writeable by the web server.  Change the permissions using chmod g+w,o+w %s',
                $publicAssets,
                $publicAssets
            ));
        }

        // iterate the list of hosts in their natural order
        // and check the theme for each is activated
        $hosts = array_keys($hostsConfig);
        natsort($hosts);

        // remove duplicates
        $themesToActivate = [];
        foreach ($hosts as $host) {
            $theme = $hostsConfig[$host];
            $parts = explode(':', $theme);
            $theme = $parts[0];

            if (!in_array($theme, $themesToActivate)) {
                array_push($themesToActivate, $theme);
            }
        }

        // deactivate any themes that aren't going to be in use
        // On a broken symlink is_link() returns true and file_exists() returns false.
        foreach (scandir($publicAssets) as $entry) {
            if (('.' === $entry) || ('..' === $entry)) {
                continue;
            }

            // check if the current symlink in the public/assets directory
            // is still in use.  If not, remove it
            if (!in_array($entry, $themesToActivate)) {
                $this->logger->debug(sprintf(
                    '%s link found in %s directory',
                    $entry,
                    $publicAssets
                ));
                $fullpath = $publicAssets . '/' . $entry;

                if (false === unlink($fullpath)) {
                    $logger->info(sprintf(
                        'Failed to unlink "%s".  This theme needs to be deactivted as is no longer in use.',
                        $fullpath
                    ));
                } else {
                    $this->logger->debug(sprintf(
                        'unlink %s',
                        $fullpath
                    ));
                    $this->logger->info(sprintf(
                        'Deactivating theme "%s" from the web root.',
                        $entry
                    ));
                }
            }
        }

        // activate each theme
        foreach ($themesToActivate as $name) {
            $this->activateTheme($name);
        }
    }

    public function activateTheme($name)
    {
        if (!preg_match('/^[a-z0-9\-]+$/', $name)) {
            throw new \InvalidArgumentException(sprintf(
                '%s called for theme %s.  Theme names must use lower case a-z only, including the digits 0-9 and the hyphen character.',
                __METHOD__,
                $name
            ));
        }

        $webRoot = $this->applicationConfig->getWebRoot();
        $publicAssets = $webRoot . '/assets';
        if (!is_dir($publicAssets)) {
            throw new \Exception(sprintf(
                '%s directory does not exist.  You should create this directory and make it writeable by the web server',
                $publicAssets
            ));
        }

        if (!is_writeable($publicAssets)) {
            throw new \Exception(sprintf(
                '%s directory is not writeable by the web server.  Change the permissions using chmod g+w,o+w %s',
                $publicAssets,
                $publicAssets
            ));
        }

        $target = '../../themes/' . $name . '/assets/' . $name;
        $link = $webRoot . '/assets/' . $name;

        if (!is_link($link)) {
            if (!@symlink($target, $link)) {
                throw new \Exception(sprintf(
                    'Failed to symlink %s to %s.  Make sure %s is writeable by the web server',
                    $target,
                    $link,
                    $publicAssets
                ));
            }
        }
    }

    /**
     * Return the theme config as an array
     * @return ThemeConfig
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }
}