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

    public function updateTable(string $table): self {
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

        $this->queryString .= " SET";
        $this->queryString = implode(', ', $entity->extractColumns());

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

        $this->queryString .= "$field $condition :$field";
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

    public function getQueryString(): string {
        return $this->queryString;
    }

}

?>
