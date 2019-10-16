<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums\Models\Traits;

trait CastsEnums
{
    public function getAttributeValue($key)
    {
        if ($this->isEnumAttribute($key)) {
            $class = $this->getEnumClass($key);

            return new $class($this->getAttributeFromArray($key));
        }

        return parent::getAttributeValue($key);
    }

    public function getAttribute($key)
    {
        if ($this->isEnumAttribute($key)) {
            return $this->getAttributeValue($key);
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if ($this->isEnumAttribute($key)) {
            $enumClass = $this->getEnumClass($key);

            if (! $value instanceof $enumClass) {
                $value = new $enumClass($value);
            }

            $this->attributes[$key] = $value->value();

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    protected function isEnumAttribute(string $key): bool
    {
        return isset($this->enums[$key]);
    }

    protected function getEnumClass(string $key): string
    {
        return $this->enums[$key];
    }
}
