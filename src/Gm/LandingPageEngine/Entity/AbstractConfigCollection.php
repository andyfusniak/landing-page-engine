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

class AbstractConfigCollection implements \Iterator, \Countable
{
    /**
     * @var int
     */
    protected $cursor = 0;

    /**
     * @var array
     */
    protected $collection;

    public function rewind()
    {
        $this->cursor = 0;
    }

    public function current()
    {
        return $this->collection[$this->cursor];
    }

    public function key()
    {
        return $this->cursor;
    }

    public function next() {
        ++$this->cursor;
    }

    public function valid() {
        return isset($this->collection[$this->cursor]);
    }

    public function count()
    {
        return count($this->collection);
    }
}
