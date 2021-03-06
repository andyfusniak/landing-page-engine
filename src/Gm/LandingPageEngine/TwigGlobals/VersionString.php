<?php
namespace Gm\LandingPageEngine\TwigGlobals;

class VersionString
{
    /**
     * @var string
     */
    protected $versionString;

    public function __construct($versionString)
    {
        $this->versionString = $versionString;
    }

    public function  __toString()
    {
        return urlencode($this->versionString);
    }
}
