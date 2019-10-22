<?php

namespace LenderSpender\LaravelEnums;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\In;
use Lang;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;

/**
 * Shameless copy from Myclabs/php-enum repo with a few tweaks
 *   and localization additions.
 *
 * @see    http://github.com/myclabs/php-enum
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */
abstract class Enum
{
    /**
     * Enum value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Store existing constants in a static cache per object.
     *
     * @var array
     */
    protected static $cache = [];

    protected static $fakeValues = [];

    /**
     * Creates a new value of some type.
     *
     * @param mixed $value
     *
     * @throws \UnexpectedValueException if incompatible type is given
     */
    public function __construct($value)
    {
        if ($value instanceof static) {
            $this->value = $value->value();

            return;
        }

        if (! $this->isValidValue($value)) {
            throw new \UnexpectedValueException("Value '{$value}' is not part of the enum " . get_called_class());
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string) $this->value();
    }

    /**
     * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return static
     */
    public static function __callStatic($name, $arguments)
    {
        $array = static::toArray();

        if (self::isValidKey($name)) {
            return new static($array[$name]);
        }

        throw new \BadMethodCallException("No static method or enum constant '${name}' in class " . get_called_class());
    }

    /**
     * @param array|Enum|null $exceptions
     *
     * @return In
     */
    public static function ruleIn($exceptions = null): In
    {
        $values = self::values();

        if (! $exceptions) {
            return new In($values);
        }

        $exceptions = is_array($exceptions) ? $exceptions : [$exceptions];

        foreach ($exceptions as $exception) {
            $keys[] = strtoupper($exception->value());
        }

        return new In(Arr::except($values, $keys ?? []));
    }

    /**
     * Compares one Enum with another.
     *
     * This method is final, for more information read https://github.com/myclabs/php-enum/issues/4
     *
     * @return bool True if Enums are equal, false if not equal
     */
    final public function equals(Enum $enum = null): bool
    {
        return $enum !== null && $this->valuesAreEqual($enum) && \get_called_class() === \get_class($enum);
    }

    /**
     * @deprecated use value() instead
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value();
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getCamelValue()
    {
        return Str::camel($this->value);
    }

    /**
     * Returns the enum key (i.e. the constant name).
     *
     * @return mixed
     */
    public function getKey()
    {
        return static::search($this->value);
    }

    /**
     * Returns the localized label.
     *
     * @return mixed
     */
    public function getLabel()
    {
        return static::label($this->value);
    }

    /**
     * Returns instances of the Enum class of all Enum constants.
     *
     * @return array|static[]
     *                        Constant name in key, Enum instance in value
     */
    public static function values()
    {
        $values = [];
        foreach (static::toArray() as $key => $value) {
            $values[$key] = new static($value);
        }

        return $values;
    }

    public static function selectValues(bool $emptyFirst = false, array $only = [], array $except = [], bool $showValue = false)
    {
        $values = [];

        if ($emptyFirst) {
            $values = [0 => ''];
        }

        foreach (static::toArray() as $key => $value) {
            $values[$value] = $showValue ? '(' . $key . ') ' . self::label($value) : self::label($value);
        }

        if (count($only)) {
            $onlyValues = collect($only)
                ->map(function ($value) {
                    if ($value instanceof Enum) {
                        return $value->value();
                    }

                    return $value;
                })
                ->toArray();

            return Arr::only($values, $onlyValues);
        }

        if (count($except)) {
            $exceptValues = collect($except)
                ->map(function ($value) {
                    if ($value instanceof Enum) {
                        return $value->value();
                    }

                    return $value;
                })
                ->toArray();

            return Arr::except($values, $exceptValues);
        }

        return $values;
    }

    /**
     * Returns the names (keys) of all constants in the Enum class.
     */
    public static function keys(): array
    {
        return array_keys(static::toArray());
    }

    /**
     * Returns all possible values as an array.
     */
    public static function toArray(): array
    {
        $class = get_called_class();
        $fakeValue = static::$fakeValues[$class] ?? null;

        if (! array_key_exists($class, static::$cache) || $fakeValue) {
            $reflection = new ReflectionClass($class);
            $constants = $reflection->getConstants();

            if (class_implements($class, CanBeUnknown::class)) {
                $constants['UNKNOWN'] = null;
            }

            if ($fakeValue) {
                return $constants + [strtoupper($fakeValue) => $fakeValue];
            }

            static::$cache[$class] = $constants;
        }

        return static::$cache[$class];
    }

    /**
     * Returns all possible values as a comma separated string.
     */
    public static function valuesToString(): string
    {
        return implode(',', self::toArray());
    }

    /**
     * Check if is valid enum value.
     *
     * @param $value
     */
    public static function isValidValue($value): bool
    {
        if (class_implements(get_called_class(), CanBeUnknown::class) && ($value === null || $value === '')) {
            return true;
        }

        return in_array($value, self::toArray(), $strict = true);
    }

    /**
     * Check if is valid enum key.
     *
     * @param $key
     */
    public static function isValidKey($key): bool
    {
        $array = self::toArray();

        return isset($array[$key]) || \array_key_exists($key, $array);
    }

    /**
     * Return key for value.
     *
     * @param $value
     *
     * @return mixed
     */
    public static function search($value)
    {
        return array_search($value, static::toArray(), true);
    }

    /**
     * Methods for localization.
     */
    public static function label($typeValue): string
    {
        $langId = 'typelabels.' . get_called_class() . '.' . strtolower(self::search($typeValue));

        if (Lang::has($langId)) {
            $translation = trans($langId);
            if (is_string($translation)) {
                return $translation;
            }

            if (is_array($translation)) {
                if (Lang::has($langId . '.label')) {
                    return trans($langId . '.label');
                }
            }
        }
        // fallback: return constant name
        return static::search($typeValue);
    }

    public function description(): string
    {
        $langId = 'typelabels.' . get_called_class() . '.' . strtolower($this->value()) . '.description';

        return Lang::has($langId) ? Lang::get($langId) : '';
    }

    public static function testDataProvider(): array
    {
        return collect(static::values())
            ->mapWithKeys(function (Enum $enum) {
                return [$enum->getKey() => [$enum]];
            })
            ->toArray();
    }

    /**
     * @param string $value
     *
     * @return \Mockery\MockInterface|$this
     */
    public static function fake(string $value): MockInterface
    {
        $className = get_called_class();
        static::$fakeValues[$className] = $value;

        return Mockery::spy(new $className($value));
    }

    public static function clearFakeValues(): void
    {
        static::$fakeValues = [];
    }

    private function valuesAreEqual(Enum $enum = null): bool
    {
        if ($enum instanceof CanBeUnknown) {
            return $this->value() == $enum->value();
        }

        return $this->value() === $enum->value();
    }
}
