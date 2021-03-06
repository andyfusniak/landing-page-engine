<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;
use Gm\LandingPageEngine\Service\CaptureService;
use Monolog\Logger;

class FormController extends AbstractController
{
    const AJAX_VALIDATION_ERROR = 9000;

    /**
     * @var LpEngine
     */
    protected $lpEngine;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $themeConfig;

    public function __construct(LpEngine $lpEngine)
    {
        $this->lpEngine    = $lpEngine;
        $this->themeConfig = $lpEngine->getThemeConfigService()->getThemeConfig();
        $this->logger      = $lpEngine->getLogger();
    }

    public function postAction()
    {
        $host = $this->request->getHost();
        $postParams = $this->request->request->all();

        $this->logger->debug(sprintf(
            'HTTP POST parameters %s',
            var_export($postParams, true)
        ));
        $stage = isset($this->match['stage']) ? $this->match['stage'] : 1;

        $formName = $postParams['_form'];

        // if the form has no _next field, then there is
        // nowhere to go next and this is a problem
        if (null === $this->request->get('_next')) {
            $this->logger->error(sprintf(
                '%s +%s in %s : Form "%s" has failed to HTTP POST the _next parameter',
                __FILE__,
                __LINE__,
                __METHOD__,
                $formName
            ));
            throw new \Exception(sprintf(
                'HTML Form "%s" has no <input type="hidden" name="_next" value="..."> field set.  '
                . 'There is no route name to redirect to after the HTTP POST.',
                $formName
            ));
        }

        $filterAndValidatorLookup = $this->lpEngine->loadFiltersAndValidators(
            $formName
        );

        if (null === $this->themeConfig->getFormConfigCollection()) {
            throw new \Exception(sprintf(
                'Template attemtped HTTP POST but theme config is missing a '
                . '<forms> section for form "%s"',
                $formName
            ));
        }

        $customParams = [];
        foreach ($postParams as $name => $value) {
            if ('_' === substr($name, 0, 1)) {
                continue;
            }
            $customParams[$name] = $value;
        }

        if (null === $filterAndValidatorLookup) {
            if (($count = count($customParams)) > 0) {
                $this->logger->warning(sprintf(
                    '%s custom params sent via HTTP POST but theme config indicates this should '
                    . ' be an empty form.  See fields sent below:',
                    $count
                ));
                foreach ($customParams as $name => $value) {
                    $this->logger->warning(sprintf(
                        'HTTP POST name="%s" value="%s" but no map for this field',
                        $name,
                        $value
                    ));
                }
            }

            $this->lpEngine->getCaptureService()->advanceStage($host, $stage + 1);
            $this->redirectRoute($this->request->get('_next'));
            return;
        }

        $formErrors = false;
        $errors = [];
        $this->logger->debug(sprintf(
            '%s +%s in %s : Begin applying filters and checking validator chains',
            __FILE__,
            __LINE__,
            __METHOD__
        ));

        foreach ($customParams as $name => $value) {
            $originalValue = $value;

            if (isset($filterAndValidatorLookup[$name])) {
                // check this form element has a filter chain
                // and if it does, then run through the filters
                if (isset($filterAndValidatorLookup[$name]['filters'])) {
                    $filterChain = $filterAndValidatorLookup[$name]['filters'];

                    // checkbox and radio boxes use arrays
                    // if the value is not a string it's likely a checkbox
                    // so we will not run the filters on it
                    if (is_string($value)) {
                        $value = $filterChain->filter($value);

                        $this->logger->debug(sprintf(
                            'Filter chain %s on field %s returned "%s"',
                            (string) $filterChain,
                            $name,
                            $value
                        ));

                        $postParams[$name] = $value;
                    }
                }

                if (isset($filterAndValidatorLookup[$name]['validators'])) {
                    $validatorChain = $filterAndValidatorLookup[$name]['validators'];

                    if ((strlen($value) < 1) && (true === $filterAndValidatorLookup[$name]['optional'])) {
                        $this->logger->info(sprintf(
                            'Form "%s" field "%s" is optional and has empty field value so skipping validator chain',
                            $formName,
                            $name
                        ));
                    } else {
                        $validatorChainResult = $validatorChain->isValid($value);
                        $this->logger->debug(sprintf(
                            'Validation chain %s on field %s returned %s for value="%s"',
                            (string) $validatorChain,
                            $name,
                            (true === $validatorChainResult) ? 'VALID' : 'INVALID',
                            $value
                        ));

                        if (false === $validatorChainResult) {
                            $formErrors = true;
                            $errors[$name] = $validatorChain->getMessages();

                            foreach ($errors[$name] as $msg) {
                                $this->logger->debug(sprintf(
                                    'Adding error message "%s" for form field "%s" on form "%s"',
                                    $msg,
                                    $name,
                                    $formName
                                ));
                            }
                            $this->lpEngine->addTwigGlobal(
                                $name . '_err',
                                true
                            );
                            $this->lpEngine->addTwigGlobal(
                                $name . '_errors', array_values($errors[$name])
                            );
                        } else {
                            if ('phone' === $name) {
                                // special case to remove leading 0 from a phone number
                                $postParams['phone'] = ltrim($postParams['phone'], '0');
                            }
                        }
                    }

                }
            }

            $this->lpEngine->addTwigGlobal($name, $originalValue);
        }

        // if the form is invalid
        if (true === $formErrors) {
            $twigEnv = $this->lpEngine->getTwigEnv();

            // HTTP POST routes have a '_post' postfix that needs removing
            $route = substr(
                $this->match['_route'],
                0,
                strlen($this->match['_route']) - strlen('_post')
            );
            $routeObj = $this->themeConfig->getRouteByUrl($route);
            $template = $twigEnv->load($routeObj->getTarget());

            // add {{ is_http_post }}
            $this->lpEngine->addTwigGlobal('is_http_post', true);

            if (($next = $this->request->get('_next')) === 'no-redirect') {
                $this->logger->debug(
                    'AJAX mode returning JSON string in jsonapi.org format'
                );
                $this->response->headers->set('Content-Type', 'application/vnd.api+json');
                $this->response->setStatusCode(422);

                $payload = [
                    'data' => [
                        'errors' => [
                            'status' => 422,
                            'code'   => self::AJAX_VALIDATION_ERROR,
                            'detail' => $errors
                        ],
                        'attributes' => $postParams
                    ]
                ];
                return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                return $template->render(
                    $this->lpEngine->getTwigTags()
                );
            }
        }

        $lastInsertId = $this->lpEngine->getCaptureService()->save(
            $host,
            $stage,
            $postParams,
            $this->themeConfig
        );

        if (($next = $this->request->get('_next')) === 'no-redirect') {
            $this->response->headers->set('Content-Type', 'application/vnd.api+json');
            $this->response->setStatusCode(201);
            $payload = [
                'data' => [
                    'type' => 'leads',
                    'id'   => $lastInsertId,
                    'attributes' => $postParams
                ]
            ];
            return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $this->redirectRoute($next);
    }

    private function redirectRoute($next)
    {
        // developer redirects start don't start with / or http
        if (('/' === substr($next, 0, 1)) ||
            ('http' === substr($next, 0, 4))) {
            $this->redirectToUrl($next);
        } else if (null !== ($nextRoute = $this->themeConfig->getRouteByName($next))) {
            $this->redirectToUrl($nextRoute->getUrlWithPrefix());
        } else {
            throw new \Exception(sprintf(
                '%s cannot find route or url, _next="%s"',
                __METHOD__,
                $next
            ));
        }
    }
}
