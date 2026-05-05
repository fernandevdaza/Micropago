<?php

namespace App\Enums;

enum UserRole: string
{
    case Passenger = 'passenger';
    case Driver = 'driver';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
    case LineAdmin = 'line_admin';
}
