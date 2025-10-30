<?php

namespace App\Models;

use App\StatusEnum;
use App\TipoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Compra extends Model
{
    use HasApiTokens;
    protected $fillable =
    [
        'usuario_id',
        'lista_origem_id',
        'nome',
        'descricao',
        'tipo',
        'status',
        'total',
        'local',
        'data_compra'
    ];

    protected $casts =
    [
        'data_compra' => 'datetime',
        'total' => 'decimal:2',
        'status' => StatusEnum::class,
        'tipo' => TipoEnum::class,
    ];

    public function usuario() : BelongsTo
    {
        return $this->belongsTo(User::class, );
    }

    public function itens() : HasMany
    {
        return $this->hasMany(ItemCompra::class, );
    }

    public function listaOrigem() : BelongsTo
    {
        return $this->belongsTo(Compra::class, );
    }
}
