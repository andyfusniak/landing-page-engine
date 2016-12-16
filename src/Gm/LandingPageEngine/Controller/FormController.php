<?php
namespace Gm\LandingPageEngine\Controller;

use Gm\LandingPageEngine\LpEngine;
use Gm\LandingPageEngine\Service\CaptureService;
use Monolog\Logger;

class FormController extends AbstractController
{
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
        $this->themeConfig = $lpEngine->getThemeConfig();
        $this->logger      = $lpEngine->getLogger();
    }

    public function postAction()
    {

        $formName = $postParams['_form'];

        // if the form has no _nexturl field, then there is
        // nowhere to go next and this is a problem
        if (null === $this->request->get('_nexturl')) {
            $this->logger->error(sprintf(
                '%s +%s in %s : Form "%s" has failed to HTTP POST the _nexturl parameter to /process-post',
                __FILE__,
                __LINE__,
                __METHOD__,
                $formName
            ));
            throw new \Exception(sprintf(
                'HTML Form "%s" has no <input type="hidden" name="_nexturl" value="..."> field set.  There is no URL to redirect to after the HTTP POST is successful.',
                $formName
            ));
        }

        $postParams = $this->request->request->all();

        $formName = $postParams['_form'];
        $filterAndValidatorLookup = $this->lpEngine->loadFiltersAndValidators(
            $formName
        );

        if (!isset($this->themeConfig['forms'][$formName]['dbtable'])
            && (null === $filterAndValidatorLookup)) {
            $customParams = [];
            foreach ($postParams as $name => $value) {
                if ('_' === substr($name, 0, 1)) {
                    continue;
                }
                $customParams[$name] = $value;
            }

            $logger = $this->lpEngine->getLogger();
            if (($count = count($customParams)) > 0) {
                $logger->warning(sprintf(
                    '%s custom params sent via HTTP POST but theme config indicates this should be an empty form.  See fields sent below:',
                    $count
                ));
                foreach ($customParams as $name => $value) {
                    $logger->warning(sprintf(
                        'HTTP POST name="%s" value="%s" but no map for this field',
                        $name,
                        $value
                    ));
                }
            }

            $nextUrl = $this->request->get('_nexturl');
            $this->redirectToUrl($nextUrl);
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

        foreach ($postParams as $name => $value) {
            $this->logger->debug(sprintf(
                'HTTP POST parameters %s=%s',
                $name,
                $value
            ));

            $originalValue = $value;

            if ('_' !== substr($name, 0, 1)) {
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
                        }
                    }

                    if (isset($filterAndValidatorLookup[$name]['validators'])) {
                        $validatorChain = $filterAndValidatorLookup[$name]['validators'];

                        $validatorChainResult = $validatorChain->isValid($value);
                        $this->logger->debug(sprintf(
                            'Validation chain %s returned %s for value="%s"',
                            (string) $validatorChain,
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

                            $this->lpEngine->addTwigGlobal($name . '_err', true);
                            $this->lpEngine->addTwigGlobal($name . '_errors', array_values($errors[$name]));
                        }
                    }
                }

                $this->lpEngine->addTwigGlobal($name, $originalValue);
            }
        }

        // if the form is invalid
        if (true === $formErrors) {
            $twigEnv = $this->lpEngine->getTwigEnv();

            // HTTP POST routes have a '_post' postfix that needs removing
            $route = substr($this->match['_route'], 0, strlen($this->match['_route']) - strlen('_post'));

            $template = $this->themeConfig['routes'][$route];
            $template = $twigEnv->load($template);

            // add {{ is_http_post }}
            $this->lpEngine->addTwigGlobal('is_http_post', true);

            return $template->render(
                $this->lpEngine->getTwigTags()
            );
        }

        $captureService = $this->lpEngine->getCaptureService();

        $captureService->save(
            $postParams,
            $this->lpEngine->getThemeConfig()
        );

        $nextUrl = $this->request->get('_nexturl');
        $this->redirectToUrl($nextUrl);
    }
}
