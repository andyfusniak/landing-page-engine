<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;

class StatusPageController extends AbstractController
{
    /**
     * @var LpEngine
     */
    private $lpEngine;

    /**
     * @var StatusService
     */
    protected $statusService;

    public function __construct(LpEngine $lpEngine)
    {
        $this->lpEngine      = $lpEngine;
        $this->statusService = $lpEngine->getStatusService();
    }

    public function showAction()
    {
        // setup a private twig environment for the status page
        $loader = new \Twig_Loader_Filesystem(
            dirname(__FILE__) . '/../../../../views/status-page/'
        );
        $twigEnv = new \Twig_Environment($loader, [
            'debug'       => true,
            'cache'       => false,
            'auto_reload' => true,
        ]);

        $this->statusService->systemSettings();
        $this->statusService->landingPageEngine();

        $template = $twigEnv->load('show.html.twig');
        return $template->render(
            $this->lpEngine->getTwigTags()
        );
    }
}
