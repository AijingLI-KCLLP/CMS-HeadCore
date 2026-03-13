<?php

namespace Core\Entities;

use Core\Annotations\ORM\Column;
use Core\Annotations\AnnotationReader;
use Core\Annotations\ORM\Id;

abstract class AbstractEntity {

    abstract public function getId(): int | string;

    /**
     * Hydrate an entity from a DB row array.
     * Maps DB column names to PHP properties using #[Column(name: '...')] or direct property name.
     *
     * Convention:
     *   - #[Column(name: 'created_at')] → maps key 'created_at' to property
     *   - no name → maps key matching property name
     */
    public static function hydrate(array $data): static {
        $entity = new static();
        $reflection = new \ReflectionClass($entity);
        $dump = AnnotationReader::extractFromClass(static::class);

        foreach ($reflection->getProperties() as $property) {
            $propertyDump = null;
            foreach ($dump->getProperties() as $p) {
                if ($p->getName() === $property->getName()) {
                    $propertyDump = $p;
                    break;
                }
            }

            $columnName = $property->getName();
            if ($propertyDump !== null && $propertyDump->hasAnnotation(Column::class)) {
                $col = $propertyDump->getAnnotation(Column::class);
                if ($col->name !== null) {
                    $columnName = $col->name;
                }
            }

            if (array_key_exists($columnName, $data)) {
                $property->setAccessible(true);
                $property->setValue($entity, $data[$columnName]);
            }
        }

        return $entity;
    }

    public function toArray(): array {
        $array = [];
        $reflection = new \ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            if ($property->isInitialized($this)) {
                $array[$property->getName()] = $property->getValue($this);
            }
        }
        return $array;
    }

    public function extractColumns(): array {
        $classAnnotationsDump = AnnotationReader::extractFromClass($this::class);
        $reflection = new \ReflectionClass($this);

        $columns = [];

        foreach ($classAnnotationsDump->getPropertiesWithAnnotation(Column::class) as $property) {
            if ($property->hasAnnotation(Id::class)) {
                continue;
            }

            $prop = $reflection->getProperty($property->getName());
            $prop->setAccessible(true);
            if (!$prop->isInitialized($this)) {
                continue;
            }

            $colAnnotation = $property->getAnnotation(Column::class);
            $columns[] = $colAnnotation->name ?? $property->getName();
        }

        return $columns;
    }

    public function getValues(): array {
        $classAnnotationsDump = AnnotationReader::extractFromClass($this::class);
        $reflection = new \ReflectionClass($this);

        $array = [];

        foreach ($classAnnotationsDump->getPropertiesWithAnnotation(Column::class) as $propDump) {
            if ($propDump->hasAnnotation(Id::class)) {
                continue;
            }

            $colAnnotation = $propDump->getAnnotation(Column::class);
            $columnName = $colAnnotation->name ?? $propDump->getName();

            $prop = $reflection->getProperty($propDump->getName());
            $prop->setAccessible(true);

            if ($prop->isInitialized($this)) {
                $array[$columnName] = $prop->getValue($this);
            }
        }

        return $array;
    }
}

?>
