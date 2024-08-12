<?php

namespace App\Enums;

enum RoleEnum: int implements BaseEnum
{
    case ADMIN = 1;
    case USER = 2;

    public function label(): string
    {
        return match ($this) {
            static::ADMIN => __('Administrador'),
            static::USER => __('Usuario')
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
