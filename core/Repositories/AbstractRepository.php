<?php

namespace Core\Repositories;

use Core\Database\DatabaseConnexion;
use Core\Database\Dsn;
use Core\Entities\AbstractEntity;
use Core\ORM\QueryBuilder;
use Core\ORM\QueryConditions;

abstract class AbstractRepository
{
    protected DatabaseConnexion $db;
    protected QueryBuilder $queryBuilder;
    protected \PDOStatement $query;
    protected \ReflectionClass $entity;

    public function __construct(string $entity)
    {
        if(class_exists($entity) === false || is_subclass_of($entity, AbstractEntity::class) === false) {
            throw new \Exception('Not a valid entity');
        }
        
        $this->entity = new \ReflectionClass($entity);
        
        $dsn = new Dsn();
        $dsn->addHostToDsn();
        $dsn->addPortToDsn();
        $dsn->addDbnameToDsn();
        
        $db = new DatabaseConnexion();
        $db->setConnexion($dsn);
        
        $this->db = $db;
    }

    public function getQueryBuilder(): QueryBuilder {
        $this->queryBuilder = new QueryBuilder($this->entity);
        return $this->queryBuilder;
    }

    public function executeQuery(): self {
        $this->query = $this->db->getConnexion()->prepare($this->queryBuilder->getQueryString());
        $this->query->setFetchMode(\PDO::FETCH_CLASS, $this->entity->getName());
        $this->query->execute($this->queryBuilder->getParams());
        return $this;
    }

    public function getOneResult(): AbstractEntity {
        return $this->query->fetch();
    }

    public function getAllResults(): array {
        return $this->query->fetchAll();
    }

    public function find(string | int $id) {
        return $this->findOneBy(['id' => $id]);
    }

    public function findAll(): array {
        return $this->findBy([]);
    }

    public function findBy(array $criteria) {
        $this->getQueryBuilder()
            ->build()
            ->select()
            ->from()
            ->addWhereAccordingToCriterias($criteria)
        ;

        return $this->executeQuery()
            ->getAllResults();
    }

    public function findOneBy(array $criteria) {
        $this->getQueryBuilder()
            ->build()
            ->select()
            ->from()
            ->addWhereAccordingToCriterias($criteria)
        ;

        $data = $this->executeQuery()
            ->getOneResult();

        if($data === false) {
            return null;
        }

        return $data;
    }

    public function save(AbstractEntity $entity): string {
        $this->getQueryBuilder()
            ->build()
            ->insert($entity)
            ->values($entity)
            ->setParams($entity->getValues())
        ;

        $this->executeQuery();
        return $this->db->getConnexion()->lastInsertId();
    }

    public function update(AbstractEntity $entity) {
        $this->getQueryBuilder()
            ->build()
            ->updateTable()
            ->set($entity)
            ->where('id', QueryConditions::EQ)
            ->setParams($entity->getValues())
        ;
    
        $this->executeQuery();
    }

    public function remove(AbstractEntity $entity) {
        $this->getQueryBuilder()
            ->build()
            ->delete()
            ->from()
            ->where('id', QueryConditions::EQ)
            ->addParam('id', $entity->getId())
        ;
    
        $this->executeQuery();
    }
}
