<?php declare(strict_types=1);
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Config
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Config;

use Symfony\Component\Config\Util\XmlUtils;
use Gm\LandingPageEngine\Config\Exception\DeveloperConfigXmlException;
use Gm\LandingPageEngine\Entity\AppProfile;
use Gm\LandingPageEngine\Entity\DeveloperDatabaseProfile;
use Gm\LandingPageEngine\Entity\HostProfile;

use DOMElement;
use Monolog\Logger;

class DeveloperConfig
{
    const NODE_TYPE_STRING        = 'string';
    const NODE_TYPE_BOOL          = 'bool';
    const NODE_TYPE_ENUM_LOGLEVEL = 'enum:{debug,info,notice,warning,error,critical,alert,emergency}';

    const NODE_APP_CONNECTION_PROFILE         = 'connection-profile';
    const NODE_APP_DEVELOPER_MODE             = 'developer-mode';
    const NODE_APP_SKIP_AUTO_VAR_DIR_SETUP    = 'skip-auto-var-dir-setup';
    const NODE_APP_SKIP_AUTO_THEME_ACTIVATION = 'skip-auto-theme-activation';
    const NODE_APP_NO_CAPTURE                 = 'no-capture';
    const NODE_APP_PROJECT_ROOT               = 'project-root';
    const NODE_APP_WEB_ROOT                   = 'web-root';
    const NODE_APP_THEMES_ROOT                = 'themes-root';
    const NODE_APP_TWIG_CACHE_DIR             = 'twig-cache-dir';
    const NODE_APP_LOG_FULLPATH               = 'log-fullpath';
    const NODE_APP_LOG_LEVEL                  = 'log-level';

    const NODE_VALUE_DEFAULT = '@default';

    protected static $xmlNodeAppNodeTypes = [
        self::NODE_APP_CONNECTION_PROFILE         => self::NODE_TYPE_STRING,
        self::NODE_APP_DEVELOPER_MODE             => self::NODE_TYPE_BOOL,
        self::NODE_APP_SKIP_AUTO_VAR_DIR_SETUP    => self::NODE_TYPE_BOOL,
        self::NODE_APP_SKIP_AUTO_THEME_ACTIVATION => self::NODE_TYPE_BOOL,
        self::NODE_APP_NO_CAPTURE                 => self::NODE_TYPE_BOOL,
        self::NODE_APP_PROJECT_ROOT               => self::NODE_TYPE_STRING,
        self::NODE_APP_WEB_ROOT                   => self::NODE_TYPE_STRING,
        self::NODE_APP_THEMES_ROOT                => self::NODE_TYPE_STRING,
        self::NODE_APP_TWIG_CACHE_DIR             => self::NODE_TYPE_STRING,
        self::NODE_APP_LOG_FULLPATH               => self::NODE_TYPE_STRING,
        self::NODE_APP_LOG_LEVEL                  => self::NODE_TYPE_ENUM_LOGLEVEL
    ];

    /**
     * @var AppProfile
     */
    protected $app ;
    /**
     * @var array associative array of DeveloperDatabaseProfile objects
     */
    protected $databases = [];

    /**
     * @var array associative array of HostProfile objects
     */
    protected $hosts = [];

