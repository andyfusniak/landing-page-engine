<?php
namespace Gm\LandingPageEngine\Service;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Monolog\Logger;

use Gm\LandingPageEngine\Config\ConfigLoader\XmlThemeConfigLoader;
use Gm\LandingPageEngine\Config\ThemeConfig;

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
     * @var array
     */
    protected $config;

    /**
     *
     * @param $config application config
     */
    public function __construct(Logger $logger, $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     *
     * @return ThemeConfig
     */
    public function loadThemeConfig($theme)
    {
        $directories = [
            $this->config['themes_root'] . '/' . $theme
        ];
        $locator = new FileLocator($directories);
        $loader = new XmlThemeConfigLoader($locator);
        $themeDomDoc = $loader->load($locator->locate('theme.xml'));

        return $this->themeConfig = new ThemeConfig(
            $this->logger,
            $themeDomDoc
        );

        // @todo legacy .json file below

        $jsonThemeFilepath = $this->config['themes_root'] . '/' . $theme . '/theme.json';
        $this->logger->debug(sprintf(
            'Attempt to loaded theme configuration "%s"',
            $jsonThemeFilepath
        ));

        $string = file_get_contents($jsonThemeFilepath);
        $json = json_decode($string, true);

        if (null === $json) {
            $this->logger->error(sprintf(
                'The theme JSON file "%s" could not be parsed',
                $jsonThemeFilepath
            ));
            throw new \Exception(sprintf(
                'The theme JSON file "%s" could not be parsed. Err code %s, error message "%s"',
                $jsonThemeFilepath,
                json_last_error(),
                json_last_error_msg()
            ));
        }

        // check the template contains appropriate contents
        if (isset($json['name']) && (isset($json['version']))) {
            $this->logger->info(sprintf(
                'Template "%s" version %s in use."',
                $json['name'],
                $json['version']
            ));
        } else {
            if (!isset($json['name'])) {
                $this->logger->error(sprintf(
                    'Template "%s" has a missing theme name.  Use {"name": "Template name"} section.',
                    $jsonThemeFilepath
                ));
                throw new \Exception(sprintf(
                    'theme.json file "%s" is missing compulsory {"name": "Template name"}.  The \
                    template name is needed as an autocapture field in the database.',
                    $jsonThemeFilepath
                ));
            }

            if (!isset($json['version'])) {
                $this->logger->error(sprintf(
                    'Template "%s" has a missing version.  Use {"version": "x.y.z"} section.',
                    $jsonThemeFilepath
                ));
                throw new \Exception(sprintf(
                    'theme.json file "%s" is missing compulsory {"version": "x.y.z"}.  The \
                    template version is needed as an autocapture field in the database.',
                    $jsonThemeFilepath
                ));
            }
        }

        return $this->themeConfig = new ThemeConfig(
            $this->logger,
            $json,
            ThemeConfig::CONFIG_TYPE_JSON
        );
    }

    public function activateThemes()
    {
        if (isset($this->config['skip_auto_theme_activation']) &&
            (true === $this->config['skip_auto_theme_activation'])) {
            $logger->info('Skipping theme activation checks as skip_auto_theme_activation=true');
            return;
        }

        $hostsConfig = isset($this->config['hosts']) ? $this->config['hosts'] : null;
        if (null === $hostsConfig) {
            throw new \Exception(
                'config.php contains no \'hosts\' configuration.  You must have at least one valid host-to-theme mapping.'
            );
        }

        $publicAssets = $this->config['web_root'] . '/assets';
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
        $publicAssets = $this->config['web_root'] . '/assets';
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

        $publicAssets = $this->config['web_root'] . '/assets';
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
        $link = $this->config['web_root'] . '/assets/' . $name;

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