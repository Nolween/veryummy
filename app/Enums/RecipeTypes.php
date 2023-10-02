<?php

namespace App\Enums;

enum RecipeTypes: string
{
    case STARTER = 'starter';
    case MAIN_COURSE = 'main_course';
    case DESSERT = 'dessert';
    case APPETIZER = 'appetizer';
    case SAUCE = 'sauce';
    case ACCOMPANIMENTS = 'accompaniments';
    case DRINK = 'drink';
    case CONFECTIONERY = 'confectionery';
    case ADVICE = 'advice';

    /**
     * @return array<string>
     */
    public static function allValues(): array
    {
        return [
            self::STARTER->value,
            self::MAIN_COURSE->value,
            self::DESSERT->value,
            self::APPETIZER->value,
            self::SAUCE->value,
            self::ACCOMPANIMENTS->value,
            self::DRINK->value,
            self::CONFECTIONERY->value,
            self::ADVICE->value,
        ];
    }
}
