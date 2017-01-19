<?php
namespace Gm\LandingPageEngine\Service;

use PDO;
use PDOException;
use Monolog\Logger;
use Gm\LandingPageEngine\Config\DeveloperConfig;

class PdoService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var DeveloperConfig
     */
    protected $developerConfig;

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(Logger $logger, DeveloperConfig $developerConfig)
    {
        $this->logger = $logger;
        $this->developerConfig = $developerConfig;
    }

    public function getPdoObject(string $host) {

        if (null !== $this->pdo) {
            return $this-pdo;
        }

        $databaseProfile = $this->developerConfig
                                ->getActiveProfileByDomain($host)
                                ->getActiveDeveloperDatabaseProfile();

        try {
            $dsn = 'mysql:host=' . $databaseProfile->getDbHost() . ';dbname=' .
                    $databaseProfile->getDbName();
            $user = $databaseProfile->getDbUser();

            $this->logger->debug(sprintf(
                'Data Source Name = %s, user = %s',
                $dsn,
                $user
            ));

            $this->pdo = new PDO(
                $dsn,
                $user,
                $databaseProfile->getDbPass(),
                [
                    PDO::ATTR_TIMEOUT => 4,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ]
            );

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->pdo;
    }
}
