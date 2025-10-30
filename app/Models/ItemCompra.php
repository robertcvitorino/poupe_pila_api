<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class ItemCompra extends Model
{
    use HasApiTokens;
    protected $fillable =
    [
        'compra_id',
        'produto_id',
        'quantidade',
        'preco_unitario',
        'adicionado_carrinho'
    ];

    public function compra() : BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
