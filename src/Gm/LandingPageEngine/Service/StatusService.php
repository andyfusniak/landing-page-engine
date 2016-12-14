<?php
namespace Gm\LandingPageEngine\Service;

use Gm\LandingPageEngine\Mapper\TableMapper;
use Gm\LandingPageEngine\Service\PdoService;
use Gm\LandingPageEngine\LpEngine;
use Monolog\Logger;

class StatusService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var PdoService
     */
    protected $pdoService;

    /**
     * @var LpEngine
     */
    protected $lpEngine;
    
    /**
     * @var array
     */
    protected $config;
    
    /**
     * @var TableMapper
     */
    protected $tableMapper;

    public function __construct(Logger $logger,
                                PdoService $pdoService,
                                LpEngine $lpEngine,
                                array $config)
    {
        $this->logger      = $logger;
        $this->pdoService  = $pdoService;
        $this->lpEngine    = $lpEngine;
        $this->config      = $config;
        $this->tableMapper = $this->getTableMapper();
    }

    public function getPhpSettings()
    {
        $this->lpEngine->addTwigGlobal(
            'php_version',
            phpversion()
        );
    }

    public function getTableMapper()
    {
        if (null === $this->tableMapper) {
            $pdo = $this->pdoService->getPdoObject();
            $this->tableMapper = new TableMapper($this->logger, $pdo);
        }
        return $this->tableMapper;
    }
}
