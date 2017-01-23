<?php
namespace Gm\LandingPageEngine\Service;

use PDO;
use PDOException;
use GuzzleHttp\Client;

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
                $dbTable = $databaseProfile->getDbTable()
            );
            $this->logger->info(sprintf(
                'Fetched %s row(s) from %s',
                count($rows),
                $databaseProfile->getDbTable()
            ));


            $feedsKlaviyo = $developerProfile->getFeeds()['klaviyo'];

            $this->feedKlaviyo(
                $feedsKlaviyo['api-key'],
                $feedsKlaviyo['list'],
                $feedsKlaviyo['map'],
                $dbTable,
                $rows
            );
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
            default:
                throw $e;
            }
        }
    }

    /**
     * Push the data to the Klaviyo API
     *
     * POST https://a.klaviyo.com/api/v1/list/{{ LIST_ID }}/members
     * POST https://a.klaviyo.com/api/v1/list/BvTacm/members/
     * https://www.klaviyo.com/docs/api/lists
     *
     * @param string $apiKey the Klaviyo API key
     * @param string $list list ID
     * @param array $row associative array of contacts to push
     */
    private function feedKlaviyo(string $apiKey, string $list,
                                 array $map, string $dbTable, array $rows)
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://a.klaviyo.com/api/v1/list/',
            // You can set any number of default request options.
            'timeout'  => 3.0,
        ]);

        foreach ($rows as $row) {
            $email = $row['email'];
            unset($row['email']);
            $klaviyoData = $this->mapDataFields($map, $row);
            $klaviyoJson = json_encode($klaviyoData);

            $response = $client->post(
                '' . $list . '/members',
                [
                    'headers' => [
                        'Accept' => 'application/json'
                    ],
                    'form_params' => [
                        'api_key' => $apiKey,
                        'confirm_optin' => 'false',
                        'email' => $email,
                        'properties' => $klaviyoJson
                    ]
                ]
            );
            $this->logger->debug(sprintf(
                'HTTP POST to Klaviyo API with email=%s and properties=%s',
                $email,
                $klaviyoJson
            ));

            $statusCode = $response->getStatusCode();
            switch ($statusCode) {
            case 200:
                $this->syncKlaviyo($dbTable, (int) $klaviyoData['id'], 1);
                break;
            default:
                $this->logger->debug(sprintf(
                    'Klaviyo API response status code is %s',
                    $statusCode
                ));
                break;
            }
        }
    }

    /**
     * Build a new associative array with new mappings
     */
    private function mapDataFields($map, $row) : array
    {
        $klaviyoData = [];
        foreach ($row as $key => $value) {
            if (array_key_exists($key, $map)) {
                $klaviyoKey = $map[$key]['field'];
                $klaviyoData[$klaviyoKey] = $value;
            } else if ('email' === $key) {
                continue;
            } else {
                $klaviyoData[$key] = $value;
            }
        }

        return $klaviyoData;
    }

    public function syncKlaviyo(string $dbTable, int $id, int $state)
    {
        $this->tableMapper->updateSyncedKlaviyo($dbTable, $id, $state);
    }
}
