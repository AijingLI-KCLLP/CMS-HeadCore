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
        foreach ($this as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }

    public function extractColumns(): array {
        $classAnnotationsDump = AnnotationReader::extractFromClass($this::class);

        $columns = [];

        $propertiesWithColumnAnnotation = $classAnnotationsDump->getPropertiesWithAnnotation(Column::class);
        foreach($propertiesWithColumnAnnotation as $property) {
            if($property->hasAnnotation(Id::class) === true) {
                continue;
            }
            $propertyName = $property->getName();
            if($property->getAnnotation(Column::class)->name !== null) {
                $propertyName = $property->getAnnotation(Column::class)->name;
            }

            $columns[] = $propertyName;
        }

        return $columns;
    }

    public function getValues() {
        $columns = $this->extractColumns();

        $array = [];
        
        foreach($this as $key => $value) {
            if(in_array($key, $columns)) {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}

?>
