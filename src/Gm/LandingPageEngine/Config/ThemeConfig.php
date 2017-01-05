<?php
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

use Gm\LandingPageEngine\Entity\FormConfig;
use Gm\LandingPageEngine\Entity\FormConfigCollection;
use Gm\LandingPageEngine\Entity\FieldConfig;
use Gm\LandingPageEngine\Entity\FieldConfigCollection;
use Gm\LandingPageEngine\Entity\FilterConfig;
use Gm\LandingPageEngine\Entity\FilterConfigCollection;
use Gm\LandingPageEngine\Entity\ValidatorConfig;
use Gm\LandingPageEngine\Entity\ValidatorConfigCollection;
use Gm\LandingPageEngine\Entity\Route;
use Gm\LandingPageEngine\Config\Exception\ThemeConfigException;

use Monolog\Logger;

class ThemeConfig
{
    const CONFIG_TYPE_XML   = 'xml';
    const CONFIG_TYPE_YAML  = 'yaml';
    const CONFIG_TYPE_JSON  = 'json';
    const CONFIG_TYPE_ARRAY = 'array';

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $themeName;

    /**
     * @var string
     */
    protected $themeVersion;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @var FormConfigCollection
     */
    protected $formConfigCollection;

    /**
     *
     * @param \DOMDocument|array $themeObj
     */
    public function __construct(Logger $logger,
                                $themeObj,
                                $type = self::CONFIG_TYPE_XML)
    {
        $this->logger = $logger;
        if ((self::CONFIG_TYPE_XML === $type)
            && ($themeObj instanceof \DOMDocument)) {
            $this->themeConfigFromDomDocument($themeObj);
        }
    }

    private function themeConfigFromDomDocument($domDoc)
    {
        // get the root theme node
        /* @var \DOMElement */
        $themeElement = $domDoc->getElementsByTagName('theme')->item(0);

        // get the theme->name node value
        $this->themeName = $themeElement->getElementsByTagName('name')->item(0)->nodeValue;

        // get the theme->value node value
        $this->themeVersion = $themeElement->getElementsByTagName('version')->item(0)->nodeValue;

        /* @var \DOMElement */
        $routesElement = $themeElement->getElementsByTagName('routes')->item(0);
        if (null === $routesElement) {
            throw new ThemeConfigException('config.xml is missing a <config> element');
        }

        $routeNodeList = $routesElement->getElementsByTagName('route');
        if ($routeNodeList->length < 1) {
            $this->logger->error(
                'The theme config contains a <routes> section but defines no <route> sections.'
            );
        }

        // the <routes> element contains one or more <route name="..."> elements
        foreach ($routesElement->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                if ('route' === $node->nodeName) {
                    // if no <route name=".."> attribute is not set
                    // the use a unique reference
                    if (false === $node->hasAttribute('name')) {
                        $key = uniqid('route_');
                    } else {
                        $key = $node->getAttribute('name');
                    }

                    if (true === array_key_exists($key, $this->routes)) {
                        throw new ThemeConfigException(sprintf(
                            '<route name="%s"> is a duplicate route in config.xml line %s',
                            $key,
                            $node->getLineNo()
                        ));
                    }

                    $this->routes[$key] = new Route(
                        $key,
                        $node->getElementsByTagName('url')->item(0)->nodeValue,
                        $node->getElementsByTagName('target')->item(0)->nodeValue
                    );
                }
            }
        }

        // theme forms
        $formsNodeElement = $themeElement->getElementsByTagName('forms')->item(0);

        // if there is no form section then we're done
        if (null === $formsNodeElement) {
            return;
        }

        $formNodeList = $formsNodeElement->getElementsByTagName('form');

        // forms
        $formConfigCollection = new FormConfigCollection();
        foreach ($formNodeList as $formElement) {
            // form->form
            $formConfig = new FormConfig(
                $formElement->getAttribute('name'),
                $formElement->getAttribute('dbtable')
            );
            $formConfigCollection->addFormConfig($formConfig);

            // forms->form->field
            $fieldNodeList = $formElement->getElementsByTagName('field');
            $fieldConfigCollection = new FieldConfigCollection();
            $formConfig->setFieldConfigCollection($fieldConfigCollection);

            foreach ($fieldNodeList as $fieldNodeElement) {
                // <field name="..." dbcolumn="..."
                $fieldConfig = new FieldConfig(
                    $fieldNodeElement->getAttribute('name'),
                    $fieldNodeElement->getAttribute('dbcolumn')
                );
                $fieldConfigCollection->addFieldConfig($fieldConfig);



                // filters are optional
                $filtersElement = $fieldNodeElement->getElementsByTagName('filters')->item(0);
                if (null !== $filtersElement) {
                    $filterNodeList = $filtersElement->getElementsByTagName('filter');

                    $filterConfigCollection = new FilterConfigCollection();
                    foreach ($filterNodeList as $filterElement) {
                        $filterName = $filterElement->getAttribute('name');
                        $filterConfigCollection->addFilterConfig(
                            new FilterConfig($filterName)
                        );
                    }
                    $fieldConfig->setFilterConfigCollection($filterConfigCollection);
                }

                // validators are optional
                $validatorsElement = $fieldNodeElement->getElementsByTagName('validators')->item(0);
                if (null !== $validatorsElement) {
                    $validatorNodeList = $validatorsElement->getElementsByTagName('validator');

                    $validatorConfigCollection = new ValidatorConfigCollection();
                    foreach ($validatorNodeList as $validatorElement) {
                        $validatorName = $validatorElement->getAttribute('name');
                        $validatorConfigCollection->addValidatorConfig(
                            new ValidatorConfig($validatorName)
                        );
                    }
                    $fieldConfig->setValidatorConfigCollection($validatorConfigCollection);
                }
            }
        }

        return $this->formConfigCollection = $formConfigCollection;
    }


    private function addRoute($url, $target)
    {
        $this->routes[$url] = $target;
        return $this;
    }

    /**
     * Get the theme name
     *
     * @return string name of the theme
     */
    public function getThemeName()
    {
        return $this->themeName;
    }

    /*
     * Get the theme version
     *
     * @return string version of the theme
     */
    public function getThemeVersion()
    {
        return $this->themeVersion;
    }

    /**
     * Returns an associative array of Route objects
     *
     * @return array associative array of Route objects
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get the form config collection
     *
     * @return FormConfigCollection
     */
    public function getFormConfigCollection()
    {
        return $this->formConfigCollection;
    }
}