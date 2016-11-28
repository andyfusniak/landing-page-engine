<?php
namespace Gm\LandingPageEngine;

require_once 'vendor/twig/twig/lib/Twig/Autoloader.php';

class LpEngine
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var \Twig_Environment
     */
    private $twigEnv;

    public static function setup()
    {
    }

    public function __construct(array $config)
    {
        $this->config = $config;
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem($config['themes_root'] . '/' . $config['theme_name']);
        $this->twigEnv = new \Twig_Environment($loader, [
            'cache' => $config['cache_root'],
        ]);
    }

    public function run()
    {
        $template = $this->twigEnv->load('html/template.html.twig');
        echo $template->render([
            'title' => 'Example title for our page'
        ]);
    }
}
