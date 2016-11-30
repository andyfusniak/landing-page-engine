<?php
namespace Gm\LandingPageEngine;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Gm\LandingPageEngine\Service\CaptureService;
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
     * @var array
     */
    protected $config;
   
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
    protected $themeConfig;

    /**
     * @var CaptureService
     */
    protected $captureService;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Initialise a Landing Page Engine instance and wire it up
     *
     * @param array $config the application configuration
     * @return LpEngine
     */
    public static function init($config)
    {
        // setup the logging
        $logger = new Logger('lpengine');
        $logger->pushHandler(
            new StreamHandler($config['log_fullpath'], $config['log_level'])
        );
        $logger->info('LP Engine Initialising');

        // setup the request and response
        $request = Request::createFromGlobals();
        $response = new Response();
        $response->setProtocolVersion('1.1');
         
        // create a new landing page engine instance
        $engine = new LpEngine($request, $response, $logger, $config);

        // Build the custom URL routes using the developer's theme.json file
        $themeConfig = $engine->getThemeConfig();
        $routes = new RouteCollection();
        foreach ($themeConfig['routes'] as $mapping) {
            $routes->add($mapping['route'], new Route('/' . $mapping['route'], [
                '_controller' =>
                    'Gm\LandingPageEngine\Controller\FrontController:showAction',
                    'template' => $mapping['template']
            ]));
        }

        $logger->info(sprintf(
            'Configured route "%s" to map to twig template "%s"',
            $mapping['route'],
            $mapping['template']
        ));

        // Build a dedicated URL route for handling form posts
        $routes->add('http-post', new Route('/process-post', [
            '_controller' => 'Gm\LandingPageEngine\Controller\FormController:postAction',
        ], [], [], '', [], ['POST']));
        $logger->info(sprintf(
            'Added dedicated route for /process-post for HTTP form POSTS'
        ));

        $engine->setRoutes($routes);

        return $engine;
    }
    
    public function __construct(Request $request,
                                Response $response,
                                Logger $logger,
                                array $config)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->logger   = $logger;
        $this->config   = $config;

        \Twig_Autoloader::register();
        $twigTemplateDir = $config['themes_root'] . '/activetheme/templates';
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
        $this->loadThemeConfig();
    }

    public function run()
    {
        $session = $this->getSession();
        if (null === $session->get('initial_query_params')) {
            $session->set('initial_query_params', $this->getRequest()->query->all());
        }   
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
    }

    public function loadThemeConfig()
    {
        $jsonThemeFilepath = $this->config['themes_root'] 
                             . '/activetheme/theme.json';
        $this->logger->debug(sprintf(
            'Loaded JSON theme from %s',
            $jsonThemeFilepath
        ));

        $string = '{"title": "The lord of the rings"}';
        $string = file_get_contents($jsonThemeFilepath);
        $json = json_decode($string, true);

        if (null === $json) {
            $this->logger->error(sprintf(
                'The theme JSON file "%s" could not be parsed',
                $jsonThemeFilepath
            ));
            throw new \Exception(sprintf(
                'The theme JSON file "%s" could not be parsed',
                $jsonThemeFilepath
            ));
        }

        // check the template contains appropriate contents
        if (isset($json['name']) && (mb_strlen($json['name']) > 0)) {
            $this->logger->info(sprintf(
                'Template "%s" in use."',
                $json['name']
            ));
        } else {
            $this->logger->warning(sprintf(
                'Template "%s" has a missing theme name.  Use {"name": "Template name"} to set your theme name.',
                $jsonThemeFilepath
            ));
        }

        if (isset($json['version']) && (mb_strlen($json['version']) > 0)) {
            $this->logger->info(sprintf(
                'Template version %s in use.',
                $json['version']
            ));
        } else {
            $this->logger->warning(sprintf(
                'Template has no version string set in the theme config ("%s"), so we are running an unknown version of the theme.',
                $jsonThemeFilepath
            ));
        }

        $this->themeConfig = $json;
    }

    /**
     * Return the theme config as an array
     * @return array
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * Returns the Twig Environement instance
     * @return \Twig_Environment
     */
    public function getTwigEnv()
    {
        return $this->twigEnv;
    }

    public function getTwigTags()
    {
        return [
            'ip_address' => $this->request->getClientIp(),
            'q' => $this->getSession()->get('initial_query_params')
        ];
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
                $this->config,
                $this->getSession()
            );
        }
        return $this->captureService;
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
}
