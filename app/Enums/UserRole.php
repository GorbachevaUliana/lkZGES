<?php

namespace App\Enums;

enum UserRole: string
{
    case Guest     = 'guest';
    case Applicant = 'applicant';
    case Client    = 'client';
    case Staff     = 'staff';
    case Admin     = 'admin';

    public function label(): string
    {
        return match($this) {
            self::Guest     => 'Гость',
            self::Applicant => 'Заявитель',
            self::Client    => 'Клиент',
            self::Staff     => 'Сотрудник',
            self::Admin     => 'Администратор',
        };
    }
}