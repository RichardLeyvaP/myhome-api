<?php

namespace App\Enums;

use JsonSerializable;

interface BaseEnum extends JsonSerializable
{
    public function label(): string;
    public static function toArray(): array;
}
