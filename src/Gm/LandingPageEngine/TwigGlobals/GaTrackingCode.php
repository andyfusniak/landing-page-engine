<?php
namespace Gm\LandingPageEngine\TwigGlobals;

use Gm\LandingPageEngine\Entity\DeveloperProfile;
use Symfony\Component\HttpFoundation\Session\Session;

class GaTrackingCode
{
    /**
     * @var DeveloperProfile
     */
    protected $developerProfile;

    /**
     * @var Session
     */
    protected $session;

    public function __construct(DeveloperProfile $developerProfile, Session $session)
    {
        $this->developerProfile = $developerProfile;
        $this->session = $session;
    }

    public function  __toString()
    {
        // http://localhost/?utm_source=google&utm_medium=test+medium&utm_term=test_term&utm_content=content&utm_campaign=test_campaign
        $themeSettings = $this->developerProfile->getThemeSettings();
        $gaTrackingId = isset($themeSettings['ga-tracking-id'])
            ? $themeSettings['ga-tracking-id'] : null;
        if (null === $gaTrackingId) {
            return '';
        }


        $gaHeader = "
        <!-- Google Analytics -->
        <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '" . $gaTrackingId . "', 'auto');
        ";

        //ga('set', 'campaignName', 'campaign_a');
        //ga('set', 'campaignSource', 'tomato');
        //ga('set', 'campaignMedium', 'apple');

        $gaFooter = "
        ga('send', 'pageview');
        </script>
        <!-- End Google Analytics -->
        ";

        // the query parameters at the time the prospect first arrived to the site
        $initialQueryParams = $this->session->get('initial_query_params');

        $components = [
            "ga('create', '" . urlencode($gaTrackingId) . "', 'auto');"
        ];

        $utmTagsToGa = [
            'utm_source'   => 'campaignName',
            'utm_medium'   => 'campaignMedium',
            'utm_term'     => 'campaignKeyword',
            'utm_content'  => 'campaignContent',
            'utm_campaign' => 'campaignName'
        ];
        foreach ($utmTagsToGa as $utmTag => $ga) {
            if (isset($initialQueryParams[$utmTag])) {
                array_push(
                    $components,
                    "ga('set', '" . $ga . "', '" . urlencode($initialQueryParams[$utmTag]) . "');"
                );
            }
        }

        $result = $gaHeader . PHP_EOL;
        foreach ($components as $component) {
            $result .= $component . PHP_EOL;
        }
        $result .= $gaFooter . PHP_EOL;

        return $result;
    }
}
