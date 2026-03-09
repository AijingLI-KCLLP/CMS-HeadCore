<?php

namespace Core\Commands;

use Core\Annotations\AnnotationReader;
use Core\Annotations\AnnotationsDump\PropertyAnnotationsDump;
use Core\Annotations\ORM\AutoIncrement;
use Core\Annotations\ORM\Column;
use Core\Annotations\ORM\Id;
use Core\Annotations\ORM\ManyToOne;
use Core\Database\DatabaseConnexion;
use Core\Database\Dsn;
use Core\Entities\AbstractEntity;

class CreateSchema extends AbstractCommand {

    const string ENTITIES_NAMESPACE_PREFIX = "App\\Entities\\";
    const string CREATE_TABLE_FORMAT = 'CREATE TABLE IF NOT EXISTS %s (%s);';

    public function execute(): void {
        $entitiesClasses = self::getEntitiesClasses();
        $statement = '';

        $classesAnnotationsDump = [];

        foreach($entitiesClasses as $entityClass) {
            $classesAnnotationsDump[] = AnnotationReader::extractFromClass($entityClass);
        }

        $sortedClassesAnnotationsDump = [];

        while(count($sortedClassesAnnotationsDump) < count($classesAnnotationsDump)) {
        	foreach($classesAnnotationsDump as $class) {
            	if(array_key_exists($class->getName(), $sortedClassesAnnotationsDump) === true) {
            		continue;
            	}

            	if($class->propertiesHaveAnnotation(ManyToOne::class) === false) {
            		$sortedClassesAnnotationsDump[$class->getName()] = $class;
            	    continue;
            	}
	
            	$referencesCount = count($class->getPropertiesWithAnnotation(ManyToOne::class));
            	foreach($sortedClassesAnnotationsDump as $name => $weightedClass) {
            	    foreach($class->getPropertiesWithAnnotation(ManyToOne::class) as $property) {
            	        if($name === $property->getAnnotation(ManyToOne::class)->class) {
                			$referencesCount--;
            	        }
            	    }
            	}
	
            	if($referencesCount === 0) {
            		$sortedClassesAnnotationsDump[$class->getName()] = $class;
            		continue;
            	}
            }
        }

        foreach($sortedClassesAnnotationsDump as $classAnnotionsDump) {
            $properties = $classAnnotionsDump->getPropertiesWithAnnotation(Column::class);

            $statement .= self::getSqlCreateTableScript($classAnnotionsDump->getName(), $properties);
            $statement .= PHP_EOL;
        }


        echo $statement;

        $db = new DatabaseConnexion();
        $dsn = new Dsn();
        $dsn->addHostToDsn();
        $dsn->addPortToDsn();
        $dsn->addDbnameToDsn();
        $db->setConnexion($dsn);

        $db->getConnexion()->exec($statement);
    }

    public function undo(): void {
        
    }

    public function redo(): void {
        
    }

    private static function getEntitiesClasses(): array {
        $entitiesClasses = [];

        $files = scandir(__DIR__ . '/../../Entities');

        foreach($files as $file) {
            if($file === '.' || $file === '..') {
                continue;
            }
            
            $className = self::ENTITIES_NAMESPACE_PREFIX . pathinfo($file, PATHINFO_FILENAME);

            if(!class_exists($className)) {
                continue;
            }

            if(!is_subclass_of($className, AbstractEntity::class)) {
                continue;
            }

            $entitiesClasses[] = $className;
        }


        return $entitiesClasses;
    }

    private static function getSqlCreateTableScript(string $className, array $properties): string {
        $propertiesStatement = '';
        
        foreach($properties as $propertyAnnotationsDump) {
            $propertiesStatement .= self::getSqlPropertyScript($propertyAnnotationsDump);
        }

        return sprintf(self::CREATE_TABLE_FORMAT, (new \ReflectionClass($className))->getShortName(), rtrim($propertiesStatement, ','));
    }
    
    private static function getSqlPropertyScript(PropertyAnnotationsDump $propertyAnnotationsDump): string {
        $statement = '';

        $propertyName = $propertyAnnotationsDump->getName();
        if($propertyAnnotationsDump->getAnnotation(Column::class)->name !== null) {
            $propertyName = $propertyAnnotationsDump->getAnnotation(Column::class)->name;
        }

        $statement .= $propertyName . ' ';

        $statement .= $propertyAnnotationsDump->getAnnotation(Column::class)->type . '';

        if($propertyAnnotationsDump->getAnnotation(Column::class)->size !== null) {
            $statement .= '(' . $propertyAnnotationsDump->getAnnotation(Column::class)->size . ')';
        }
        
        if($propertyAnnotationsDump->getAnnotation(Column::class)->nullable === false) {
            $statement .= ' NOT NULL';
        }
        
        if($propertyAnnotationsDump->hasAnnotation(AutoIncrement::class) === true) {
            $statement .= ' AUTO_INCREMENT';
        }
        
        if($propertyAnnotationsDump->hasAnnotation(Id::class) === true) {
            $statement .= ' PRIMARY KEY';
        }

        $statement .= ',';

        if($propertyAnnotationsDump->hasAnnotation(ManyToOne::class) === true) {
            $reflector = new \ReflectionClass($propertyAnnotationsDump->getAnnotation(ManyToOne::class)->class);
            $statement .= PHP_EOL;
            $statement .= 'FOREIGN KEY (' . $propertyName . ') REFERENCES ' . pathinfo($reflector->getFileName(), PATHINFO_FILENAME) . '(' .$propertyAnnotationsDump->getAnnotation(ManyToOne::class)->property  . '),';
        }

        return $statement;
    }
    
}
