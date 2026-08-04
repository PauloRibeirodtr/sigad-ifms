<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtividadeCategoria extends Model
{
    /** @use HasFactory<\Database\Factories\AtividadeCategoriaFactory> */
    use HasFactory;

    protected $attributes = [
        'ativa' => true,
    ];

    protected $fillable = [
        'nome',
        'descricao',
        'ativa',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class, 'categoria_id');
    }

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ativa', true);
    }

    protected function casts(): array
    {
        return [
            'ativa' => 'boolean',
        ];
    }
}
