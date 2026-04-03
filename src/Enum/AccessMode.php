<?php

namespace App\Enum;

enum AccessMode: string
{
    case Public = 'public';
    case Password = 'password';
    case Email = 'email';
}
