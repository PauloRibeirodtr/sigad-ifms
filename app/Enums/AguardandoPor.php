<?php

namespace App\Enums;

enum AguardandoPor: string
{
    case Usuario = 'usuario';
    case Aluno = 'aluno';
    case Docente = 'docente';
    case Colegiado = 'colegiado';
    case SetorInterno = 'setor_interno';
    case SetorExterno = 'setor_externo';
    case Terceiro = 'terceiro';
    case Outro = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::Usuario => 'Usuário',
            self::Aluno => 'Aluno',
            self::Docente => 'Docente',
            self::Colegiado => 'Colegiado',
            self::SetorInterno => 'Setor interno',
            self::SetorExterno => 'Setor externo',
            self::Terceiro => 'Terceiro',
            self::Outro => 'Outro',
        };
    }
}
