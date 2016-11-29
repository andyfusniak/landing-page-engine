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

    public static function setup()
    {
    }

    public function __construct(Logger $log, array $config)
    {
        $this->log = $log;
        $this->config = $config;
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem($config['themes_root'] . '/' . $config['theme_name'] . '/html');
        $log->debug(sprintf(
            'Setting the Twig Loader filesystem path to %s',
            $config['themes_root'] . '/' . $config['theme_name'] . '/html'
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
