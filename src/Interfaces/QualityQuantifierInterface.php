<?php

namespace App\Interfaces;


use App\Domain\Ad;


interface QualityQuantifierInterface
{
    public static function Quantify ( Ad $ad, int $min ) :int;
}