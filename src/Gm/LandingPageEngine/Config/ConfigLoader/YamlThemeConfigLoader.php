<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Config\ConfigLoader
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Config\ConfigLoader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlThemeConfigLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        try {
            $themeConfig = Yaml::parse(file_get_contents($resource));
        } catch (\Exception $e) {
            throw $e;
        }

        return $themeConfig;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}
