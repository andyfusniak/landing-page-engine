<?php
namespace Gm\LandingPageEngine\Service;

use Monolog\Logger;
use Gm\LandingPageEngine\LpEngine;
use Gm\LandingPageEngine\Config\ThemeConfig;
use Gm\LandingPageEngine\Mapper\TableMapper;
use Gm\LandingPageEngine\Service\PdoService;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class CaptureService
{
    const STAGE          = 'stage';
    const REQUEST_SCHEME = 'request_scheme';
    const HTTP_HOST      = 'http_host';
    const THEME          = 'theme';
    const ROUTE_CONFIG   = 'route_config';
    const USER_AGENT     = 'user_agent';
    const HTTP_REFERER   = 'http_referer';
    const REMOTE_ADDR    = 'remote_addr';

    const UTM_SOURCE   = 'utm_source';
    const UTM_MEDIUM   = 'utm_medium';
    const UTM_TERM     = 'utm_term';
    const UTM_CONTENT  = 'utm_content';
    const UTM_CAMPAIGN = 'utm_campaign';

    protected static $utmTrackingTags = [
        self::UTM_SOURCE,
        self::UTM_MEDIUM,
        self::UTM_TERM,
        self::UTM_CONTENT,
        self::UTM_CAMPAIGN
    ];

    /**
     * @var LpEngine
     */
    protected $lpEngine;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var PdoService
     */
    protected $pdoService;

    /**
     * @var TableMapper
     */
    protected $tableMapper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Logger  $logger application logger
     * @param array   $config global configuration for LP Engine
     * @param Session $session session instance used for multi-page flow
     */
    public function __construct(LpEngine $lpEngine)
    {
        $this->lpEngine   = $lpEngine;
        $this->logger     = $lpEngine->getLogger();
        $this->pdoService = $lpEngine->getPdoService();
        $this->session    = $lpEngine->getSession();
        $this->request    = $lpEngine->getRequest();
    }

    public function save(array $params, ThemeConfig $themeConfig)
    {
        // check the HTTP POST contains a _form
        // otherwise there is no way to lookup the mappings
        // for the form field to the database fields
        if ((isset($params['_form']))
            && (mb_strlen($params['_form']) > 0)) {
            $this->logger->debug(sprintf(
                '%s +%s in %s Processing HTTP POST _form="%s"',
                __FILE__,
                __LINE__,
                __METHOD__,
                $params['_form']
            ));
        } else {
            $this->logger->error(sprintf(
                '%s +%s in %s HTTP POST called without passing _form parameter',
                __FILE__,
                __LINE__,
                __METHOD__
            ));
            throw new \Exception(
                'HTTP POST called without passing _form parameter'
            );
        }

        // make sure the _form given exists in the theme JSON config
        // The theme.json file must contain form field name to database field
        // name mappings grouped by _form
        $formConfigCollection = $themeConfig->getFormConfigCollection();
        if (null === $formConfigCollection) {
            $this->logger->error(sprintf(
                'HTTP POST called for form "%s", but theme config file contains no "forms" section',
                $params['_form']
            ));
            throw new \Exception(sprintf(
                'HTTP POST called for form "%s", but theme config file contains no "forms" section',
                $params['_form']
            ));
        }

        if (count($formConfigCollection) < 1) {
            throw new \Exception(sprintf(
                'HTTP POST called for form "%s", but theme config file contains no form definitions',
                $params['_form']
            ));
        }


        $this->logger->debug(sprintf(
            'Scanning theme confifg file forms section to find a match for %s',
            $params['_form']
        ));

        $formNameMatch = false;

        $formConfig = $formConfigCollection->getFormConfigByName($params['_form']);
        if (null === $formConfig) {
            $this->logger->error(sprintf(
                'Cannot find a definition for _form=%s in theme config forms section',
                $params['_form']
            ));
            throw new \Exception(sprintf(
                'Cannot find a definition for _form=%s in theme config forms section',
                $params['_form']
            ));
        }

        $tableName = $formConfig->getDbTable();
        if (empty($tableName)) {
            throw new \Exception(sprintf(
                'Form "%s" is missing a "dbtable"',
                $formName
            ));
        }

        $fields = $formConfig->getFieldsConfigCollection();

        // build a lookup table from database column name to form field value
        $lookup = [];
        $formFieldColumns = [];
        foreach ($fields as $fieldConfig) {
            $formFieldName = $fieldConfig->getName();
            $dbColumn = $fieldConfig->getDbColumn();
            if (empty($dbColumn)) {
                throw new \Exception(sprintf(
                    'Form field "%s" does not contain a "dbcolumn" entry in the theme file',
                    $formFieldName
                ));
            }

            if (isset($params[$formFieldName])) {
                $lookup[$dbColumn] = $params[$formFieldName];
            } else {
                $this->logger->warning(sprintf(
                    'Form field "%s" is defined in the theme mappings for _form=%s but is has no value passed from the template form',
                    $formFieldName,
                    $params['_form']
                ));
            }
            $formFieldColumns[] = $formFieldName;
        }

        // no mapping to database fields.  Without this check the values
        // would silently be lost and never catpured to the database
        foreach (array_keys($params) as $formFieldName) {
            // skip system fields
            if (in_array($formFieldName, ['_form', '_url', '_nexturl'])) {
                continue;
            }

            $this->logger->debug(sprintf(
                'Checking that form field %s has a mapping entry in the theme config file for section _form=%s',
                $formFieldName,
                $params['_form']
            ));

            if (!in_array($formFieldName, $formFieldColumns)) {
                $this->logger->warning(sprintf(
                    'Form field "%s" is used in the template form "%s", but has no mapping entry in the theme config file',
                    $formFieldName,
                    $params['_form']
                ));
            }
        }


        // add the session id to the sql parameters
        // every insert will use the session id, or null is not set
        if ((isset($this->session)) && ($this->session instanceof Session)) {
            $lookup['session_id'] = $this->session->getId();
        } else {
            $lookup['session_id'] = null;
        }

        $applicationConfig = $this->lpEngine->getApplicationConfig();
        $developerMode = $applicationConfig->getDeveloerMode();
        $noCapture     = $applicationConfig->getNoCapture();

        if ((true === $developerMode) &&
            (true === $noCapture)) {
            $this->logger->warning(sprintf(
                'developer_mode=%s and no_capture=%s so data catpure is switch off',
                (true === $developerMode) ? 'true' : 'false',
                (true === $noCapture) ? 'true' : 'false'
            ));
            return;
        }

        $mapper = $this->getTableMapper();

        if (null === $lookup['session_id']) {
            $row = null;
        } else {
            $row = $mapper->findRowBySessionId(
                $tableName,
                $lookup['session_id']
            );
            if (false === $row) {
                $row = null;
            }
        }

        // if there is no PHPSESSID associated to any row in the DB
        // then we should be inserting a new row for this data form capture
        // otherwise we are in the same web session and the end-user is
        // reposting the capture data, or on a multi-page landing site
        if (null === $row) {
            // automatically save the UTM tracking in the datbase
            if ((null !== $this->session)
                && ($this->session instanceof Session)
                && (null !== $this->session->get('initial_query_params'))) {
                $queryParams = $this->session->get('initial_query_params');
                foreach (self::$utmTrackingTags as $utmTag) {
                    if (isset($queryParams[$utmTag]) && (strlen($queryParams[$utmTag]) > 0)) {
                        $lookup[$utmTag] = $queryParams[$utmTag];
                    } else {
                        $lookup[$utmTag] = null;
                    }
                }
            }

            // add the stage, request_scheme, http_host, theme, route_config
            // user_agent, referer and remote_addr fields.  These only need
            // to be inserted once and are not updated.
            $lookup[self::STAGE]          = 1;
            $lookup[self::REQUEST_SCHEME] = $this->request->getScheme();
            $lookup[self::HTTP_HOST]      = $this->request->getHost();
            $lookup[self::THEME]          = $themeConfig->getThemeName()
                                          . ' ' . $themeConfig->getThemeVersion();
            $lookup[self::ROUTE_CONFIG]   = json_encode($themeConfig->getRoutes(),
                                                        JSON_UNESCAPED_SLASHES);
            $lookup[self::USER_AGENT]     = $this->request->server->get('HTTP_USER_AGENT');
            $lookup[self::HTTP_REFERER]   = $this->session->get('ARRIVAL_HTTP_REFERER');
            $lookup[self::REMOTE_ADDR]    = $this->request->getClientIp();

            $mapper->insert($tableName, $lookup);
        } else {
            $mapper->update($tableName, $lookup);
        }
    }

    public function getTableMapper()
    {
        if (null === $this->tableMapper) {
            $pdo = $this->pdoService->getPdoObject();
            $this->tableMapper = new TableMapper($this->logger, $pdo);
        }
        return $this->tableMapper;
    }
}
