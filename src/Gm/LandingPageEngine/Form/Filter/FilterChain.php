<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Filter
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2016
 */
namespace Gm\LandingPageEngine\Form\Filter;

class FilterChain
{
    const CHAIN_STRING_DELIMITER = ' -> ';

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

    public function __toString()
    {
        $filterChainString = '{ ';
        foreach ($this->filters as $filter) {
            $filterChainString .= $filter;
            $filterChainString .= self::CHAIN_STRING_DELIMITER;
        }

        // cut off the trailing comma ', ' (last 2 characters)
        return substr(
            $filterChainString,
            0,
            strlen($filterChainString) - strlen(self::CHAIN_STRING_DELIMITER)
        ) . ' }';
    }
}
