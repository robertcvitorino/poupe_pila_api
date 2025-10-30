<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Categoria extends Model
{
    use HasApiTokens;
    protected $fillable = ['nome', 'descricao'];

    public function produtos() : HasMany
    {
        return $this->hasMany(Produto::class, 'categoria_id');
    }
}
