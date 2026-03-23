<?php

namespace Core\Entities;

use Core\Annotations\ORM\Column;
use Core\Annotations\AnnotationReader;
use Core\Annotations\ORM\Id;

abstract class AbstractEntity {

    abstract public function getId(): int | string;
    
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
