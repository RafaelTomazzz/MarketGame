<?php

namespace App\Enums;

enum TipoPromocao: string
{
    case Percentual = 'percentual';
    case PrecoFixo = 'preco_fixo';
}
