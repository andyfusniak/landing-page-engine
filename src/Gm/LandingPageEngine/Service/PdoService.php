<?php
namespace Gm\LandingPageEngine\Service;

use Monolog\Logger;

class PdoService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(Logger $logger, $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    public function getPdoObject() {
        
        if (null !== $this->pdo) {
            return $this-pdo;
        }

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