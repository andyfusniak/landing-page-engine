<?php
namespace Gm\LandingPageEngine\Service;

use Monolog\Logger;
use Gm\LandingPageEngine\Mapper\TableMapper;
use Symfony\Component\HttpFoundation\Session\Session;

class CaptureService
{
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
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var TableMapper
     */
    protected $tableMapper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Logger  $logger application logger
     * @param array   $config global configuration for LP Engine
     * @param Session $session session instance used for multi-page flow
     */
    public function __construct(Logger $logger, array $config, Session $session)
    {
        $this->logger  = $logger;
        $this->config  = $config;
        $this->session = $session;
    }

    public function save(array $params, array $themeConfig)
    {
        // check the HTTP POST contains a form_name
        // otherwise there is no way to lookup the mappings
        // for the form field to the database fields
        if ((isset($params['form_name']))
            && (mb_strlen($params['form_name']) > 0)) {
            $this->logger->debug(sprintf(
                'Processing HTTP POST form_name=%s',
                $params['form_name']
            ));
        } else {
            $this->logger->error(
                'HTTP POST called without passing form_name parameter'
            );
            throw new \Exception(
                'HTTP POST called without passing form_name parameter'
            );
        }

        // make sure the form_name given exists in the theme JSON config
        // The theme.json file must contain form field name to database field
        // name mappings grouped by form_name
        if (!isset($themeConfig['forms'])) {
            $this->logger->error(
                'HTTP POST called but "theme.json" contains no form section'
            );
            throw new \Exception(
                'HTTP POST called but "theme.json" contains no form section'
            ); 
        }

        $this->logger->debug(sprintf(
            'Scanning theme.json forms section to find a match for %s',
            $params['form_name']
        ));

        $formNameMatch = false;
        foreach ($themeConfig['forms'] as $entry) {
            if (!isset($entry['form_name'])) {
                $this->logger->error(
                    'Missing form_name entry from theme.json file'
                );
                throw new \Exception(
                    'Missing form_name entry from theme.json file'
                );
            }
            $this->logger->debug(sprintf(
                'Checking theme.json config form_name=%s',
                $entry['form_name']
            ));
            if ($params['form_name'] === $entry['form_name']) {
                $this->logger->debug(sprintf(
                    'Found a match for form_name=%s',
                    $params['form_name']
                ));
                $formNameMatch = true;
                $tableName = $entry['table'];
                $mappings = $entry['mappings'];
                break;
            }
        }

        if (false === $formNameMatch) {
            $this->logger->error(sprintf(
                'Cannot find a definition for form_name=%s whilst scanning theme.json forms section',
                $params['form_name']
            ));
            throw new \Exception(sprintf(
                'Cannot find a definition for form_name=%s whilst scanning theme.json forms section',
                $params['form_name']
            ));
        }


        // build a lookup table from database column name to form field value
        $lookup = [];
        $formFieldColumns = [];
        foreach ($mappings as $entry) {
            foreach ($entry as $formFieldName => $databaseColumnName) {
                if (isset($params[$formFieldName])) {
                    $lookup[$databaseColumnName] = $params[$formFieldName];
                } else {
                    $this->logger->warning(sprintf(
                        'Form field "%s" is defined in the theme.json mappings for form_name=%s but is has no value passed from the template form',
                        $formFieldName,
                        $params['form_name']
                    ));
                }
                $formFieldColumns[] = $formFieldName;
            }
        }
        
        // no mapping to database fields.  Without this check the values
        // would silently be lost and never catpured to the database
        foreach (array_keys($params) as $formFieldName) {
            // skip system fields
            if (in_array($formFieldName, ['nexturl', 'form_name'])) {
                continue;
            }

            $this->logger->debug(sprintf(
                'Checking that form field %s has a mapping entry in the theme.json file for section form_name=%s',
                $formFieldName,
                $params['form_name']
            ));

            if (!in_array($formFieldName, $formFieldColumns)) {
                $this->logger->error(sprintf(
                    'Form field %s is used in the template form but has no mapping entry in the theme.json file',
                    $formFieldName
                ));
                throw new \Exception(sprintf(
                    'Form field %s is used in the template form but has no mapping     entry in the theme.json file.  Edit your theme.json file to include the missing field name.',
                    $formFieldName
                ));
            }
        }

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

        // add the session id to the sql parameters
        // every insert will use the session id, or null is not set
        if ((isset($this->session)) && ($this->session instanceof Session)) {
            $lookup['session_id'] = $this->session->getId();
        } else {
            $lookup['session_id'] = null;
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
            $mapper->insert($tableName, $lookup);
        } else {
            $mapper->update($tableName, $lookup);
        }
    }

    public function getTableMapper()
    {
        if (null === $this->tableMapper) {
            try {
                $pdo = new \PDO(
                    'mysql:host=' . $this->config['db']['dbhost'] . ';dbname='
                    . $this->config['db']['dbname'],
                    $this->config['db']['dbuser'],
                    $this->config['db']['dbpass']
                );
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                throw $e;
            } catch (\Exception $e) {
                throw $e;
            }
            $this->tableMapper = new TableMapper($pdo);
        }
        return $this->tableMapper;
    }
}
