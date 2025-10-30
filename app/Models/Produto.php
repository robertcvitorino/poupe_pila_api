<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class Produto extends Model
{
    use HasApiTokens;
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
