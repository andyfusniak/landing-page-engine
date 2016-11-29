<?php
namespace Gm\LandingPageEngine\Mapper;

class TableMapper
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
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
            $statement->bindValue(':' . $columnName, $value, \PDO::PARAM_STR);
        }

        $statement->execute();
        return $this->pdo->lastInsertId();
    }
}
