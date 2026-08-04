<?php

namespace App\Models;

use App\Enums\AguardandoPor;
use App\Enums\AtividadeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtividadeMovimentacao extends Model
{
    /** @use HasFactory<\Database\Factories\AtividadeMovimentacaoFactory> */
    use HasFactory;

    protected $table = 'atividade_movimentacoes';

    protected $fillable = [
        'data_movimentacao',
        'descricao',
        'status',
        'aguardando_por',
        'aguardando_descricao',
        'minutos_trabalhados',
        'anexo_path',
        'anexo_nome_original',
    ];

    protected $hidden = [
        'anexo_path',
    ];

    public function atividade(): BelongsTo
    {
        return $this->belongsTo(Atividade::class);
    }

    public function scopeInBusinessOrder(Builder $query, bool $descending = false): Builder
    {
        $direction = $descending ? 'desc' : 'asc';

        return $query
            ->orderBy('data_movimentacao', $direction)
            ->orderBy('created_at', $direction)
            ->orderBy('id', $direction);
    }

    protected function casts(): array
    {
        return [
            'data_movimentacao' => 'date',
            'status' => AtividadeStatus::class,
            'aguardando_por' => AguardandoPor::class,
            'minutos_trabalhados' => 'integer',
        ];
    }
}
