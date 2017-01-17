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

    public function __construct(string $name,
                                DeveloperDatabaseProfile $developerDatabaseProfile)
    {
        $this->name = $name;
        $this->developerDatabaseProfile = $developerDatabaseProfile;
    }

    /**
     * Get the profile name
     *
     * @return string the profile name
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function getActiveDeveloperDatabaseProfile() : DeveloperDatabaseProfile
    {
        return $this->developerDatabaseProfile;
    }
}
