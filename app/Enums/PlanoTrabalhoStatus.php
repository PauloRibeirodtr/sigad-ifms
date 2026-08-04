<?php

namespace App\Enums;

enum PlanoTrabalhoStatus: string
{
    case Aguardando = 'aguardando';
    case EmAndamento = 'em_andamento';
    case Encerrado = 'encerrado';

    public function label(): string
    {
        return match ($this) {
            self::Aguardando => 'Aguardando',
            self::EmAndamento => 'Em andamento',
            self::Encerrado => 'Encerrado',
        };
    }
}
