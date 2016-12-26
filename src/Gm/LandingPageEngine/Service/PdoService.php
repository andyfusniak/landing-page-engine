<?php
namespace Gm\LandingPageEngine\Service;

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

    public function getPdoObject() {

        if (null !== $this->pdo) {
            return $this-pdo;
        }

        var_dump($this->developerConfig);
        die();

        try {
            $dsn = 'mysql:host=' . $this->config['db']['dbhost'] . ';dbname=' .
                    $this->config['db']['dbname'];
            $user = $this->config['db']['dbuser'];

            $this->logger->debug(sprintf(
                'Data Source Name = %s, user = %s',
                $dsn,
                $user
            ));

            $this->pdo = new \PDO(
                $dsn,
                $user,
                $this->config['db']['dbpass'],
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ]
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        return $this->pdo;
    }
}