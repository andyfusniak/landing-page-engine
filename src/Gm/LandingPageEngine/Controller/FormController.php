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

        $this->lpEngine->loadFiltersAndValidators($postParams['_form']);

        $filterAndValidatorLookup = $this->lpEngine->getFieldToFilterAndValidatorLookup();

        $formErrors = false;
        $errors = [];
        foreach ($postParams as $name => $value) {
            $originalValue = $value;
            if ('_' !== substr($name, 0, 1)) {
                if (isset($filterAndValidatorLookup[$name])) {
                    $filterChain = $filterAndValidatorLookup[$name]['filters'];
                    $value = $filterChain->filter($value);

                    $validatorChain = $filterAndValidatorLookup[$name]['validators'];
                    
                    if (false === $validatorChain->isValid($value)) {
                        $formErrors = true;
                        $errors[$name] = $validatorChain->getMessages();
                        $this->lpEngine->addTwigGlobal($name . '_err', true);
                        $this->lpEngine->addTwigGlobal($name . '_errors', $errors[$name]);
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
