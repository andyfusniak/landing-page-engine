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

class DeveloperProfile
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var DeveloperDatabaseProfile
     */
    protected $developerDatabaseProfile;

    /**
     * @var array
     */
    protected $feeds;

    /**
     * @var array
     */
    protected $themeSettings;

    public function __construct($name,
                                DeveloperDatabaseProfile $developerDatabaseProfile,
                                array $feeds,
                                array $themeSettings)
    {
        $this->name = $name;
        $this->developerDatabaseProfile = $developerDatabaseProfile;
        $this->feeds = $feeds;
        $this->themeSettings = $themeSettings;
    }

    /**
     * Get the profile name
     *
     * @return string the profile name
     */
    public function getName()
    {
        return $this->name;
    }

    public function getActiveDeveloperDatabaseProfile()
    {
        return $this->developerDatabaseProfile;
    }

    public function getFeeds()
    {
        return $this->feeds;
    }

    public function getThemeSettings()
    {
        return $this->themeSettings;
    }
}
