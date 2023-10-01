<?php

namespace App\Enums;

enum Diets: string
{
    case ALL          = 'all';
    case VEGETARIAN   = 'vegetarian';
    case VEGAN        = 'vegan';
    case GLUTEN_FREE  = 'gluten_free';
    case LACTOSE_FREE = 'lactose_free';
    case HALAL        = 'halal';
    case KOSHER       = 'kosher';

    /**
     * @return array<string>
     */
    public static function allValues(): array
    {
        return [
            self::VEGETARIAN->value,
            self::VEGAN->value,
            self::GLUTEN_FREE->value,
            self::LACTOSE_FREE->value,
            self::HALAL->value,
            self::KOSHER->value,
        ];
    }
}
