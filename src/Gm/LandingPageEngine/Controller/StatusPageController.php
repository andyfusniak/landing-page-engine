<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;

class StatusPageController extends AbstractController
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
        ob_start();
        include __DIR__ . '/../../../../views/status-page/show.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
