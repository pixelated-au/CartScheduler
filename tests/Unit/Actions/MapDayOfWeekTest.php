<?php

namespace Actions;

use App\Actions\MapDayOfWeek;
use InvalidArgumentException;
use Tests\TestCase;


class MapDayOfWeekTest extends TestCase
{
    public function test_convert_day_of_week_to_integer(): void
    {
        $mapDayOfWeek = new MapDayOfWeek();

        $this->assertEquals(0, $mapDayOfWeek->toInteger('SUN'));
        $this->assertEquals(1, $mapDayOfWeek->toInteger('MON'));
        $this->assertEquals(2, $mapDayOfWeek->toInteger('TUE'));
        $this->assertEquals(3, $mapDayOfWeek->toInteger('WED'));
        $this->assertEquals(4, $mapDayOfWeek->toInteger('THU'));
        $this->assertEquals(5, $mapDayOfWeek->toInteger('FRI'));
        $this->assertEquals(6, $mapDayOfWeek->toInteger('SAT'));
    }

    public function test_throw_an_exception_for_invalid_day_of_week(): void
    {
        $mapDayOfWeek = new MapDayOfWeek();

        $this->expectException(InvalidArgumentException::class);
        $mapDayOfWeek->toInteger('INVALID_DAY');
    }

    public function test_lengthen(): void
    {
        $mapDayOfWeek = new MapDayOfWeek();

        $this->assertEquals('Sunday', $mapDayOfWeek->lengthen('SUN'));
        $this->assertEquals('Monday', $mapDayOfWeek->lengthen('MON'));
        $this->assertEquals('Tuesday', $mapDayOfWeek->lengthen('TUE'));
        $this->assertEquals('Wednesday', $mapDayOfWeek->lengthen('WED'));
        $this->assertEquals('Thursday', $mapDayOfWeek->lengthen('THU'));
        $this->assertEquals('Friday', $mapDayOfWeek->lengthen('FRI'));
        $this->assertEquals('Saturday', $mapDayOfWeek->lengthen('SAT'));
    }

    public function test_lengthen_with_invalid_day_of_week(): void
    {
        $mapDayOfWeek = new MapDayOfWeek();

        $this->expectException(InvalidArgumentException::class);
        $mapDayOfWeek->lengthen('INVALID');
    }
}
