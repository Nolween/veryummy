<?php

namespace App\Enums;

enum Units: string
{
    case UNIT = 'unit';
    case GRAM = 'gram';
    case OUNCE = 'ounce';
    case KILO = 'kilogram';
    case POUND = 'pound';
    case SOUP_SPOON = 'soup_spoon';
    case COFFEE_SPOON = 'coffee_spoon';
    case CENTILITER = 'centiliter';
    case LITER = 'liter';
    case PINCH = 'pinch';
    case SACHET = 'sachet';
    case TIN = 'tin';
    case STEM = 'stem';
    case BUNCH = 'bunch';
    case POD = 'pod';
    case SLICE = 'slice';
    case FILET = 'filet';
    case GLASS = 'glass';
    case BAR = 'bar';
    case EAR = 'ear';

    /**
     * @return array<string>
     */
    public static function allValues(): array
    {
        return [
            self::UNIT->value,
            self::GRAM->value,
            self::OUNCE->value,
            self::KILO->value,
            self::POUND->value,
            self::SOUP_SPOON->value,
            self::COFFEE_SPOON->value,
            self::CENTILITER->value,
            self::LITER->value,
            self::PINCH->value,
            self::SACHET->value,
            self::TIN->value,
            self::STEM->value,
            self::BUNCH->value,
            self::POD->value,
            self::SLICE->value,
            self::FILET->value,
            self::GLASS->value,
            self::BAR->value,
            self::EAR->value,
        ];
    }
}
