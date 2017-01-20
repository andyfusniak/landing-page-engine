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
                $databaseProfile->getDbTable()
            );

            $feeds = $developerProfile->getFeeds()['klaviyo'];
            var_dump($feeds);
            $this->feedKlaviyo($feeds['api-key'], $feeds['list'], $rows);
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
    private function feedKlaviyo(string $apiKey, string $list, $rows)
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://a.klaviyo.com/api/v1/list/',
            // You can set any number of default request options.
            'timeout'  => 3.0,
        ]);



        //$row['email'] = 'andy+209@greycatmedia.co.uk';
        $row['first_name'] = 'Andrew';
        $row['last_name'] = 'Fusniak';

            $response = $client->post(
                '' . $list . '/members',
                [
                    'headers' => [
                        'Accept' => 'application/json'
                    ],

                    'form_params' => [
                        'api_key' => $apiKey,
                        'confirm_optin' => 'false',
                        'email' => 'andy+209@greycatmedia.co.uk',
                        //'properties' => '{
                        //    "first_name" : "carrots"
                        //}',
                        'properties' => json_encode($row)
                    ]
                ]
            );

        echo json_encode($row) . '<br>';

        //         'form_params' => [
        //
        //         ]
        //     ]
        // ]);

        $body = $response->getBody();
// Implicitly cast the body to a string and echo it
echo $body;

        // Get all of the response headers.
foreach ($response->getHeaders() as $name => $values) {
    echo $name . ': ' . implode(', ', $values) . "\r\n";
}
    }
}
