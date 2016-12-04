<?php
/**
 * Landing Page Engine
 *
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace GmTest;

chdir(__DIR__);
require_once __DIR__ . '/../vendor/autoload.php';

class Bootstrap
{
    public static function init()
    {
        /*
         * Set error reporting to the level to which Zend Framework code must comply.
         */
        error_reporting( E_ALL | E_STRICT );
    }
}

Bootstrap::init();
