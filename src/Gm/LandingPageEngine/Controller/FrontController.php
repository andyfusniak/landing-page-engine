<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;

class FrontController extends AbstractController
{
    /**
     * @var LpEngine
     */
    private $lpEngine;

    public function __construct(LpEngine $lpEngine)
    {
        $this->lpEngine = $lpEngine;
    }

    public function showAction()
    {
        $twigEnv = $this->lpEngine->getTwigEnv();
        $template = $twigEnv->load($this->match['template']);
        return $template->render(
            $this->lpEngine->getTwigTags()
        );
    }
}
