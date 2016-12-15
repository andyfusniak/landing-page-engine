<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;

use Symfony\Component\HttpFoundation\Request;

class RedirectController extends AbstractController
{
    /**
     * @var LpEngine
     */
    private $lpEngine;

    public function __construct(LpEngine $lpEngine)
    {
        $this->lpEngine = $lpEngine;
    }

    public function redirectAction()
    {
        $this->redirectToUrl($this->match['redirect_url']);

    }
}
