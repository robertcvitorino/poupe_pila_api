<?php

namespace App;

enum TipoEnum : string
{
    case Carrinho = 'carrinho';
    case Lista = 'lista';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
