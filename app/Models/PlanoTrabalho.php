<?php

namespace App\Models;

use Database\Factories\PlanoTrabalhoFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanoTrabalho extends Model
{
    /** @use HasFactory<PlanoTrabalhoFactory> */
    use HasFactory;

    protected $table = 'planos_trabalho';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    public function pit(): BelongsTo
    {
        return $this->belongsTo(Pit::class);
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class);
    }

    protected function dataInicial(): Attribute
    {
        return Attribute::get(fn () => $this->pit->data_inicial)->withoutObjectCaching();
    }

    protected function dataFinal(): Attribute
    {
        return Attribute::get(fn () => $this->pit->data_final)->withoutObjectCaching();
    }

    protected function status(): Attribute
    {
        return Attribute::get(fn () => $this->pit->status)->withoutObjectCaching();
    }
}
