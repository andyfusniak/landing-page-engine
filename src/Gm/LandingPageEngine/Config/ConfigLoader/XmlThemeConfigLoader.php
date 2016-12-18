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

use Symfony\Component\Config\Util\XmlUtils;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class XmlThemeConfigLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        try {
            $dom = XmlUtils::loadFile($resource);
        } catch (\Exception $e) {
            throw $e;
        }

        return $dom;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}
