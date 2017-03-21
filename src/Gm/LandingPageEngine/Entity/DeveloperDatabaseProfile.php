<?php
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

class DeveloperDatabaseProfile
{
    /**
     * @var string
     */
    protected $profileName;

    /**
     * @var string
     */
    protected $dbHost;

    /**
     * @var string
     */
    protected $dbUser;

    /**
     * @var string
     */
    protected $dbPass;

    /**
     * @var string
     */
    protected $dbName;

    /**
     * @var string
     */
    protected $dbTable;

    public function __construct(string $profileName,
                                string $dbHost,
                                string $dbUser,
                                string $dbPass,
                                string $dbName,
                                string $dbTable)
    {
        $this->profileName = $profileName;
        $this->dbHost  = $dbHost;
        $this->dbUser  = $dbUser;
        $this->dbPass  = $dbPass;
        $this->dbName  = $dbName;
        $this->dbTable = $dbTable;
    }

    /**
     * @return string profile name
     */
    public function getProfileName()
    {
        return $this->profileName;
    }

    /**
     * @return string database host
     */
    public function getDbHost()
    {
        return $this->dbHost;
    }

    /**
     * @return string database user
     */
    public function getDbUser()
    {
        return $this->dbUser;
    }

    /**
     * @return string database password
     */
    public function getDbPass()
    {
        return $this->dbPass;
    }

    /**
     * @return string database name
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * @return string table name
     */
    public function getDbTable()
    {
        return $this->dbTable;
    }
}
