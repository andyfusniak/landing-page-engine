<?php
namespace Gm\LandingPageEngine;


use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Gm\LandingPageEngine\Config\ApplicationConfig;
use Gm\LandingPageEngine\Entity\FilterConfigCollection;
use Gm\LandingPageEngine\Entity\ValidatorConfigCollection;
use Gm\LandingPageEngine\Form\Filter\FilterChain;
use Gm\LandingPageEngine\Form\Validator\ValidatorChain;
use Gm\LandingPageEngine\Service\CaptureService;
use Gm\LandingPageEngine\Service\PdoService;
use Gm\LandingPageEngine\Service\StatusService;
use Gm\LandingPageEngine\Service\ThemeConfigService;
use Gm\LandingPageEngine\Version\Version;
use Gm\LandingPageEngine\TwigGlobals\ThaiDate;
use Gm\LandingPageEngine\TwigGlobals\UtmQueryParams;


use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LpEngine
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var ApplicationConfig
     */
    protected $applicationConfig;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Twig_Environment
     */
    protected $twigEnv;

    /**
     * @var array
     */
    protected $twigGlobals;

    /**
     * @var string
     */
    protected $theme;

    /**
     * @var ThemeConfigService
     */
    protected $themeConfigService;

    /**
     * @var StatusService
     */
    protected $statusService;

    /**
     * @var array
     */
    protected $fieldToFilterAndValidatorLookup;

    /**
     * @var CaptureService
     */
    protected $captureService;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Create the following directories with file permissions
     *
     *   project_root/var               0777
     *   project_root/var/log           0777
     *   project_root/var/twig_cache    0777
     *
     * @param $varDir       string the application var root dir
     * @param $twigCacheDir string the twig cache root dir
     * @param $logDir       string the log root dir
     * @return bool true if the /var/log was successfully created
     * @throws \Exception if the project_root/var dir cannot be written to
     */
    public static function setupVarDirectoryAndPermissions($varDir,
                                                           $twigCacheDir,
                                                           $logDir)
    {
        // Check the var directory structure is in place
        if (!file_exists($varDir)) {
            mkdir($varDir, 0777);
            chmod($varDir, 0777);
        }

        if (!file_exists($twigCacheDir)) {
            if ((false === @mkdir($twigCacheDir, 0777, true))
                || (false === @chmod($twigCacheDir, 0777))) {
                throw new \Exception(sprintf(
                    'Your project root dir "%s" is not writeable by the web server. Change the permissions on this directory using "chmod g+w,o+w %s"',
                    $config['project_root'],
                    $config['project_root']
                ));
            }
        }

        $logDir = $varDir . '/log';
        if (!file_exists($logDir)) {
            $logDirExists = false;
            if (true === @mkdir($varDir . '/log', 0777, true)) {
                chmod($varDir . '/log', 0777);
                $logDirExists = true;
            }
        } else {
            $logDirExists = true;
        }

        return $logDirExists;
    }

    /**
     * Initialise a Landing Page Engine instance and wire it up
     *
     * @param array $config application configuration overrides
     * @return LpEngine
     */
    public static function init($config)
    {
        $applicationConfig = new ApplicationConfig($config['project_root']);

        if (true === $applicationConfig->getDeveloperMode()) {
            Debug::enable();
        }

        if (true === $applicationConfig->getSkipAutoVarDirSetup()) {
            $logDirReady = true;
        } else {
            $logDirReady = self::setupVarDirectoryAndPermissions(
                $applicationConfig->getVarDir(),
                $applicationConfig->getTwigCacheDir(),
                $applicationConfig->getLogDir()
            );
        }

        // setup the logging and stream for log file only if the var/log
        // directory is ready and writeble.  If it's not writeable, we
        // will have an null logger
        $logger = new Logger('lpengine');
        if (true === $logDirReady) {
            $logger->pushHandler(
                new StreamHandler($config['log_fullpath'], $config['log_level'])
            );
        }

        // setup the PdoService
        $pdoService = new PdoService($logger, $config);

        // setup the request and response
        $request = Request::createFromGlobals();
        $response = new Response();
        $response->setProtocolVersion('1.1');

        // create a new landing page engine instance
        $engine = new LpEngine(
            $request,
            $response,
            $logger,
            new ThemeConfigService($logger, $applicationConfig),
            $pdoService,
            $config
        );

        // activate the themes
        $themeConfigService = $engine->getThemeConfigService();
        $themeConfigService->activateThemes($config);
        return $engine;
    }

    public function __construct(Request $request,
                                Response $response,
                                Logger $logger,
                                ThemeConfigService $themeConfigService,
                                PdoService $pdoService,
                                array $config)
    {
        $logger->info(sprintf(
            'LPE Version %s Running',
            Version::VERSION
        ));
        $this->request            = $request;
        $this->response           = $response;
        $this->logger             = $logger;
        $this->themeConfigService = $themeConfigService;
        $this->pdoService         = $pdoService;
        $this->config             = $config;

        $host = $this->request->getHost();
        if (isset($config['hosts'][$host])) {
            $this->theme = $config['hosts'][$host];
            $logger->debug(sprintf(
                'Host "%s" is configure to use theme "%s".  Checking theme exists',
                $host,
                $this->theme
            ));
            \Twig_Autoloader::register();
            $twigTemplateDir = $config['themes_root'] . '/' . $this->theme . '/templates';
        } else {
            throw new \Exception(sprintf(
                'No host-to-template mapping configured for the host "%s".  Check your config.php file',
                $host
            ));
        }

        $this->themeConfigService->loadThemeConfig($this->theme);

        // Build the custom URL routes using the theme config file
        $themeConfig = $this->themeConfigService->getThemeConfig();

        $themeConfigRoutes = $themeConfig->getRoutes();

        if (null === $themeConfigRoutes) {
            $logger->critical(
                'No routes defined'
            );
            throw new \Exception('No routes defined');
        }

        $routes = new RouteCollection();
        foreach ($themeConfigRoutes as $url => $templateOrRedirectUrl) {
            // if the template has a leading / or starts with http
            // then we will treat it as a redirct
            if ((substr($templateOrRedirectUrl, 0, 1) === '/')
                || (substr($templateOrRedirectUrl, 0, 4) === 'http')) {
                $routes->add(uniqid('redirect_'), new Route($url, [
                    '_controller'
                        => 'Gm\LandingPageEngine\Controller\RedirectController:redirectAction',
                    'redirect_url' => $templateOrRedirectUrl,
                    'title' => 'The Lost World'
                ]));
                $logger->info(sprintf(
                    'Configured redirect "%s" to map to url "%s"',
                    $url,
                    $templateOrRedirectUrl
                ));
            } else {
                $routes->add($url, new Route('/' . $url, [
                    '_controller' =>
                        'Gm\LandingPageEngine\Controller\FrontController:showAction',
                    'template' => $templateOrRedirectUrl
                ], [], [], '', [], ['GET']));

                $routes->add($url . '_post', new Route($url, [
                    '_controller' => 'Gm\LandingPageEngine\Controller\FormController:postAction',
                ], [], [], '', [], ['POST']));

                $logger->info(sprintf(
                    'Configured both HTTP GET and POST routes "%s" and "%s" to map to twig template "%s"',
                    $url,
                    $url . '-post',
                    $templateOrRedirectUrl
                ));
            }
        }

        $routes->add('status-page', new Route('/status-page', [
            '_controller' => 'Gm\LandingPageEngine\Controller\StatusPageController:showAction'
        ], [], [], '', [], ['GET']));

        $this->setRoutes($routes);

        $loader = new \Twig_Loader_Filesystem($twigTemplateDir);
        $logger->debug(sprintf(
            'Setting the Twig Loader filesystem path to %s',
            $twigTemplateDir
        ));

        if ((isset($config['developer_mode']))
            && ($config['developer_mode'] === true)) {
            $twigEnvOptions = [
                'debug'       => true,
                'cache'       => false,
                'auto_reload' => true,
            ];
        } else {
            $twigEnvOptions = [
                'cache' => $config['twig_cache_dir'],
            ];
        }

        $this->twigEnv = new \Twig_Environment($loader, $twigEnvOptions);

        // @todo needs to be more modular to lazy-load and plug them in
        // provide global for thai_date
        $this->twigEnv->addGlobal('thai_date', new ThaiDate());
    }

    public function run()
    {
        $themeConfig = $this->getThemeConfigService()->loadThemeConfig($this->theme);

        $session = $this->getSession();
        if (null === $session->get('initial_query_params')) {
            $session->set('initial_query_params', $this->getRequest()->query->all());
            $session->set('ARRIVAL_HTTP_REFERER', $this->request->server->get('HTTP_REFERER'));
        }

        $this->twigEnv->addGlobal(
            'utm_query_params',
             new UtmQueryParams($session->get('initial_query_params'))
        );
        $session->set('query_params', $this->getRequest()->query->all());

        try {
            $context = new RequestContext();
            $context->fromRequest($this->request);
            $matcher = new UrlMatcher($this->routes, $context);
            $parameters = $matcher->match($this->request->getPathInfo());

            list($controller, $action) = preg_split('/:/', $parameters['_controller']);

            // lazy load the controler instance
            $controller = new $controller($this);
            $controller->setMatch($parameters);

            // return [
            $this->addTwigGlobal('ip_address', $this->request->getClientIp());
            $this->addTwigGlobal('q', $this->getSession()->get('initial_query_params'));

            // dispatch the request and get the return string
            $this->response->setContent(
                $controller->dispatch($this->request, $this->response)
            );
        } catch (Routing\Exception\ResourceNotFoundException $e) {
            $this->response->setContent('Not Found');
            $this->response->setStatusCode(404);
        } catch (Exception $e) {
            $this->response->setContent('An error occurred');
            $this->response->setStatusCode(500);

            // rethrow the exception a quick and dirty bailout
            throw $e;
        }

        $this->response->send();

        $this->logger->info('LPE Terminating');
    }

    /**
     * Get the ThemeConfigService
     *
     * @return ThemeConfigService
     */
    public function getThemeConfigService()
    {
        return $this->themeConfigService;
    }

    /**
     * Returns the Twig Environement instance
     * @return \Twig_Environment
     */
    public function getTwigEnv()
    {
        return $this->twigEnv;
    }

    public function addTwigGlobal($name, $value)
    {
        $this->twigGlobals[$name] = $value;
        return $this;
    }

    public function getTwigTags()
    {
        return $this->twigGlobals;
    }

    public function setCaptureService(CaptureService $captureService)
    {
        $this->captureService = $captureService;
        return $this;
    }

    public function getCaptureService()
    {
        if (null === $this->captureService) {
            $this->captureService = new CaptureService(
                $this->logger,
                $this->pdoService,
                $this->config,
                $this->getSession(),
                $this->getRequest()
            );
        }
        return $this->captureService;
    }

    public function loadFiltersAndValidators($formName)
    {
        $themeConfig = $this->getThemeConfigService()->getThemeConfig();

        // reset the lookup table as this is a new form
        $this->fieldToFilterAndValidatorLookup = null;

        $formConfigCollection = $themeConfig->getFormConfigCollection();

        if (null === $formConfigCollection) {
            // no forms section in the config
            return null;
        }

        $formConfig = $formConfigCollection->getFormConfigByName($formName);

        if (null === $formConfig) {
            $this->logger->critical(sprintf(
                'Theme "%s" config contains no form with name "%s" section',
                $this->theme,
                $formName
            ));
            throw new \Exception(sprintf(
                'Theme "%s" config contains no form with name "%s" section',
                $this->theme,
                $formName
            ));
        }

        $fields = $formConfig->getFieldsConfigCollection();
        foreach ($fields as $fieldConfig) {
            $formFieldName = $fieldConfig->getName();
            if ($fieldConfig->hasFilters()) {
                $this->fieldToFilterAndValidatorLookup[$formFieldName]['filters']
                    = $this->loadFilterChain($fieldConfig->getFilterConfigCollection());
            }

            if ($fieldConfig->hasValidators()) {
                $this->fieldToFilterAndValidatorLookup[$formFieldName]['validators']
                    = $this->loadValidatorChain($fieldConfig->getValidatorConfigCollection());
            }
        }

        return $this->fieldToFilterAndValidatorLookup;
    }

    public function getFieldToFilterAndValidatorLookup()
    {
        return $this->fieldToFilterAndValidatorLookup;
    }

    /**
     * Create a ValidatorChain instance and attach validators
     * according to theme config
     *
     * @param $validatorConfigCollection
     */
    private function loadValidatorChain(ValidatorConfigCollection $validatorConfigCollection)
    {
        $validatorChain = new ValidatorChain();
        foreach ($validatorConfigCollection as $validatorConfig) {
            $name = $validatorConfig->getName();
            $validatorChain->attach($this->loadValidator($name));
            $this->logger->debug(sprintf(
                'Attached validator "%s" to the validator chain',
                $name
            ));
        }
        return $validatorChain;
    }

    private function loadValidator($name)
    {
        $name = 'Gm\\LandingPageEngine\\Form\\Validator\\' . $name;
        return new $name();
    }

    /**
     * @param FilerConfigCollection
     */
    private function loadFilterChain(FilterConfigCollection $filterConfigCollection)
    {
        $filterChain = new FilterChain();
        foreach ($filterConfigCollection as $name => $filterConfig) {
            $name = $filterConfig->getName();
            $filterChain->attach($this->loadFilter($name));
            $this->logger->debug(sprintf(
                'Attached filter "%s" to the validator chain',
                $name
            ));
        }
        return $filterChain;
    }

    private function loadFilter($name)
    {
        $name = 'Gm\\LandingPageEngine\\Form\\Filter\\' . $name;
        return new $name();
    }

    /**
     * Lazy-load the status service
     * @return StatusService
     */
    public function getStatusService()
    {
        if (null === $this->statusService) {
            $this->statusService = new StatusService($this);
        }
        return $this->statusService;
    }

    /**
     * Get the logger instance
     *
     * @return Logger the logger instance
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Get the application config
     * @return array the config associative array
     */
    public function getApplicationConfig()
    {
        return $this->applicationConfig;
    }

    /**
     * Set a session to use within the Landing Page Engine
     *
     * @param Session $session
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Get the session or create one if not set and start it
     *
     * @return Session
     */
    public function getSession()
    {
        if (null === $this->session) {
            $this->session = new Session();
            $this->session->start();
        }
        return $this->session;
    }

    /**
     * Set the routes
     *
     * @param RouteCollection $routes the custom routes
     * @return LpEngine
     */
    public function setRoutes(RouteCollection $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * Get the Request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the version id string
     *
     * @return string version id string for LPE
     */
    public function getVersionIdString()
    {
        return Version::VERSION;
    }
}
