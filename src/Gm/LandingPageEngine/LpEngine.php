<?php
namespace Gm\LandingPageEngine;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Gm\LandingPageEngine\Form\Filter\FilterChain;
use Gm\LandingPageEngine\Form\Validator\ValidatorChain;
use Gm\LandingPageEngine\Service\CaptureService;
use Gm\LandingPageEngine\Version\Version;
use Gm\LandingPageEngine\TwigGlobals\ThaiDate;

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
    protected $twigGlobals;

    /**
     * @var array
     */
    protected $themeConfig;

    /**
     * @var string
     */
    protected $theme;

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
     * @param @config array the application config
     * @return bool true if the /var/log was successfully created
     * @throws \Exception if the project_root/var dir cannot be written to
     */
    public static function setupVarDirectoryAndPermissions($config)
    {
        // Check the var directory structure is in place
        $varDir = $config['project_root'] . '/var';
        if (!file_exists($varDir)) {
            mkdir($varDir, 0777);
            chmod($varDir, 0777); 
        }

        $twigCacheDir = isset($config['twig_cache_dir']) ? $config['twig_cache_dir'] : null;
        if (null === $twigCacheDir) {
            throw new \Exception(
                'The config.php does not contain a \'twig_cache_dir\' entry'
            );
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

    public function activateThemes()
    {
        $hostsConfig = isset($this->config['hosts']) ? $this->config['hosts'] : null;
        if (null === $hostsConfig) {
            throw new \Exception(
                'config.php contains no \'hosts\' configuration.  You must have at least one valid host-to-theme mapping.'
            );
        }

        $publicAssets = $this->config['web_root'] . '/assets';
        if (!is_dir($publicAssets)) {
            throw new \Exception(sprintf(
                '%s directory does not exist.  You should create this directory and make it writeable by the web server',
                $publicAssets
            ));
        }

        if (!is_writeable($publicAssets)) {
            throw new \Exception(sprintf(
                '%s directory is not writeable by the web server.  Change the permissions using chmod g+w,o+w %s',
                $publicAssets,
                $publicAssets
            ));
        }

        // iterate the list of hosts in their natural order
        // and check the theme for each is activated
        $hosts = array_keys($hostsConfig);
        natsort($hosts);
 
        // remove duplicates    
        $themesToActivate = [];
        foreach ($hosts as $host) {
            $theme = $hostsConfig[$host];
            $parts = explode(':', $theme);
            $theme = $parts[0];

            if (!in_array($theme, $themesToActivate)) {
                array_push($themesToActivate, $theme);
            }
        }
        
        
        // deactivate any themes that aren't going to be in use
        // On a broken symlink is_link() returns true and file_exists() returns false.
        $publicAssets = $this->config['web_root'] . '/assets';
        foreach (scandir($publicAssets) as $entry) {
            if (('.' === $entry) || ('..' === $entry)) {
                continue;
            }
            
            // check if the current symlink in the public/assets directory
            // is still in use.  If not, remove it
            if (!in_array($entry, $themesToActivate)) {
                $this->logger->debug(sprintf(
                    '%s link found in %s directory',
                    $entry,
                    $publicAssets
                ));
                $fullpath = $publicAssets . '/' . $entry;
                
                if (false === unlink($fullpath)) {
                    $logger->info(sprintf(
                        'Failed to unlink "%s".  This theme needs to be deactivted as is no longer in use.',
                        $fullpath
                    ));
                } else {
                    $this->logger->debug(sprintf(
                        'unlink %s',
                        $fullpath
                    ));
                    $this->logger->info(sprintf(
                        'Deactivating theme "%s" from the web root.',
                        $entry
                    ));
                }
            }
        }

        // activate each theme
        foreach ($themesToActivate as $name) {
            $this->activateTheme($name);
        }
    }

    public function activateTheme($name)
    {
        if (!preg_match('/^[a-z0-9\-]+$/', $name)) {
            throw new \InvalidArgumentException(sprintf(
                '%s called for theme %s.  Theme names must use lower case a-z only, including the digits 0-9 and the hyphen character.',
                __METHOD__,
                $name
            ));
        }

        $publicAssets = $this->config['web_root'] . '/assets';
        if (!is_dir($publicAssets)) {
            throw new \Exception(sprintf(
                '%s directory does not exist.  You should create this directory and make it writeable by the web server',
                $publicAssets
            ));
        }

        if (!is_writeable($publicAssets)) {
            throw new \Exception(sprintf(
                '%s directory is not writeable by the web server.  Change the permissions using chmod g+w,o+w %s',
                $publicAssets,
                $publicAssets
            ));
        }

        $target = '../../themes/' . $name . '/assets/' . $name;
        $link = $this->config['web_root'] . '/assets/' . $name;

        if (!is_link($link)) {
            if (!@symlink($target, $link)) {
                throw new \Exception(sprintf(
                    'Failed to symlink %s to %s.  Make sure %s is writeable by the web server',
                    $target,
                    $link,
                    $publicAssets
                ));
            }
        }
    }


    /**
     * Initialise a Landing Page Engine instance and wire it up
     *
     * @param array $config the application configuration
     * @return LpEngine
     */
    public static function init($config)
    {
        
        if (isset($config['skip_auto_var_dir_setup']) &&
            (true === $config['skip_auto_var_dir_setup'])) {
            $logDirReady = true;
        } else {
            $logDirReady = self::setupVarDirectoryAndPermissions($config);
        }

        $logDirReady = self::setupVarDirectoryAndPermissions($config);

        // setup the logging
        $logger = new Logger('lpengine');
        if (true === $logDirReady) {
            $logger->pushHandler(
                new StreamHandler($config['log_fullpath'], $config['log_level'])
            );
        }

        // setup the request and response
        $request = Request::createFromGlobals();
        $response = new Response();
        $response->setProtocolVersion('1.1');
         
        // create a new landing page engine instance
        $engine = new LpEngine($request, $response, $logger, $config);

        // activate the themes
        if (isset($config['skip_auto_theme_activation']) &&
            (true === $config['skip_auto_theme_activation'])) {
            $logger->info('Skipping theme activation checks as skip_auto_theme_activation=true');
        } else {
            $engine->activateThemes();
        }

        // Build the custom URL routes using the developer's theme.json file
        $themeConfig = $engine->getThemeConfig();

        // Check for missing routes section in theme.json config file
        if (isset($themeConfig) && (!isset($themeConfig['routes']))) {
            $logger->error('Your theme.json is missing a "routes" section.  You must define at least one route.');
            throw new \Exception(
                'Your theme.json file is missing a "routes" section.  You must define at least one route.'
            );
        }

        // Check the "routes" section of the theme.json config file contains at least one route
        if (count($themeConfig['routes']) < 1) {
            $logger->warning('Your theme.json contains a "routes" section but defines no mappings.');
        }

        $routes = new RouteCollection();
        foreach ($themeConfig['routes'] as $url => $template) {
            $routes->add($url, new Route('/' . $url, [
                '_controller' =>
                    'Gm\LandingPageEngine\Controller\FrontController:showAction',
                    'template' => $template
            ]));
            $logger->info(sprintf(
                'Configured route "%s" to map to twig template "%s"',
                $url,
                $template
            ));
        }

        // Build a dedicated URL route for handling form posts
        $routes->add('http-post', new Route('/process-post', [
            '_controller' => 'Gm\LandingPageEngine\Controller\FormController:postAction',
        ], [], [], '', [], ['POST']));
        $logger->info(sprintf(
            'Added dedicated route for /process-post for HTTP form POSTS'
        ));

        $routes->add('status-page', new Route('/status-page', [
            '_controller' => 'Gm\LandingPageEngine\Controller\StatusPageController:showAction'
        ], [], [], '', [], ['GET']));

        $engine->setRoutes($routes);

        return $engine;
    }
    
    public function __construct(Request $request,
                                Response $response,
                                Logger $logger,
                                array $config)
    {
        $logger->info(sprintf(
            'LPE Version %s Running',
            Version::VERSION
        ));
        $this->request  = $request;
        $this->response = $response;
        $this->logger   = $logger;
        $this->config   = $config;
       
        $host = $this->request->getHost(); 
        if (isset($config['hosts'][$host])) {
            $theme = $config['hosts'][$host];
            $logger->debug(sprintf(
                'Host "%s" is configure to use theme "%s".  Checking theme exists',
                $host,
                $theme
            ));
            \Twig_Autoloader::register();
            $twigTemplateDir = $config['themes_root'] . '/' . $theme . '/templates';
            $this->theme = $theme;
        } else {
            throw new \Exception(sprintf(
                'No host-to-template mapping configured for the host "%s".  Check your config.php file',
                $host
            ));
        }

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

        $this->loadThemeConfig();
    }

    public function run()
    {
        $session = $this->getSession();
        if (null === $session->get('initial_query_params')) {
            $session->set('initial_query_params', $this->getRequest()->query->all());
            $session->set('ARRIVAL_HTTP_REFERER', $this->request->server->get('HTTP_REFERER'));
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
    }

    public function loadThemeConfig()
    {
        $jsonThemeFilepath = $this->config['themes_root'] . '/' . $this->theme . '/theme.json';
        $this->logger->debug(sprintf(
            'Attempt to loaded theme configuration "%s"',
            $jsonThemeFilepath
        ));

        $string = file_get_contents($jsonThemeFilepath);
        $json = json_decode($string, true);

        if (null === $json) {
            $this->logger->error(sprintf(
                'The theme JSON file "%s" could not be parsed',
                $jsonThemeFilepath
            ));
            throw new \Exception(sprintf(
                'The theme JSON file "%s" could not be parsed. Err code %s, error message "%s"',
                $jsonThemeFilepath,
                json_last_error(),
                json_last_error_msg()
            ));
        }

        // check the template contains appropriate contents
        if (isset($json['name']) && (isset($json['version']))) {
            $this->logger->info(sprintf(
                'Template "%s" version %s in use."',
                $json['name'],
                $json['version']
            ));
        } else {
            if (!isset($json['name'])) {
                $this->logger->error(sprintf(
                    'Template "%s" has a missing theme name.  Use {"name": "Template name"} section.',
                    $jsonThemeFilepath
                ));
                throw new \Exception(sprintf(
                    'theme.json file "%s" is missing compulsory {"name": "Template name"}.  The \
                    template name is needed as an autocapture field in the database.',
                    $jsonThemeFilepath
                ));
            }

            if (!isset($json['version'])) {
                $this->logger->error(sprintf(
                    'Template "%s" has a missing version.  Use {"version": "x.y.z"} section.',
                    $jsonThemeFilepath
                ));
                throw new \Exception(sprintf(
                    'theme.json file "%s" is missing compulsory {"version": "x.y.z"}.  The \
                    template version is needed as an autocapture field in the database.',
                    $jsonThemeFilepath
                ));
            }
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
                $this->config,
                $this->getSession(),
                $this->getRequest()
            );
        }
        return $this->captureService;
    }

    public function loadFiltersAndValidators($formName)
    {
        // reset the filter and validator lookups as this is a new form
        $this->fieldNameToFilterLookup = null;
        $this->fieldNameToValidatorLookup = null;

        // check for "forms": { "form-name": { ... } .. } section
        if (!isset($this->themeConfig['forms'][$formName])) {
            throw new \Exception(sprintf(
                'Cannot find definition for form "%s" in theme.json',
                $formName
            ));
        }

        $formConfig = $this->themeConfig['forms'][$formName];

        // check for { "form-name": { "map": { ... } } } section
        if (!isset($formConfig['map'])) {
            throw new \Exception(sprintf(
                'Cannot find map section for form "%s" in theme.json file',
                $formName
            ));
        }

        $map = $formConfig['map'];

        foreach ($map as $formFieldName => $formFieldConfig) {
            foreach ($formFieldConfig as $section => $chain) {
                if ('filters' === $section) {
                    $this->fieldToFilterAndValidatorLookup[$formFieldName]['filters']
                        = $this->loadFilterChain($chain);
                } else if ('validators' === $section) {
                    $this->fieldToFilterAndValidatorLookup[$formFieldName]['validators']
                        = $this->loadValidatorChain($chain);
                }
            }
        }
    }

    public function getFieldToFilterAndValidatorLookup()
    {
        return $this->fieldToFilterAndValidatorLookup;
    }

    private function loadValidatorChain($chain)
    {
        $validatorChain = new ValidatorChain();
        foreach ($chain as $name => $block) {
            $validatorChain->attach($this->loadValidator($name));
        }
        return $validatorChain;
    }

    private function loadValidator($name)
    {
        $name = 'Gm\\LandingPageEngine\\Form\\Validator\\' . $name;
        return new $name();
    }

    private function loadFilterChain($chain)
    {
        $filterChain = new FilterChain();
        foreach ($chain as $name => $block) {
            $filterChain->attach($this->loadFilter($name));
        }
        return $filterChain;
    }

    private function loadFilter($name)
    {
        $name = 'Gm\\LandingPageEngine\\Form\\Filter\\' . $name;
        return new $name();
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
