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

class HostProfile
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $theme;

    /**
     * @var string
     */
    protected $profile;

    public function __construct(string $domain, string $theme, string $profile)
    {
        $this->domain  = $domain;
        $this->theme   = $theme;
        $this->profile = $profile;
    }

    /**
     * Get the domain
     *
     * @return string domain
     */
    public function getDomain() : string
    {
        return $this->domain;
    }

    /**
     * Get the theme
     *
     * @return string theme name
     */
    public function getThemeName() : string
    {
        return $this->theme;
    }

    /**
     * Get the profile name
     *
     * @return string profile name
     */
    public function getProfile() : string
    {
        return $this->profile;
    }
}
