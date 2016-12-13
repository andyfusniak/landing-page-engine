<?php
namespace Gm\LandingPageEngine\TwigGlobals;

use Symfony\Component\HttpFoundation\Session\Session;

class UtmQueryParams
{
    /**
     * @var Session
     */
    protected $session;

    public static $utmParams = [
        'utm_source',
        'utm_medium',
        'utm_term',
        'utm_content',
        'utm_campagn'
    ];

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function  __toString()
    {
        $queryString = '';
        $first = true;
        foreach (self::$utmParams as $name) {
            if (isset($this->session[$name])) {
                if (true === $first) {
                    $queryString .= $name . '=' . urlencode($this->session[$name]);
                    $first = false;
                } else {
                    $queryString .= '&' . $name . '=' . urlencode($this->session[$name]);
                }
            }
        }
        return $queryString;
    }
}
