<?php

namespace App\Enums;

enum AtividadeStatus: string
{
    case Aberta = 'aberta';
    case EmAndamento = 'em_andamento';
    case Aguardando = 'aguardando';
    case Concluida = 'concluida';
    case Cancelada = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Aberta => 'Aberta',
            self::EmAndamento => 'Em andamento',
            self::Aguardando => 'Aguardando',
            self::Concluida => 'Concluída',
            self::Cancelada => 'Cancelada',
        };
    }
}
