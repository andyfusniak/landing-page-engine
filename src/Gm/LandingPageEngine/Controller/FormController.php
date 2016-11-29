<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;

class FormController extends AbstractController
{
    /**
     * @var LpEngine
     */
    private $lpEngine;

    public function __construct(LpEngine $lpEngine)
    {
        $this->lpEngine = $lpEngine;
    }

    public function postAction()
    {

        $nextUrl = $this->request->get('nexturl');
        $this->redirectToUrl($nextUrl);
    }
}
