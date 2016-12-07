<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Filter
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Form\Filter;

class FilterChain
{
    /**
     * @var array of Filter objects
     */
    protected $filters = [];

    public function attach($filter)
    {
        if (!$filter instanceof FilterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a FilterInterface object as its argument',
                __METHOD__
            ));
        }
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Filter the value through the chain of filters one by one
     *
     * @param string $value the form value to check
     * @return string the final filtered value
     */
    public function filter($value)
    {
        if (!is_string($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string value',
                __METHOD__
            ));
        }

        foreach ($this->filters as $filter) {
            $value = $filter->filter($value);
        }
        return $value;
    }
}
