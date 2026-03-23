<?php

namespace Core\ORM;

use Core\Entities\AbstractEntity;

class QueryBuilder {
    private string $queryString;
    private \ReflectionClass $table;
    private string $tableAlias;
    private array $params = [];

    public function __construct(\ReflectionClass $table) {
        $this->table = $table;
    }
    
    public function build(): self {
        $this->queryString = "";
        return $this;
    }
    
    public function select(...$fields): self {
        $this->queryString .= "SELECT";

        if(count($fields) === 0) {
            $this->queryString .= ' *';
            return $this;
        }

        $this->queryString .= ' ' . implode(', ', $fields);
        return $this;
    }

    public function insert(AbstractEntity $entity): self {
        $this->queryString .= "INSERT INTO ";
        $this->queryString .= $this->table->getShortName();
        $this->queryString .= ' (';
        $this->queryString .= implode(', ', $entity->extractColumns());
        $this->queryString .= ') ';
        
        return $this;
    }

    public function values(AbstractEntity $entity): self {
        $this->queryString .= "VALUES(";

        foreach($entity->extractColumns() as $column) {
            $this->queryString .= ":$column,";
        }

        $this->queryString = rtrim($this->queryString, ',');
        
        $this->queryString .= ') ';
        
        return $this;
    }

    public function delete(): self {
        $this->queryString .= "DELETE";
        return $this;
    }

    public function updateTable(?string $table = null): self {
        $table = $table ?? $this->table->getShortName();
        $this->queryString .= "UPDATE $table";
        return $this;
    }
    
    public function from(string | null $tableAlias = null): self {
        $table = $this->table->getShortName();
        $this->queryString .= " FROM $table";

        if($tableAlias !== null) {
            $this->as($tableAlias);
        }

        return $this;
    }
    
    public function set(AbstractEntity $entity): self {
        $pairs = array_map(fn($col) => "$col = :$col", $entity->extractColumns());
        $this->queryString .= " SET " . implode(', ', $pairs);
        return $this;
    }
    
    public function as(string $tableAlias): self {
        $this->queryString .= " AS $tableAlias";
        $this->tableAlias = $tableAlias;
        return $this;
    }

    public function andWhere(string $field, QueryConditions $condition, ?string $table = null): self {
        $this->queryString .= " AND  ";
        return $this->where($field, $condition, $table);
    }

    public function orWhere(string $field, QueryConditions $condition, ?string $table = null): self {
        $this->queryString .= " OR  ";
        return $this->where($field, $condition, $table);
    }

    public function where(string $field, QueryConditions $condition, ?string $table = null): self {
        $this->queryString .= " WHERE ";
        if($table !== null) {
            $this->queryString .= "$table.";
        }else {
            $this->queryString .= "$this->tableAlias.";
        }

        $this->queryString .= "$field {$condition->value} :$field";
        return $this;
    }

    public function addParam(string $key, $value): self {
        $this->params[$key] = $value;
        return $this;
    }

    public function setParams(array $params): self {
        $this->params = $params;
        return $this;
    }

    public function getParams(): array {
        return $this->params;
    }
    
    public function addWhereAccordingToCriterias(array $criterias) {
        foreach($criterias as $key => $value) {
            if(strpos($this->queryString, 'WHERE') === false) {
                $this->where($key, QueryConditions::EQ);
            } else {
                $this->andWhere($key, QueryConditions::EQ);
            }
            $this->addParam($key, $value);
        }
    }

    public function orderBy(string $field, string $direction = 'ASC'): self {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->queryString .= " ORDER BY $field $direction";
        return $this;
    }

    public function limit(int $limit): self {
        $this->queryString .= " LIMIT $limit";
        return $this;
    }

    public function offset(int $offset): self {
        $this->queryString .= " OFFSET $offset";
        return $this;
    }

    public function count(string $field = '*'): self {
        $this->queryString .= "SELECT COUNT($field)";
        return $this;
    }

    public function innerJoin(string $table, string $on): self {
        $this->queryString .= " INNER JOIN $table ON $on";
        return $this;
    }

    public function leftJoin(string $table, string $on): self {
        $this->queryString .= " LEFT JOIN $table ON $on";
        return $this;
    }

    public function groupBy(string $field): self {
        $this->queryString .= " GROUP BY $field";
        return $this;
    }

    public function raw(string $sql, array $params = []): self {
        $this->queryString = $sql;
        $this->params = $params;
        return $this;
    }

    public function debug(): string {
        $sql = $this->queryString;
        foreach ($this->params as $key => $value) {
            $value = is_string($value) ? "'$value'" : $value;
            $sql = str_replace(":$key", (string)$value, $sql);
        }
        return "SQL: $sql\nParams: " . json_encode($this->params, JSON_PRETTY_PRINT);
    }

    public function getQueryString(): string {
        return $this->queryString;
    }

}

?>
