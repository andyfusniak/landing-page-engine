<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Entity
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Entity;

class FormConfigCollection
{
    /**
     * @var array lookup table of form to FormConfig entries
     */
    protected $forms = [];

    /**
     * @param FormConfig $formConfig form config object
     * @return FormConfigCollection
     */
    public function addFormConfig(FormConfig $formConfig)
    {
        $key = $formConfig->getName();
        $this->forms[$key] = $formConfig;
        return $this;
    }

    /**
     * @return array associative array of form name to FormConfig objects
     */
    public function getAllFormConfigs()
    {
        return $this->forms;
    }

    /**
     * Get a FormConfig by name
     * @return FormConfig
     */
    public function getFormConfigByName($name)
    {
        if (isset($this->forms[$name])) {
            return $this->forms[$name];
        }
        return null;
    }
}
