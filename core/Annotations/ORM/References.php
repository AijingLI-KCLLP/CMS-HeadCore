<?php


namespace Core\Annotations\ORM;

use Core\Annotations\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class References extends AbstractAnnotation{
    public function __construct(
        public string $class,
        public string $property
    ){}
}

?>
