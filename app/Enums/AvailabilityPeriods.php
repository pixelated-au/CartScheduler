<?php

namespace App\Enums;

enum AvailabilityPeriods: string
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Evening = 'evening';
    case FullDay = 'full-day';
    case HalfDay = 'half-day';
    case NotAvailable = 'not-available';
    case Zero = '0';
    case One = '1';
    case Two = '2';
    case Three = '3';
    case Four = '4';
    case Five = '5';
    case Six = '6';
    case Seven = '7';
    case Eight = '8';
    case Nine = '9';
    case Ten = '10';
    case Eleven = '11';
    case Twelve = '12';
    case Thirteen = '13';
    case Fourteen = '14';
    case Fifteen = '15';
    case Sixteen = '16';
    case Seventeen = '17';
    case Eighteen = '18';
    case Nineteen = '19';
    case Twenty = '20';
    case TwentyOne = '21';
    case TwentyTwo = '22';
    case TwentyThree = '23';

    public static function getDayParts(): array
    {
        return [
            self::Morning,
            self::Afternoon,
            self::Evening,
            self::FullDay,
            self::HalfDay,
            self::NotAvailable,
        ];
    }

    public static function getDayPartValues(): array
    {
        return array_map(
            fn(self $dayPart) => $dayPart->value,
            self::getDayParts()
        );
    }

    public static function getHourParts() :array
    {
        return [
            self::Zero,
            self::One,
            self::Two,
            self::Three,
            self::Four,
            self::Five,
            self::Six,
            self::Seven,
            self::Eight,
            self::Nine,
            self::Ten,
            self::Eleven,
            self::Twelve,
            self::Thirteen,
            self::Fourteen,
            self::Fifteen,
            self::Sixteen,
            self::Seventeen,
            self::Eighteen,
            self::Nineteen,
            self::Twenty,
            self::TwentyOne,
            self::TwentyTwo,
            self::TwentyThree,
        ];
    }

    public static function getHourPartValues(): array
    {
        return array_map(
            fn(self $hourPart) => $hourPart->value,
            self::getHourParts()
        );
    }
}
