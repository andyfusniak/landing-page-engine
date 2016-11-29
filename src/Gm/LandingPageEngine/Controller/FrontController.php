<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FrontController extends Controller
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
        $template = $twigEnv->load($this->match['page']);
        return $template->render([
            'title' => 'Example title for our page',
            'menuitems' => ['pizza', 'lasagna', 'fruit cake', 'donut']
        ]);
    }

    public function setMatch(array $match)
    {
        $this->match = $match;
        return $this;
    }

    public function dispatch(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        list($service, $action) = preg_split('/:/', $this->match['_controller']);
        return $this->$action();
    }
}
