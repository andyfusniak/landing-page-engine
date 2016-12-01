<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;
use Gm\LandingPageEngine\Service\CaptureService;

class FormController extends AbstractController
{
    /**
     * @var LpEngine
     */
    protected $lpEngine;

    public function __construct(LpEngine $lpEngine)
    {
        $this->lpEngine = $lpEngine;
    }

    public function postAction()
    {
        $postParams = $this->request->request->all();

        $captureService = $this->lpEngine->getCaptureService();

        $captureService->save(
            $postParams,
            $this->lpEngine->getThemeConfig()
        );

        $nextUrl = $this->request->get('_nexturl');
        $this->redirectToUrl($nextUrl);
    }
}
