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
       
        $formName = $postParams['_form'];
        $fieldToFilterAndValidatorLookup = $this->lpEngine->loadFiltersAndValidators(
            $formName
        );

        if (null === $filterAndValidatorLookup) {
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
        foreach ($postParams as $name => $value) {
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
                    
                        if (false === $validatorChain->isValid($value)) {
                            $formErrors = true;
                            $errors[$name] = $validatorChain->getMessages();
                            $this->lpEngine->addTwigGlobal($name . '_err', true);
                            $this->lpEngine->addTwigGlobal($name . '_errors', $errors[$name]);
                        }
                    }
                }
                
                $this->lpEngine->addTwigGlobal($name, $originalValue);
            }
        }

        // if the form is invalid
        if (true === $formErrors) {
            $twigEnv = $this->lpEngine->getTwigEnv();

            $themeConfig = $this->lpEngine->getThemeConfig();
            $template = $themeConfig['routes'][$postParams['_url']];
            $template = $twigEnv->load($template);

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
