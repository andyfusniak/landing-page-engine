<?php
namespace Gm\LandingPageEngine\Mapper;

use Monolog\Logger;

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
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));
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

    public function fetchLastNRowsAssocArray($tableName, $number = 5)
    {
        $sql = 'SELECT * FROM ' . $tableName . ' LIMIT ' . strval($number);
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $columns = array_keys($sqlFieldMap);
        if (($key = array_search('session_id', $columns)) !== false) {
            unset($columns[$key]);
        }

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
        $statement = $this->pdo->prepare($sql);

        foreach ($sqlFieldMap as $columnName => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $statement->bindValue(':' . $columnName, $value, \PDO::PARAM_STR);
        }
        $statement->execute();
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));
    }
}
