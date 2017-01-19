<?php declare(strict_types=1);
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Entity
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
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

    public function __construct(string $routeName,
                                int $stage,
                                string $url,
                                string $target,
                                string $resourcePrefix = '')
    {
        $this->routeName       = $routeName;
        $this->stage           = $stage;
        $this->url             = $url;
        $this->target          = $target;
        $this->resourcePrefix  = $resourcePrefix;
    }

    /**
     * Get the route name
     *
     * @return string route name
     */
    public function getRouteName() : string
    {
        return $this->routeName;
    }

    /**
     * Get the stage of the route in the funnel
     *
     * @return int stage in funnel
     */
    public function getStage() : int
    {
        return $this->stage;
    }

    /**
     * Get the URL
     *
     * @return string the URL
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * Get the target
     */
    public function getTarget() : string
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
    public function getResourcePrefix() : string
    {
        return $this->resourcePrefix;
    }

    /**
     * Get full URL including prefix
     *
     * @return string the URL complete with prefix
     */
    public function getUrlWithPrefix() : string
    {
        if (substr($this->url, 0, 1) === '/') {
            return '/' . $this->resourcePrefix . ltrim($this->url, '/');
        }
        return $this->resourcePrefix . $this->url;
    }
}