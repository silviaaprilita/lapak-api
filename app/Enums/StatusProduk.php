<?php

namespace App\Enums;

enum StatusProduk: string
{
    case Draf = 'draf';
    case Aktif = 'aktif';
    case Arsip = 'arsip';
}
