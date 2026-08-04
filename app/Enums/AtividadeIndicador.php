<?php

namespace App\Enums;

enum AtividadeIndicador: string
{
    case Atrasadas = 'atrasadas';
    case Aguardando = 'aguardando';
    case Urgentes = 'urgentes';
    case SemAtualizacao = 'sem_atualizacao';

    public function label(): string
    {
        return match ($this) {
            self::Atrasadas => 'Atrasadas',
            self::Aguardando => 'Aguardando',
            self::Urgentes => 'Urgentes',
            self::SemAtualizacao => 'Sem atualização recente',
        };
    }
}