    /**
     * Load a config.xml file and create a DeveloperConfig instance
     *
     * @param string $configFilepath full path to the config.xml file
     * @return DeveloperConfig
     */
    public static function loadXmlConfig(string $configFilepath) : DeveloperConfig
    {
        try {
            $domDoc = XmlUtils::loadFile($configFilepath);
        } catch (\Exception $e) {
            throw $e;
        }

        $configElement = $domDoc->getElementsByTagName('config')->item(0);

        if (null === $configElement) {
            throw new DeveloperConfigXmlException(
                'config.xml is missing a <config> element'
            );
        }

        // the <config> element contains <app>, <databases> and <hosts> elements
        foreach ($configElement->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                switch ($node->nodeName) {
                    case 'app':
                        $app = self::processAppDomNode($node);
                        break;
                    case 'databases':
                        $databases = self::processDatabasesDomNode($node);
                        break;
                    case 'hosts':
                        $hosts = self::processHostsDomNode($node);
                        break;
                    default:
                        throw new DeveloperConfigXmlException(sprintf(
                            'config.xml <config> element contains an unknown element <%s> in config.xml line %s',
                            $node->nodeName,
                            $node->getLineNo()
                        ));
                }
            }
        }
        return new DeveloperConfig($app, $databases, $hosts);
    }

    private static function processHostsDomNode(DOMElement $hostsNode) : array
    {
        $hosts = [];

        $numHostElements = 0;
        foreach ($hostsNode->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                if ('host' === $node->nodeName) {
                    $hosts[] = self::processHostDomNode($node);
                    $numHostElements++;
                } else {
                    throw new DeveloperConfigXmlException(sprintf(
                        'config.xml <hosts> element contains an unknown element <%s> in config.xml line %s',
                        $node->nodeName,
                        $node->getLineNo()
                    ));
                }
            }
        }

        if (0 === $numHostElements) {
            throw new DeveloperConfigXmlException(sprintf(
                'config.xml <hosts> element contains no <host> elements in config.xml line %s',
                $hostsNode->getLineNo()
            ));
        }
        return $hosts;
    }

    private static function xmlValue(DOMElement $node, string $type)
    {
        /** @var array */
        static $logLevels = null;

        $value = $node->nodeValue;
        switch ($type) {
            case self::NODE_TYPE_STRING:
                if (preg_match("/^[a-zA-Z0-9@]+$/", $value) === 0) {
                    throw new DeveloperConfigXmlException(sprintf(
                        'config.xml <%s> element expects a string type (%s given) value config.xml line %s',
                        $node->nodeName,
                        $value,
                        $node->getLineNo()
                    ));
                }
                return $node->nodeValue;
                break;
            case self::NODE_TYPE_BOOL:
                $value = strtolower($value);
                if ('true' === $value) {
                    return true;
                } else if ('false' === $value) {
                    return false;
                } else {
                    throw new DeveloperConfigXmlException(sprintf(
                        'config.xml <%s> element expects a bool type value of true or false (%s given) in config.xml line %s',
                        $node->nodeName,
                        $value,
                        $node->getLineNo()
                    ));
                }
                break;
            case self::NODE_TYPE_ENUM_LOGLEVEL:
                if (null === $logLevels) {
                    $logLevels = explode(
                        ',',
                        substr(explode(':', $type)[1], 1, -1)
                    );
                }

                if (true === in_array($value, $logLevels)) {
                    return constant(sprintf(
                        '%s::%s',
                        Logger::class,
                        strtoupper($value)
                    ));
                } else {
                    throw new DeveloperConfigXmlException(sprintf(
                        'config.xml <%s> element expects an enum type with values of {%s}, (%s given) in config.xml line %s',
                        $node->nodeName,
                        implode(', ', $logLevels),
                        $value,
                        $node->getLineNo()
                    ));
                }
                break;
        }
    }

    private static function processAppDomNode(DOMElement $appNode) : AppProfile
    {
        // use a lookup to denote values that haven't been set in the XML config
        $app = [];
        foreach (array_keys(self::$xmlNodeAppNodeTypes) as $key) {
            $app[$key] = null;
        }

        foreach ($appNode->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                if (true === in_array($node->nodeName, array_keys(self::$xmlNodeAppNodeTypes))) {
                    $app[$node->nodeName] = self::xmlValue(
                        $node,
                        self::$xmlNodeAppNodeTypes[$node->nodeName]
                    );
                } else {
                    throw new DeveloperConfigXmlException(sprintf(
                        'config.xml <app> element contains an unknown element <%s> in config.xml line %s',
                        $node->nodeName,
                        $node->getLineNo()
                    ));
                }
            }
        }

        // build a list of missing elements within the <app> element
        $missing = [];
        if (in_array(null, array_values($app))) {
            foreach ($app as $n => $v) {
                if (null === $v) {
                    $missing[] = '<' . $n . '>';
                }
            }
        }
        if (!empty($missing)) {
            throw new DeveloperConfigXmlException(sprintf(
                'config.xml <app> element contains is missing %s element%s in config.xml line %s',
                implode(', ', $missing),
                (count($missing) > 1) ? 's' : '',
                $appNode->getLineNo()
            ));
        }
        $appProfile = new AppProfile();
        $appProfile->setConnectionProfile($app[self::NODE_APP_CONNECTION_PROFILE])
                   ->setDeveloperMode($app[self::NODE_APP_DEVELOPER_MODE])
                   ->setSkipAutoVarDirSetup($app[self::NODE_APP_SKIP_AUTO_VAR_DIR_SETUP])
                   ->setSkipAutoThemeActivation($app[self::NODE_APP_SKIP_AUTO_THEME_ACTIVATION])
                   ->setNoCapture($app[self::NODE_APP_NO_CAPTURE])
                   ->setProjectRoot($app[self::NODE_APP_PROJECT_ROOT])
                   ->setWebRoot($app[self::NODE_APP_WEB_ROOT])
                   ->setThemesRoot($app[self::NODE_APP_THEMES_ROOT])
                   ->setTwigCacheDir($app[self::NODE_APP_TWIG_CACHE_DIR])
                   ->setLogFullpath($app[self::NODE_APP_LOG_FULLPATH])
                   ->setLogLevel($app[self::NODE_APP_LOG_LEVEL]);
        return $appProfile;
    }

    private static function processDatabasesDomNode(DOMElement $databasesNode) : array
    {
        $databases = [];
        // the <databases> element must contain one or more <database profile="xyz"> elements

        $numDatabaseElements = 0;
        foreach ($databasesNode->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                if ('database' === $node->nodeName) {
                    $databases[] = self::processDatabaseDomNode($node);
                    $numDatabaseElements++;
                } else {
                    throw new DeveloperConfigXmlException(sprintf(
                        'config.xml <databases> element contains an unknown element <%s> in config.xml line %s',
                        $node->nodeName,
                        $node->getLineNo()
                    ));
                }
            }
        }

        if (0 === $numDatabaseElements) {
            throw new DeveloperConfigXmlException(sprintf(
                'config.xml <databases> element contains no <database> elements in config.xml line %s',
                $databasesNode->getLineNo()
            ));
        }
        return $databases;
    }

    private static function processHostDomNode(DOMElement $hostNode) : HostProfile
    {
        // the <host> element must contain a <domain> and <theme> element
        $host = [
            'domain' => null,
            'theme'  => null
        ];
        foreach ($hostNode->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                switch ($node->nodeName) {
                    case 'domain':
                        $host['domain'] = $node->nodeValue;
                        break;
                    case 'theme':
                        $host['theme'] = $node->nodeValue;
                        break;
                }
            }
        }

        // build a list of missing elements within the <database> element
        $missing = [];
        if (in_array(null, array_values($host))) {
            foreach ($host as $n => $v) {
                if (null === $v) {
                    $missing[] = '<' . $n . '>';
                }
            }
        }
        if (!empty($missing)) {
            throw new DeveloperConfigXmlException(sprintf(
                'config.xml <host> element contains is missing %s element%s in config.xml line %s',
                implode(', ', $missing),
                (count($missing) > 1) ? 's' : '',
                $hostNode->getLineNo()
            ));
        }
        return new HostProfile($host['domain'], $host['theme']);
    }

    private static function processDatabaseDomNode(DOMElement $databaseNode) : DeveloperDatabaseProfile
    {
        if (false === $databaseNode->hasAttribute('profilename')) {
            throw new DeveloperConfigXmlException(sprintf(
                'config.xml <database> has a missing profilename attribute in config.xml line %s',
                $databaseNode->getLineNo()
            ));
        }

        // the <database> element must contain <dbhost>, <dbuser>, <dbpass> and <dbuser> elements
        $db = [
            'dbhost' => null,
            'dbuser' => null,
            'dbpass' => null,
            'dbname' => null
        ];
        foreach ($databaseNode->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                switch ($node->nodeName) {
                    case 'dbhost':
                        $db['dbhost'] = $node->nodeValue;
                        break;
                    case 'dbuser':
                        $db['dbuser'] = $node->nodeValue;
                        break;
                    case 'dbpass':
                        $db['dbpass'] = $node->nodeValue;
                        break;
                    case 'dbname':
                        $db['dbname'] = $node->nodeValue;
                        break;
                    default:
                        throw new DeveloperConfigXmlException(sprintf(
                            'config.xml <databases> element contains an unknown element <%s> in config.xml line %s',
                            $node->nodeName,
                            $node->getLineNo()
                        ));
                }
            }
        }

        // build a list of missing elements within the <database> element
        $missing = [];
        if (in_array(null, array_values($db))) {
            foreach ($db as $n => $v) {
                if (null === $v) {
                    $missing[] = '<' . $n . '>';
                }
            }
        }
        if (!empty($missing)) {
            throw new DeveloperConfigXmlException(sprintf(
                'config.xml <database> element contains is missing %s element%s in config.xml line %s',
                implode(', ', $missing),
                (count($missing) > 1) ? 's' : '',
                $databaseNode->getLineNo()
            ));
        }

        return new DeveloperDatabaseProfile(
            $databaseNode->getAttribute('profilename'),
            $db['dbhost'],
            $db['dbuser'],
            $db['dbpass'],
            $db['dbname']
        );
    }

    public function __construct(AppProfile $app, array $databases, array $hosts)
    {
        // app
        $this->app = $app;

        // databases
        foreach ($databases as $developerDatabaseProfile) {
            if ($developerDatabaseProfile instanceof DeveloperDatabaseProfile) {
                $this->addDatabaseProfile($developerDatabaseProfile);
            }
        }

        // hosts
        foreach ($hosts as $hostProfile) {
            if ($hostProfile instanceof HostProfile) {
                $this->addHostProfile($hostProfile);
            }
        }
    }

    /**
     * Add a DatabaseProfile to the databases
     *
     * @param DeveloperDatabaseProfile $profile the database profile object to be added
     * @return DeveloperConfig
     */
    public function addDatabaseProfile(DeveloperDatabaseProfile $databaseProfile) : DeveloperConfig
    {
        $this->databases[] = $databaseProfile;
        return $this;
    }

    /**
     * Add a HostProfile to the hosts
     *
     * @param HostProfile $profile the database profile object to be added
     * @return DeveloperConfig
     */
    public function addHostProfile(HostProfile $hostProfile) : DeveloperConfig
    {
        $this->hosts[] = $hostProfile;
        return $this;
    }
}
