<?php
namespace Gm\LandingPageEngine\Mapper;

use Monolog\Logger;
use PDO;

class TableMapper
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(Logger $logger, \PDO $pdo)
    {
        $this->logger = $logger;
        $this->pdo = $pdo;
    }

    /**
     * @param array associative array of databaseColumn/values
     */
    public function insert($tableName, $sqlFieldMap)
    {
        $columns = array_keys($sqlFieldMap);
        $columnList = rtrim(implode(', ', $columns), ',');
        $columnPlaceHolders = ':' . implode(', :', $columns);
        $sql = '
            INSERT INTO ' . $tableName . ' (id, ' . $columnList
            . ') VALUES (NULL, ' . $columnPlaceHolders . ')';
        $statement = $this->pdo->prepare($sql);

        foreach ($sqlFieldMap as $columnName => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $statement->bindValue(':' . $columnName, $value, \PDO::PARAM_STR);
        }

        $statement->execute();
        $this->logger->debug(sprintf('SQL Query executed %s', $sql));
        return $this->pdo->lastInsertId();
    }

    /**
     * Retrieve a row using the unique key session_id.
     * The PHPSESSID is store when inserting a new row
     * to the database.  This function can be used to
     * determine if data has already been written during
     * the current web session.  If an insert has previously
     * been done, then the service layer can use update
     * to overwrite fields or capture missing columns
     *
     * @param string $tableName the database table
     * @param string $sessionId the PHPSESSID from the Session instance
     */
    public function findRowBySessionId($tableName, $sessionId)
    {
        $sql = 'SELECT id FROM ' . $tableName . ' WHERE session_id = :session_id';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $statement->execute();
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchRowByPhone(string $tableName, string $phone)
    {
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE phone = :phone';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':phone', $phone, \PDO::PARAM_STR);
        $statement->execute();
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchLastNRowsAssocArray($tableName, $number = 5)
    {
        $sql = 'SELECT * FROM ' . $tableName . ' ORDER BY id DESC LIMIT ' . strval($number);
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetchUnsyncedRows($tableName)
    {
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE klaviyo_sync = 0';

        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSyncedKlaviyo(string $tableName, int $id, int $state = 1)
    {
        $sql = 'UPDATE ' . $tableName . ' SET klaviyo_sync = :klaviyo_sync';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':klaviyo_sync', $state, PDO::PARAM_INT);
        $statement->execute();
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));

    }

    /**
     * Update a data capture table with the given column map
     *
     * @param string $tableName the name of the table
     * @param array associative array of column names and value to update
     */
    public function update($tableName, $sqlFieldMap)
    {
        // remove 'session_id' from the db columns as we do not
        // want to update this value.  instead we use it in the
        // SQL WHERE clause.
        if (isset($sqlFieldMap['session_id'])) {
            $sessionId = $sqlFieldMap['session_id'];
            unset($sqlFieldMap['session_id']);
        }

        if (isset($sqlFieldMap['stage'])) {
            $stage = $sqlFieldMap['stage'];
            unset($sqlFieldMap['stage']);
        }

        $columns = array_keys($sqlFieldMap);

        // after removing session_id, if there is nothing left then
        // there is nothing to update
        if (empty($columns)) {
            $this->logger->debug(sprintf(
                "%s:%s has no columns to write so returning *without* doing SQL UPDATE",
                __CLASS__,
                __METHOD__
            ));
            return;
        }

        $sql = 'UPDATE ' . $tableName . ' SET ';
        $count = count($columns);
        for ($i = 0; $i < $count - 1; $i++) {
            $colName = $columns[$i];
            $sql .= $colName . ' = :' . $colName . ', ';
        }
        if ($count > 0) {
            $colName = $columns[$count-1];
            $sql .= $colName . ' = :' . $colName;
        }
        $sql .= ' WHERE session_id = :session_id';
        $this->logger->debug(sprintf(
            'SQL Query Prior to execution: %s',
            $sql
        ));
        $statement = $this->pdo->prepare($sql);

        foreach ($sqlFieldMap as $columnName => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $statement->bindValue(':' . $columnName, $value, \PDO::PARAM_STR);
                $this->logger->debug(sprintf(
                    'Bind array (as json string) value for :%s as %s',
                    $columnName,
                    $value
                ));
            } else if (is_string($value)) {
                $statement->bindValue(':' . $columnName, $value, \PDO::PARAM_STR);
                $this->logger->debug(sprintf(
                    'Bind string value for :%s as %s',
                    $columnName,
                    $value
                ));
            } else if (is_int($value)) {
                $statement->bindValue(':' . $columnName, $value, \PDO::PARAM_INT);
                $this->logger->debug(sprintf(
                    'Bind int value for :%s as %s',
                    $columnName,
                    $value
                ));
            }
        }

        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $this->logger->debug(sprintf(
            'Bind string value for :session_id as %s',
            $sessionId
        ));

        $statement->execute();
        $this->logger->debug(sprintf('SQL Query executed %s', $sql));
    }

    public function advanceStage($dbTable, $sessionId, $newStage)
    {
        $sql = 'UPDATE ' . $dbTable
            . ' SET stage = :stage1'
            . ' WHERE session_id = :session_id AND stage < :stage2';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $statement->bindValue(':stage1', $newStage, \PDO::PARAM_INT);
        $statement->bindValue(':stage2', $newStage, \PDO::PARAM_INT);
        $statement->execute();
        $this->logger->debug(sprintf('SQL Query executed %s', $sql));
    }
}
