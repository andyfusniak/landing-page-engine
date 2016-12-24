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
use Gm\LandingPageEngine\Entity\DeveloperDatabaseProfile;

use DOMElement;

class DeveloperConfig
{
    /**
     * @var array associative array of DeveloperDatabaseProfile
     */
    protected $databases = [];

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
                        break;
                    case 'databases':
                        $databases = self::processDatabasesDomNode($node);
                        break;
                    case 'hosts':
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

        $developerConfig = new DeveloperConfig($databases);

        var_dump($developerConfig);
        return $developerConfig;
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

        if ($numDatabaseElements === 0) {
            throw new DeveloperConfigXmlException(sprintf(
                'config.xml <databases> element contains no <database> elements in config.xml line %s',
                $databasesNode->getLineNo()
            ));
        }
        return $databases;
    }

    private static function processDatabaseDomNode(DOMElement $databaseNode) : DeveloperDatabaseProfile
    {
        if (false === $databaseNode->hasAttribute('profilename')) {
            throw new DeveloperConfigXmlException(sprintf(
                'config.xml <databases> element contains an unknown element <%s> in config.xml line %s',
                $databaseNode->nodeName,
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

    public function __construct(array $databases)
    {
        foreach ($databases as $developerDatabaseProfile) {
            if ($developerDatabaseProfile instanceof DeveloperDatabaseProfile) {
                $this->addDatabaseProfile($developerDatabaseProfile);
            }
        }
    }

    public function addDatabaseProfile(DeveloperDatabaseProfile $profile) : DeveloperConfig
    {
        $this->databases[] = $profile;
        return $this;
    }
}
