<?php
namespace Gm\LandingPageEngine;

use Monolog\Logger;

require_once 'vendor/twig/twig/lib/Twig/Autoloader.php';

class LpEngine
{
    /**
     * @var array
     */
    protected $config;
   
    /**
     * @var Logger
     */
    protected $log;
     
    /**
     * @var \Twig_Environment
     */
    protected $twigEnv;

    /**
     * @var array
     */
    protected $themeConfig;

    public static function setup()
    {
    }

    public function __construct(Logger $log, array $config)
    {
        $this->log = $log;
        $this->config = $config;
        \Twig_Autoloader::register();
        $twigTemplateDir = $config['themes_root'] . '/' . $config['theme_name'] . '/html';
        $loader = new \Twig_Loader_Filesystem($twigTemplateDir);
        $log->debug(sprintf(
            'Setting the Twig Loader filesystem path to %s',
            $twigTemplateDir
        ));

        if ((isset($config['developer_mode']))
            && ($config['developer_mode'] === true)) {
            $twigEnvOptions = [
                'debug'       => true,
                'cache'       => false,
                'auto_reload' => true,
            ];
        } else {
            $twigEnvOptions = [
                'cache' => $config['twig_cache_dir'],
            ]; 
        }

        $this->twigEnv = new \Twig_Environment($loader, $twigEnvOptions);
        $this->loadThemeConfig();
    }

    public function loadThemeConfig()
    {
        $jsonThemeFilepath = $this->config['themes_root']
                             . '/' . $this->config['theme_name']
                             . '/theme.json';
        $this->log->debug(sprintf(
            'Loaded JSON theme from %s',
            $jsonThemeFilepath
        ));

        $string = '{"title": "The lord of the rings"}';
        $string = file_get_contents($jsonThemeFilepath);
        $json = json_decode($string, true);

        if (null === $json) {
            $this->log->error(sprintf(
                'The theme JSON file "%s" could not be parsed',
                $jsonThemeFilepath
            ));
            throw new \Exception(sprintf(
                'The theme JSON file "%s" could not be parsed',
                $jsonThemeFilepath
            ));
        }

        // check the template contains appropriate contents
        if (isset($json['name']) && (mb_strlen($json['name']) > 0)) {
            $this->log->info(sprintf(
                'Template "%s" in use."',
                $json['name']
            ));
        } else {
            $this->log->warning(sprintf(
                'Template "%s" has a missing theme name.  Use {"name": "Template name"} to set your theme name.',
                $jsonThemeFilepath
            ));
        }

        if (isset($json['version']) && (mb_strlen($json['version']) > 0)) {
            $this->log->info(sprintf(
                'Template version %s in use.',
                $json['version']
            ));
        } else {
            $this->log->warning(sprintf(
                'Template has no version string set in the theme config ("%s"), so we are running an unknown version of the theme.',
                $jsonThemeFilepath
            ));
        }

        $this->themeConfig = $json;
    }

    /**
     * Return the theme config as an array
     * @return array
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * Returns the Twig Environement instance
     * @return \Twig_Environment
     */
    public function getTwigEnv()
    {
        return $this->twigEnv;
    }

    public function run()
    {
        $template = $this->twigEnv->load('html/template.html.twig');
        echo $template->render([
            'title' => 'Example title for our page',
            'menuitems' => ['pizza', 'lasagna', 'fruit cake', 'donut']
        ]);
    }
}
