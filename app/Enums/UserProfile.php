<?php

namespace App\Enums;

enum UserProfile: string
{
    case Administrador = 'administrador';
    case Usuario = 'usuario';

    public function label(): string
    {
        return match ($this) {
            self::Administrador => 'Administrador',
            self::Usuario => 'Usuário',
        };
    }
}
