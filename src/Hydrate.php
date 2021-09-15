<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Kabunx\Hydrate\Contracts\EntityInterface;
use ReflectionClass;
use ReflectionProperty;

class Hydrate
{
    protected static SourceReader $sourceReader;

    protected static array $reflectionProperties = [];

    protected static array $snakeNames = [];

    protected static array $studlyNames = [];

    protected EntityInterface $entity;

    /**
     * 将数据配分到属性上
     */
    public static function reallocate(EntityInterface $entity): void
    {
        $instance = new static();
        $instance->setEntity($entity);
        $instance->assignPropertiesValue();
    }

    public function assignPropertiesValue(): void
    {
        $reflectProperties = $this->getReflectionProperties();
        foreach ($reflectProperties as $name => $property) {
            $default = $property->isInitialized($this->entity)
                ? $property->getValue($this->entity)
                : null;
            $from = $this->getSourceReader()->getSourceFrom($property);
            if (! $from) {
                $from = $this->getSourceKeyName($name);
            }
            $value = $this->entity->getOriginal($from, $default);
            // 被定义的数据转化
            $value = $this->entity->transform2Entity($name, $value, $property->getType());
            $property->setValue($this->entity, $value);
        }
    }

    /**
     * 对象转为数组
     */
    public static function extract(EntityInterface $entity): array
    {
        $instance = new static();
        $instance->setEntity($entity);

        return $instance->toArray();
    }

    public function toArray(): array
    {
        $result = [];
        $reflectProperties = $this->getReflectionProperties();
        foreach ($reflectProperties as $name => $property) {
            $to = $this->getSourceReader()->getSourceTo($property);
            if (! $to) {
                $to = $this->getArrayKeyName($name);
            }
            $value = $property->getValue($this->entity);
            $value = $this->entity->transform2Array($name, $value);
            $result[$to] = $value;
        }

        return $result;
    }

    /**
     * @param EntityInterface $entity
     * @return $this
     */
    public function setEntity(EntityInterface $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function getSourceReader(): SourceReader
    {
        if (! isset(static::$sourceReader)) {
            static::$sourceReader = new SourceReader();
        }

        return static::$sourceReader;
    }

    /**
     * 获取要转化对象的属性
     *
     * @return ReflectionProperty[]
     */
    protected function getReflectionProperties(): array
    {
        $class = $this->entity::class;
        if (isset(static::$reflectionProperties[$class])) {
            return static::$reflectionProperties[$class];
        }
        $properties = [];
        $reflectProperties = (new ReflectionClass($this->entity))->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($reflectProperties as $property) {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property;
        }

        return static::$reflectionProperties[$class] = $properties;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getSourceKeyName(string $name): string
    {
        return match ($this->entity->getSourceKeyFormat()) {
            'snake' => $this->toSnakeName($name),
            'camel' => lcfirst($this->toStudlyName($name)),
            'studly' => $this->toStudlyName($name),
            default => $name
        };
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getArrayKeyName(string $name): string
    {
        return match ($this->entity->getArrayKeyFormat()) {
            'snake' => $this->toSnakeName($name),
            default => $name
        };
    }

    /**
     * @param string $name
     * @param string $delimiter
     * @return string
     */
    protected function toSnakeName(string $name, string $delimiter = '_'): string
    {
        $key = $name;
        if (isset(static::$snakeNames[$key][$delimiter])) {
            return static::$snakeNames[$key][$delimiter];
        }
        if (! ctype_lower($name)) {
            $name = preg_replace('/\s+/u', '', ucwords($name));
            $name = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $name), 'UTF-8');
        }

        return static::$snakeNames[$key][$delimiter] = $name;
    }

    protected function toStudlyName(string $name): string
    {
        $key = $name;
        if (isset(static::$studlyNames[$key])) {
            return static::$studlyNames[$key];
        }
        $name = ucwords(str_replace(['-', '_'], ' ', $name));

        return static::$studlyNames[$key] = str_replace(' ', '', $name);
    }
}
