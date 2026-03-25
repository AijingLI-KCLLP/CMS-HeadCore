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
        $this->query->execute($this->queryBuilder->getParams());
        return $this;
    }

    public function getOneResult(): AbstractEntity|false {
        $row = $this->query->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return false;
        }
        return $this->entity->getName()::hydrate($row);
    }

    public function getAllResults(): array {
        $rows = $this->query->fetchAll(\PDO::FETCH_ASSOC);
        $entityClass = $this->entity->getName();
        return array_map(fn($row) => $entityClass::hydrate($row), $rows);
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
            ->addParam('id', $entity->getId())
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

    public function raw(string $sql, array $params = []): array {
        $this->getQueryBuilder()->raw($sql, $params);
        return $this->executeQuery()->getAllResults();
    }

    public function paginate(int $page, int $perPage = 10, array $criteria = []): array {
        $offset = ($page - 1) * $perPage;

        $this->getQueryBuilder()
            ->build()
            ->select()
            ->from()
            ->addWhereAccordingToCriterias($criteria)
            ->limit($perPage)
            ->offset($offset)
        ;

        $items = $this->executeQuery()->getAllResults();

        $this->getQueryBuilder()
            ->build()
            ->count()
            ->from()
            ->addWhereAccordingToCriterias($criteria)
        ;
        $total = (int) $this->db->getConnexion()
            ->query($this->queryBuilder->getQueryString())
            ->fetchColumn();

        return [
            'data'        => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }
}
