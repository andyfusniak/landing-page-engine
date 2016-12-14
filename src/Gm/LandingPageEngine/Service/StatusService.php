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
    }

    public function systemSettings()
    {
        // PHP Version
        $this->lpEngine->addTwigGlobal(
            'php_version',
            phpversion()
        );


        // PHP extensions loaded
        $phpExtensions = [
            'pdo_mysql' => (true === extension_loaded('pdo_mysql')) ? 1 : 0,
            'mysql'     => (true === extension_loaded('mysql')) ? 1 : 0,
            'mbstring'  => (true === extension_loaded('mbstring')) ? 1 : 0,
            'curl'      => (true === extension_loaded('curl')) ? 1 : 0,
        ];

        $this->lpEngine->addTwigGlobal(
            'php_extensions',
            $phpExtensions
        );

        // Disk usage stats
        $freeSpace  = disk_free_space('/');
        $totalSpace = disk_total_space('/');

        $this->lpEngine->addTwigGlobal(
            'disk_free',
            $this->formatBytes($freeSpace)
        );

        $this->lpEngine->addTwigGlobal(
            'disk_total',
            $this->formatBytes($totalSpace)
        );

        $this->lpEngine->addTwigGlobal(
            'disk_used',
            $this->formatBytes($totalSpace - $freeSpace)
        );

        $this->lpEngine->addTwigGlobal(
            'disk_percentage_used',
            round(
                (($totalSpace - $freeSpace) / $totalSpace) * 100.00
            )
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

    /**
     * For memory use oneK = 1024 (2^10 two to the power of ten)
     * Disk usage is typically measures in KB not KiB
     *
     * @return string the number of bytes as a human readable string
     */
    private function formatBytes($size, $precision = 2, $oneK = 1000)
    {
        $base = log($size, $oneK);
        if ($oneK === 1024) {
            $suffixes = ['', 'KiB', 'MiB', 'GiB', 'TiB'];    
        } else {
            $suffixes = ['', 'KB', 'MB', 'GB', 'TB'];
        }
        
        return round(pow(
                    $oneK,
                    $base - floor($base)),
                    $precision
                )
                . ' '
                . $suffixes[floor($base)];
    }
}
