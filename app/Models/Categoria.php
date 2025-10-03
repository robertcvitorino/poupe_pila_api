<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nome', 'descricao'];

    public function produtos() : HasMany
    {
        return $this->hasMany(Produto::class, 'categoria_id');
    }
}
