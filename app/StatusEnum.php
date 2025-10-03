<?php

namespace App;

enum StatusEnum: string
{
    case Aberto = 'aberto';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
