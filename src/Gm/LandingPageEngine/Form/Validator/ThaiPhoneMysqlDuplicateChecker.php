<?php
/**
 * Landing Page Engine
 *
 * @package Gm\LandingPageEngine
 * @subpackage Form\Validator
 * @link https://bitbucket.org/sudtanadevteam/landing-page-engine
 * @copyright Copyright (c) 2017
 * @author Andy Fusniak <andy@greycatmedia.co.uk>
 */
namespace Gm\LandingPageEngine\Form\Validator;
use Gm\LandingPageEngine\Service\CaptureService;

class ThaiPhoneMysqlDuplicateChecker implements DuplicateCheckerInterface
{
    /**
     * @var CaptureService
     */
    protected $captureService;

    /**
     * @var string
     */
    protected $host;

    public function __construct(CaptureService $captureService, $host)
    {
        $this->captureService = $captureService;
        $this->host = $host;
    }

    /**
     * Check for duplicate thai phone number
     *
     * @param string $value the phone number to check
     * @return bool true if the phone number has been used before
     */
    public function isDuplicate($value)
    {
        return $this->captureService->isPhoneDuplicate(ltrim($value, '0'), $this->host);
    }
}
