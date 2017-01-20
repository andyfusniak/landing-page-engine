<?php
namespace Gm\LandingPageEngine;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Gm\LandingPageEngine\Config\ApplicationConfig;
use Gm\LandingPageEngine\Config\DeveloperConfig;
use Gm\LandingPageEngine\Entity\DeveloperProfile;
use Gm\LandingPageEngine\Mapper\TableMapper;
use Gm\LandingPageEngine\Service\CronService;
use Gm\LandingPageEngine\Version\Version;

class Cron
{
    /**
     * @var ApplicationConfig
     */
    protected $applicationConfig;

    /**
     * @var DeveloperConfig
     */
    protected $developerConfig;

    /**
     * @var CronService
     */
    protected $cronService;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(ApplicationConfig $applicationConfig,
                                DeveloperConfig $developerConfig,
                                CronService $cronService,
                                Logger $logger)
    {
        $this->applicationConfig = $applicationConfig;
        $this->developerConfig = $developerConfig;
        $this->cronService = $cronService;
        $this->logger = $logger;
    }

    public static function init($projectRoot)
    {
        $applicationConfig = new ApplicationConfig($projectRoot);
        //var_dump($applicationConfig);
        $developerConfig = DeveloperConfig::loadXmlConfig($projectRoot . '/config/config.xml');
        $applicationConfig->overrideConfig($developerConfig);
        //var_dump($applicationConfig);
        //var_dump($developerConfig);

        $logger = new Logger('cron');
        $logger->pushHandler(
            new StreamHandler(
                $applicationConfig->getLogDir() . '/cron.log',
                Logger::DEBUG
            )
        );

        return new Cron(
            $applicationConfig,
            $developerConfig,
            new CronService($logger),
            $logger
        );
    }

    public function run()
    {
        $this->logger->info(sprintf('LPE Version %s Cron Started', Version::VERSION));

        foreach ($this->developerConfig->getProfiles() as $developerProfile) {
            if (false === empty($feeds = $developerProfile->getFeeds())) {

                if (isset($feeds['klaviyo']) && is_array($klaviyo = $feeds['klaviyo'])) {
                    $this->logger->info(sprintf(
                        'Profile %s has a klaviyo feed api-key=%s and list=%s',
                        $developerProfile->getName(),
                        isset($klaviyo['api-key']) ? $klaviyo['api-key'] : 'unknown',
                        isset($klaviyo['list']) ? $klaviyo['list'] : 'unknown'
                    ));
                    $this->process($developerProfile);
                }
            }
        }

        $this->logger->info('LPE Cron Finishing');
    }

    private function process(DeveloperProfile $developerProfile)
    {
        $rows = $this->cronService->fetchUnsyncedRows($developerProfile);
    }
}
