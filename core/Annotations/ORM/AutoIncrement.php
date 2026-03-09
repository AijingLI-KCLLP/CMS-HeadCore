<?php


namespace Core\Annotations\ORM;

use Core\Annotations\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AutoIncrement extends AbstractAnnotation {}

?>
