<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Tests\Unit;

use BadMethodCallException;
use Illuminate\Validation\Rules\In;
use LenderSpender\LaravelEnums\CanBeUnknown;
use LenderSpender\LaravelEnums\Enum;
use Orchestra\Testbench\TestCase;
use UnexpectedValueException;

class EnumTest extends TestCase
{
    public function test_get_value(): void
    {
        $value = EnumFixture::FOO();
        self::assertEquals(EnumFixture::FOO(), $value->value());

        $value = EnumFixture::BAR();
        self::assertEquals(EnumFixture::BAR(), $value->value());

        $value = EnumFixture::NUMBER();
        self::assertEquals((string) EnumFixture::NUMBER(), $value->value());
    }

    public function test_get_key(): void
    {
        $value = EnumFixture::FOO();

        self::assertEquals('FOO', $value->getKey());
        self::assertNotEquals('BA', $value->getKey());
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function test_creating_enum_with_invalid_value($value): void
    {
        self::expectException(\UnexpectedValueException::class);
        self::expectExceptionMessage('Value \'' . $value . '\' is not part of the enum LenderSpender\LaravelEnums\Tests\Unit\EnumFixture');

        new EnumFixture($value);
    }

    /**
     * Contains values not existing in EnumFixture.
     */
    public function invalidValueProvider(): array
    {
        return [
            'string' => ['test'],
            'int' => [1234],
        ];
    }

    public function test_to_string(): void
    {
        self::assertSame(EnumFixture::FOO()->value(), EnumFixture::FOO()->__toString());
    }

    public function test_keys(): void
    {
        $values = EnumFixture::keys();

        $expectedValues = [
            'FOO',
            'BAR',
            'NUMBER',
            'PROBLEMATIC_NUMBER',
            'PROBLEMATIC_NULL',
            'PROBLEMATIC_EMPTY_STRING',
            'PROBLEMATIC_BOOLEAN_FALSE',
        ];

        self::assertSame($expectedValues, $values);
    }

    public function test_values(): void
    {
        $values = EnumFixture::values();
        $expectedValues = [
            'FOO' => EnumFixture::FOO(),
            'BAR' => EnumFixture::BAR(),
            'NUMBER' => EnumFixture::NUMBER(),
            'PROBLEMATIC_NUMBER' => EnumFixture::PROBLEMATIC_NUMBER(),
            'PROBLEMATIC_NULL' => EnumFixture::PROBLEMATIC_NULL(),
            'PROBLEMATIC_EMPTY_STRING' => EnumFixture::PROBLEMATIC_EMPTY_STRING(),
            'PROBLEMATIC_BOOLEAN_FALSE' => EnumFixture::PROBLEMATIC_BOOLEAN_FALSE(),
        ];

        self::assertEquals($expectedValues, $values);
    }

    public function test_to_array(): void
    {
        $values = EnumFixture::toArray();
        $expectedValues = [
            'FOO' => EnumFixture::FOO()->value(),
            'BAR' => EnumFixture::BAR()->value(),
            'NUMBER' => EnumFixture::NUMBER()->value(),
            'PROBLEMATIC_NUMBER' => EnumFixture::PROBLEMATIC_NUMBER()->value(),
            'PROBLEMATIC_NULL' => EnumFixture::PROBLEMATIC_NULL()->value(),
            'PROBLEMATIC_EMPTY_STRING' => EnumFixture::PROBLEMATIC_EMPTY_STRING()->value(),
            'PROBLEMATIC_BOOLEAN_FALSE' => EnumFixture::PROBLEMATIC_BOOLEAN_FALSE()->value(),
        ];

        self::assertSame($expectedValues, $values);
    }

    public function test_to_array_has_unknown_key_when_enum_can_be_unknown(): void
    {
        $nullableEnum = new class('') extends Enum implements CanBeUnknown {
        };

        self::assertSame(['UNKNOWN' => null], $nullableEnum->toArray());
    }

    public function test_enum_can_constructed_when_it_is_already_an_enum(): void
    {
        $enum = EnumFixture::BAR();

        try {
            self::assertEquals(EnumFixture::BAR(), new EnumFixture($enum));
        } catch (UnexpectedValueException $e) {
            self::fail('Enum cannot be constructed from enum');
        }
    }

    public function test_equals(): void
    {
        $bar = EnumFixture::BAR();
        $foo = EnumFixture::FOO();

        self::assertTrue(EnumFixture::BAR()->equals($bar));
        self::assertFalse(EnumFixture::BAR()->equals($foo));
        self::assertFalse(EnumFixture::BAR()->equals());
    }

    public function test_static_access(): void
    {
        self::assertEquals(new EnumFixture(EnumFixture::FOO), EnumFixture::FOO());
        self::assertEquals(new EnumFixture(EnumFixture::BAR), EnumFixture::BAR());
        self::assertEquals(new EnumFixture(EnumFixture::NUMBER), EnumFixture::NUMBER());
    }

    public function test_bad_static_access(): void
    {
        try {
            EnumFixture::UNKNOWN();
        } catch (BadMethodCallException $e) {
            self::assertSame("No static method or enum constant 'UNKNOWN' in class LenderSpender\LaravelEnums\Tests\Unit\EnumFixture", $e->getMessage());

            return;
        }

        self::fail('Bad static access should throw exception');
    }

    /**
     * @dataProvider isValidProvider
     */
    public function test_is_valid($value, bool $isValid): void
    {
        self::assertSame($isValid, EnumFixture::isValidValue($value));
    }

    public function isValidProvider(): array
    {
        return [
            /*
             * Valid values
             */
            ['foo', true],
            [42, true],
            [null, true],
            [0, true],
            ['', true],
            [false, true],
            /*
             * Invalid values
             */
            ['baz', false],
        ];
    }

    public function test_is_valid_key(): void
    {
        self::assertTrue(EnumFixture::isValidKey('FOO'));
        self::assertFalse(EnumFixture::isValidKey('BAZ'));
    }

    /**
     * search().
     *
     * @see https://github.com/myclabs/php-enum/issues/13
     * @dataProvider searchProvider
     */
    public function test_search($value, $expected)
    {
        self::assertSame($expected, EnumFixture::search($value));
    }

    public function searchProvider(): array
    {
        return [
            ['foo', 'FOO'],
            [0, 'PROBLEMATIC_NUMBER'],
            [null, 'PROBLEMATIC_NULL'],
            ['', 'PROBLEMATIC_EMPTY_STRING'],
            [false, 'PROBLEMATIC_BOOLEAN_FALSE'],
            ['bar I do not exist', false],
            [[], false],
        ];
    }

    public function test_select_values(): void
    {
        $list = EnumFixture::selectValues();
        // make sure it preserves the other keys
        self::assertEquals($list[42], 'NUMBER');
    }

    public function test_enum_can_have_fake(): void
    {
        try {
            new EnumFixture('baz');
        } catch (UnexpectedValueException $e) {
            self::assertSame("Value 'baz' is not part of the enum LenderSpender\LaravelEnums\Tests\Unit\EnumFixture", $e->getMessage());
        }

        EnumFixture::fake('baz');
        self::assertSame('baz', EnumFixture::BAZ()->value());

        try {
            self::assertSame('baz', Enum::BAZ()->value());
        } catch (BadMethodCallException $e) {
            self::assertSame("No static method or enum constant 'BAZ' in class LenderSpender\LaravelEnums\Enum", $e->getMessage());
        }
    }

    public function test_can_get_rule_in(): void
    {
        $rule = EnumFixture::ruleIn();

        self::assertInstanceOf(In::class, $rule);
        self::assertSame('in:"foo","bar","42","0","","","","baz"', (string) $rule);
    }

    public function test_can_exclude_values_from_rule_in(): void
    {
        $rule = EnumFixture::ruleIn(EnumFixture::FOO());

        self::assertInstanceOf(In::class, $rule);
        self::assertSame('in:"bar","42","0","","","","baz"', (string) $rule);
    }

    public function test_can_exclude_nullable_value_from_rule_in(): void
    {
        $nullableEnum = new class('') extends Enum implements CanBeUnknown {
            private const FOO = 'foo';
        };

        $rule = $nullableEnum::ruleIn($nullableEnum::UNKNOWN());
        self::assertSame('in:"foo"', (string) $rule);
    }

    public function test_enum_cannot_be_null_by_default(): void
    {
        self::expectException(UnexpectedValueException::class);

        $enum = new class('') extends Enum {
        };
    }

    public function test_enum_cannot_be_empty_by_default(): void
    {
        self::expectException(UnexpectedValueException::class);

        $enum = new class(null) extends Enum {
        };
    }

    public function test_can_be_unknown_enum_throws_exception_when_value_is_invalid(): void
    {
        self::expectException(UnexpectedValueException::class);

        $nullableEnum = new class('FOO') extends Enum implements CanBeUnknown {
        };
    }

    public function test_can_be_unknown_enum_can_be_nullable(): void
    {
        $nullableEnum = new class(null) extends Enum implements CanBeUnknown {
        };

        self::assertNull($nullableEnum->value());
        self::assertTrue($nullableEnum::UNKNOWN()->equals($nullableEnum));
    }

    public function test_can_be_unknown_enum_can_be_empty_string_and_is_casted_to_null(): void
    {
        $nullableEnum = new class('') extends Enum implements CanBeUnknown {
        };

        self::assertSame('', $nullableEnum->value());
        self::assertSame('UNKNOWN', $nullableEnum->getLabel());
        self::assertTrue($nullableEnum::UNKNOWN()->equals($nullableEnum));
    }

    public function test_can_have_custom_unknown_if_interface_is_not_implemented(): void
    {
        $nullableEnum = new class('foo') extends Enum {
            private const UNKNOWN = 'foo';
        };

        self::assertTrue($nullableEnum::UNKNOWN()->equals($nullableEnum));
        self::assertSame('foo', $nullableEnum->value());
    }
}
