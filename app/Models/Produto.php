<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produto extends Model
{
    protected $fillable = [
        'nome',
        'categoria_id',
        'codigo_barras',
        'foto',
    ];
    public function categoria() : BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
