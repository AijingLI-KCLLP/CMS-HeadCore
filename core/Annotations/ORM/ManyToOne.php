<?php


namespace Core\Annotations\ORM;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne extends References{
}

?>
