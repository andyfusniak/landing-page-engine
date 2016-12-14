<?php
namespace Gm\LandingPageEngine\Service;

class PdoService
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getPdoObject() {
        
        if (null !== $this->pdo) {
            return $this-pdo;
        }

        try {
            $this->pdo = new \PDO(
                'mysql:host=' . $this->config['db']['dbhost'] . ';dbname='
                . $this->config['db']['dbname'],
                $this->config['db']['dbuser'],
                $this->config['db']['dbpass'],
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
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