<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case DepartmentUser = 'department_user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Hospital Administrator',
            self::DepartmentUser => 'Department Staff',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
