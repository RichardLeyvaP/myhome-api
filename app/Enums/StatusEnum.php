<?php

namespace App\Enums;

enum StatusEnum: int implements BaseEnum
{
    case ACTIVE = 1;
    case INACTIVE = 2;

    public function label(): string
    {
        return match ($this) {
            static::ACTIVE => __('Activo'),
            static::INACTIVE => __('Inactivo')
        };
    }

    public static function toArray(): array
    {
        $options = [];
        foreach (static::cases() as $case)
            $options[] = array('value' => $case->value, 'label' => $case->label());

        return $options;
    }

    public function jsonSerialize(): mixed
    {
        return array('value' => $this->value, 'label' => $this->label());
    }
}
