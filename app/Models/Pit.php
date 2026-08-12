<?php

namespace App\Models;

use App\Enums\PlanoTrabalhoStatus;
use Database\Factories\PitFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pit extends Model
{
    /** @use HasFactory<PitFactory> */
    use HasFactory;

    protected $fillable = [
        'ano',
        'semestre',
        'data_inicial',
        'data_final',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function planosTrabalho(): HasMany
    {
        return $this->hasMany(PlanoTrabalho::class);
    }

    protected function nome(): Attribute
    {
        return Attribute::get(fn (): string => $this->ano.'.'.$this->semestre);
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

    protected function childRouteBindingRelationshipName($childType): string
    {
        if ($childType === 'plano') {
            return 'planosTrabalho';
        }

        return parent::childRouteBindingRelationshipName($childType);
    }

    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'semestre' => 'integer',
            'data_inicial' => 'date',
            'data_final' => 'date',
        ];
    }
}
