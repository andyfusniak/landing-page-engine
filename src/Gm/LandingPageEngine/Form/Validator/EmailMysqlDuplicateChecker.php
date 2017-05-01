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

class EmailMysqlDuplicateChecker implements DuplicateCheckerInterface
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
     * Check for duplicate email
     *
     * @param string $value the email to check
     * @return bool true if the phone number has been used before
     */
    public function isDuplicate($value)
    {
        return $this->captureService->isEmailDuplicate(
            ltrim($value, '0'), $this->host
        );
    }
}
