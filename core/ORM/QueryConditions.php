<?php

namespace Core\ORM;

enum QueryConditions: string {
    case EQ = '=';
    case NEQ = '!=';
    case LT = '<';
    case LTE = '<=';
    case GT = '>';
    case GTE = '>=';
    case LIKE = 'LIKE';
    case IN = 'IN';
}
?>
