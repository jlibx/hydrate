<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Traits;

use Carbon\Carbon;
use Kabunx\Hydrate\Contracts\TransformInterface;
use Kabunx\Hydrate\Contracts\EntityInterface;
use ReflectionNamedType;
use ReflectionType;
use Throwable;

/**
 * 将根据数据类型，做数据转换，暂时对联合类型不做推断
 */
trait Transformable
{

    /**
     * @var array
     */
    protected static array $cachedStudlyStrings = [];

    /**
     * @param string $key
     * @param mixed $value
     * @param ReflectionType|null $type
     * @return mixed
     */
    public function transform2Entity(string $key, mixed $value, ReflectionType $type = null): mixed
    {
        // 优先调用系统setXyz函数
        if ($this->hasSetTransformer($key)) {
            return $this->asSetTransformedValue($key, $value);
        }
        // 无法推断直接返回
        if (is_null($type)) {
            return $value;
        }
        // 单一类型
        if ($type instanceof ReflectionNamedType) {
            if (is_null($value) && $type->allowsNull()) {
                return null;
            }
            // 基础类型转化
            if ($type->isBuiltin()) {
                return match ($type->getName()) {
                    'array' => (array)$value,
                    'bool' => (bool)$value,
                    'float' => (float)$value,
                    'int' => (int)$value,
                    'string' => (string)$value,
                    'object' => (object)$value
                };
            }
            // 类类型转化
            if ($this->isClassTransformer($type->getName())) {
                return $this->asClassTransformerValue($value, $type->getName());
            }
        }

        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function transform2Array(string $key, mixed $value): mixed
    {
        // 优先调用系统setXyz函数
        if ($this->hasGetTransformer($key)) {
            return $this->asGetTransformedValue($key, $value);
        }
        if (is_array($value)) {
            $subValue = [];
            foreach ($value as $key => $item) {
                if ($item instanceof EntityInterface) {
                    $subValue[$key] = $item->toArray();
                } else {
                    $subValue[$key] = $item;
                }
            }
            $value = $subValue;
        } elseif ($value instanceof Carbon) {
            $value = $value->toDateTimeString();
        } elseif ($value instanceof TransformInterface) {
            $value = $value->extract();
        } elseif ($value instanceof EntityInterface) {
            $value = $value->toArray();
        }

        return $value;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string $key
     * @return bool
     */
    protected function hasSetTransformer(string $key): bool
    {
        return method_exists($this, $this->getSetTransformer($key));
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function asSetTransformedValue(string $key, mixed $value): mixed
    {
        return $this->{$this->getSetTransformer($key)}($value);
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string $key
     * @return bool
     */
    protected function hasGetTransformer(string $key): bool
    {
        return method_exists($this, $this->getSetTransformer($key));
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function asGetTransformedValue(string $key, mixed $value): mixed
    {
        return $this->{$this->getGetTransformer($key)}($value);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getGetTransformer(string $key): string
    {
        return 'get' . $this->toStudly($key);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getSetTransformer(string $key): string
    {
        return 'set' . $this->toStudly($key);
    }

    /**
     * @param string $value
     * @return string
     */
    protected function toStudly(string $value): string
    {
        $key = $value;
        if (isset(static::$cachedStudlyStrings[$key])) {
            return static::$cachedStudlyStrings[$key];
        }
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$cachedStudlyStrings[$key] = str_replace(' ', '', $value);
    }

    /**
     * Return a decimal as string.
     *
     * @param float $value
     * @param int $decimals
     * @return string
     */
    protected function asDecimal(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, '.', '');
    }

    /**
     * @param mixed $value
     * @return Carbon|null
     */
    protected function asCarbon(mixed $value): ?Carbon
    {
        try {
            return Carbon::rawParse($value);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param string $classname
     * @return bool
     */
    protected function isClassTransformer(string $classname): bool
    {
        if (class_exists($classname)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $value
     * @param string $classname
     * @return mixed
     */
    protected function asClassTransformerValue(mixed $value, string $classname): mixed
    {
        $transformer = new $classname;
        if ($transformer instanceof Carbon) {
            return $this->asCarbon($value);
        } elseif ($transformer instanceof EntityInterface) {
            return $transformer->fill($value);
        } elseif ($transformer instanceof TransformInterface) {
            return $transformer->hydrate($value);
        } else {
            return $value;
        }
    }

}
