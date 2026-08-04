<?php

namespace App\Models;

use App\Enums\PlanoTrabalhoStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanoTrabalho extends Model
{
    /** @use HasFactory<\Database\Factories\PlanoTrabalhoFactory> */
    use HasFactory;

    protected $table = 'planos_trabalho';

    protected $fillable = [
        'nome',
        'descricao',
        'data_inicial',
        'data_final',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class);
    }

    protected function status(): Attribute
    {
        return Attribute::get(function (): PlanoTrabalhoStatus {
            if (today()->lt($this->data_inicial)) {
                return PlanoTrabalhoStatus::Aguardando;
            }

            if (today()->gt($this->data_final)) {
                return PlanoTrabalhoStatus::Encerrado;
            }

            return PlanoTrabalhoStatus::EmAndamento;
        })->withoutObjectCaching();
    }

    protected function casts(): array
    {
        return [
            'data_inicial' => 'date',
            'data_final' => 'date',
        ];
    }
}
