<?php
namespace Gm\LandingPageEngine\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $match;

    public function redirectToUrl($url)
    {
        $this->response = new RedirectResponse($url);
        $this->response->send();
        die();
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
