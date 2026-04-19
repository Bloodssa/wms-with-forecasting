<?php

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case TECHNICIAN = 'technician';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return ucwords($this->value);
    }
}
