<?php
namespace Gm\LandingPageEngine\Service;

use PDO;
use PDOException;

use Monolog\Logger;
use Gm\LandingPageEngine\Entity\DeveloperProfile;
use Gm\LandingPageEngine\Mapper\TableMapper;

class CronService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TableMapper
     */
    protected $tableMapper;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function setTableMapper(TableMapper $tableMapper)
    {
        $this->tableMapper = $tableMapper;
    }

    public function fetchUnsyncedRows(DeveloperProfile $developerProfile)
    {
        $databaseProfile = $developerProfile->getActiveDeveloperDatabaseProfile();
        $dsn = 'mysql:host=' . $databaseProfile->getDbHost() . ';dbname=' .
                $databaseProfile->getDbName();
        $user = $databaseProfile->getDbUser();

        try {
            $pdo = new PDO(
                $dsn,
                $user,
                $databaseProfile->getDbPass(),
                [
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ]
            );


            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->setTableMapper(new TableMapper($this->logger, $pdo));

            $rows = $this->tableMapper->fetchUnsyncedRows(
                $databaseProfile->getDbTable()
            );
            var_dump($rows);
            die('b');
        } catch (PDOException $e) {
            switch (strval($e->getCode())) {
            case '2002':
                $this->logger->critical(sprintf(
                    'LPE Cron Failed to connect to database dbhost=%s, dbuser=%s,
                    dbname=%s, dbtable=%s',
                    $databaseProfile->getDbHost(),
                    $user,
                    $databaseProfile->getDbName(),
                    $databaseProfile->getDbTable()
                ));
                $this->logger->debug($e->getMessage());
                break;
            case '42S22':
                $this->logger->critical(sprintf(
                    'LPE Cron Failed to sync Klaviyo contacts because of missing column'
                ));
                $this->logger->debug(sprintf(
                    'LPE Cron Failed to connect to database dbhost=%s, dbuser=%s,
                    dbname=%s, dbtable=%s',
                    $databaseProfile->getDbHost(),
                    $user,
                    $databaseProfile->getDbName(),
                    $databaseProfile->getDbTable()
                ));
                $this->logger->debug($e->getMessage());
                break;
            }
            default:
                throw $e;
        }
    }
}
