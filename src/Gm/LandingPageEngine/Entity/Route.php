<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Entity
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 */
namespace Gm\LandingPageEngine\Entity;

class Route
{
    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var int
     */
    protected $stage;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var string
     */
    protected $resourcePrefix;

    public function __construct($routeName, $stage, $url, $target, $resourcePrefix = '')
    {
        $this->routeName = $routeName;
        $this->stage = (int) $stage;
        $this->url = $url;
        $this->target = $target;
        $this->resourcePrefix = $resourcePrefix;
    }

    /**
     * Get the route name
     *
     * @return string route name
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Get the stage of the route in the funnel
     *
     * @return int stage in funnel
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Get the URL
     *
     * @return string the URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Is the target an redirect
     *
     * @return bool true if a redirect, otherwise false
     */
    public function isTargetRedirect()
    {
        if (('/' === substr($this->target, 0, 1)) ||
            ('http' === substr($this->target, 0, 4))) {
            return true;
        }
        return false;
    }

    /**
     * Get the resource prefix
     */
    public function getResourcePrefix()
    {
        return $this->resourcePrefix;
    }

    /**
     * Get full URL including prefix
     *
     * @return string the URL complete with prefix
     */
    public function getUrlWithPrefix()
    {
        if (substr($this->url, 0, 1) === '/') {
            return '/' . $this->resourcePrefix . ltrim($this->url, '/');
        }
        return $this->resourcePrefix . $this->url;
    }
}
