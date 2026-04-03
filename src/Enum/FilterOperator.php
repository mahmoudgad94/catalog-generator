<?php

namespace App\Enum;

enum FilterOperator: string
{
    case Eq = 'eq';
    case Neq = 'neq';
    case Gt = 'gt';
    case Lt = 'lt';
    case Gte = 'gte';
    case Lte = 'lte';
    case In = 'in';
    case Like = 'like';
    case Between = 'between';
}
