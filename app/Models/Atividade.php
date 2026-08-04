<?php

namespace App\Models;

use App\Enums\AguardandoPor;
use App\Enums\AtividadeIndicador;
use App\Enums\AtividadePrioridade;
use App\Enums\AtividadeStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atividade extends Model
{
    /** @use HasFactory<\Database\Factories\AtividadeFactory> */
    use HasFactory;

    protected $attributes = [
        'prioridade' => AtividadePrioridade::Normal->value,
    ];

    protected $fillable = [
        'titulo',
        'descricao',
        'solicitante',
        'data_atividade',
        'status',
        'aguardando_por',
        'aguardando_descricao',
        'prioridade',
        'prazo',
        'proxima_acao',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function planoTrabalho(): BelongsTo
    {
        return $this->belongsTo(PlanoTrabalho::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(AtividadeCategoria::class, 'categoria_id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(AtividadeMovimentacao::class);
    }

    public function scopeOperational(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            AtividadeStatus::Concluida->value,
            AtividadeStatus::Cancelada->value,
        ]);
    }

    public function scopeOverdue(Builder $query, ?CarbonInterface $referenceDate = null): Builder
    {
        $date = ($referenceDate ?? today())->format('Y-m-d');

        return $query->operational()->whereNotNull('prazo')->whereDate('prazo', '<', $date);
    }

    public function scopeWithoutRecentUpdate(
        Builder $query,
        ?CarbonInterface $referenceDate = null,
        int $days = 10,
    ): Builder {
        $date = $referenceDate ?? today();
        $cutoff = $date->copy()->subDays($days)->endOfDay()->format('Y-m-d H:i:s');
        $latestMovementDate = $this->latestMovementDateSubquery($query);

        return $query
            ->operational()
            ->where(fn (Builder $deadline) => $deadline
                ->whereNull('prazo')
                ->orWhereDate('prazo', '>=', $date->format('Y-m-d')))
            ->where($latestMovementDate, '<=', $cutoff);
    }

    public function scopeForIndicator(Builder $query, AtividadeIndicador $indicator): Builder
    {
        return match ($indicator) {
            AtividadeIndicador::Atrasadas => $query->overdue(),
            AtividadeIndicador::Aguardando => $query->where('status', AtividadeStatus::Aguardando),
            AtividadeIndicador::Urgentes => $query->operational()->where('prioridade', AtividadePrioridade::Urgente),
            AtividadeIndicador::SemAtualizacao => $query->withoutRecentUpdate(),
        };
    }

    public function scopeWithLatestMovementDate(Builder $query): Builder
    {
        return $query
            ->addSelect(['ultima_movimentacao_em' => $this->latestMovementDateSubquery($query)])
            ->withCasts(['ultima_movimentacao_em' => 'date']);
    }

    public function isOverdue(?CarbonInterface $referenceDate = null): bool
    {
        return $this->isOperational()
            && $this->prazo !== null
            && $this->prazo->lt($referenceDate ?? today());
    }

    public function isWithoutRecentUpdate(
        ?CarbonInterface $referenceDate = null,
        int $days = 10,
    ): bool {
        $date = $referenceDate ?? today();
        $lastMovementDate = $this->getAttribute('ultima_movimentacao_em');

        if ($lastMovementDate === null || ! $this->isOperational()) {
            return false;
        }

        $lastMovementDate = $lastMovementDate instanceof CarbonInterface
            ? $lastMovementDate
            : CarbonImmutable::parse($lastMovementDate);

        return $lastMovementDate->lte($date->copy()->subDays($days))
            && ($this->prazo === null || $this->prazo->gte($date));
    }

    protected function childRouteBindingRelationshipName($childType): string
    {
        if ($childType === 'movimentacao') {
            return 'movimentacoes';
        }

        return parent::childRouteBindingRelationshipName($childType);
    }

    private function isOperational(): bool
    {
        return ! in_array($this->status, [AtividadeStatus::Concluida, AtividadeStatus::Cancelada], true);
    }

    private function latestMovementDateSubquery(Builder $query): Builder
    {
        $movement = new AtividadeMovimentacao;

        return AtividadeMovimentacao::query()
            ->select('data_movimentacao')
            ->whereColumn(
                $movement->qualifyColumn('atividade_id'),
                $query->getModel()->qualifyColumn('id'),
            )
            ->inBusinessOrder(descending: true)
            ->limit(1);
    }

    protected function casts(): array
    {
        return [
            'data_atividade' => 'date',
            'status' => AtividadeStatus::class,
            'aguardando_por' => AguardandoPor::class,
            'prioridade' => AtividadePrioridade::class,
            'prazo' => 'date',
        ];
    }
}
