<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Kabunx\Hydrate\Contracts\EntityInterface;
use ReflectionClass;
use ReflectionProperty;

class Hydrate
{
    protected static AttributeReader $attributeReader;

    protected static array $reflectionClasses = [];

    protected static array $reflectionProperties = [];

    protected static array $snakeNames = [];

    protected static array $studlyNames = [];

    /**
     * 将数据配分到属性上
     */
    public static function reassign(EntityInterface $entity): void
    {
        $instance = new static();
        $instance->assignEntity($entity);
    }

    public function assignEntity(EntityInterface $entity): void
    {
        $strategy = $this->getNamingStrategy($entity, 'source');
        $reflectProperties = $this->getReflectionProperties($entity);
        foreach ($reflectProperties as $name => $property) {
            $key = $this->getSourceKeyName($property, $strategy);
            $default = $property->isInitialized($entity)
                ? $property->getValue($entity)
                : null;
            $value = $entity->getOriginalValue($key, $default);
            // 被定义的数据转化
            $arrayEntity = $this->getAttributeReader()->getArrayEntityInstance($property);
            if ($arrayEntity) {
                $entities = [];
                foreach ((array)$value as $item) {
                    $entities[] = $arrayEntity->newInstance($item);
                }
                $value = $entities;
            } else {
                $value = $entity->transform2Entity($name, $value, $property->getType());
            }

            $property->setValue($entity, $value);
        }
    }

    /**
     * 对象转为数组
     */
    public static function extract(EntityInterface $entity): void
    {
        $instance = new static();
        $instance->toArray($entity);
    }

    public function toArray(EntityInterface $entity): void
    {
        $result = [];
        $strategy = $this->getNamingStrategy($entity, 'target');
        $reflectProperties = $this->getReflectionProperties($entity);
        foreach ($reflectProperties as $name => $property) {
            $key = $this->getTargetKeyName($property, $strategy);
            $value = $property->getValue($entity);
            $value = $entity->transform2Array($name, $value);
            $result[$key] = $value;
        }
        $entity->setArray($result);
    }

    public function getAttributeReader(): AttributeReader
    {
        if (!isset(static::$attributeReader)) {
            static::$attributeReader = new AttributeReader();
        }

        return static::$attributeReader;
    }

    /**
     * @param ReflectionProperty $property
     * @param string $strategy
     * @return string
     */
    public function getSourceKeyName(ReflectionProperty $property, string $strategy): string
    {
        $source = $this->getAttributeReader()->getColumnSourceName($property);
        if (!$source) {
            $source = $this->getKeyNameFromStrategy($property->getName(), $strategy);
        }
        return $source;
    }

    /**
     * @param ReflectionProperty $property
     * @param $strategy
     * @return string
     */
    public function getTargetKeyName(ReflectionProperty $property, $strategy): string
    {
        $source = $this->getAttributeReader()->getColumnTargetName($property);
        if (!$source) {
            $source = $this->getKeyNameFromStrategy($property->getName(), $strategy);
        }
        return $source;
    }

    protected function getReflectionClass(EntityInterface $entity): ReflectionClass
    {
        $className = $entity::class;
        if (isset(static::$reflectionClasses[$className])) {
            return static::$reflectionClasses[$className];
        }

        return static::$reflectionClasses[$className] = new ReflectionClass($entity);
    }

    /**
     * 获取要转化对象的属性
     *
     * @return ReflectionProperty[]
     */
    protected function getReflectionProperties(EntityInterface $entity): array
    {
        $className = $entity::class;
        if (isset(static::$reflectionProperties[$className])) {
            return static::$reflectionProperties[$className];
        }
        $properties = [];
        $reflectProperties = $this->getReflectionClass($entity)->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($reflectProperties as $property) {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property;
        }

        return static::$reflectionProperties[$className] = $properties;
    }

    /**
     * @param EntityInterface $entity
     * @param $type
     * @return string
     */
    protected function getNamingStrategy(EntityInterface $entity, $type): string
    {
        $strategy = $this->getAttributeReader()->getNamingStrategy(
            $this->getReflectionClass($entity)
        );
        [$source, $target] = explode('_', $strategy);
        return $type == 'source' ? $source : $target;
    }

    /**
     * @param string $name
     * @param $strategy
     * @return string
     */
    protected function getKeyNameFromStrategy(string $name, $strategy): string
    {
        return match ($strategy) {
            'snake' => $this->toSnakeName($name),
            'studly' => $this->toStudlyName($name),
            'camel' => lcfirst($this->toStudlyName($name)),
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
        if (!ctype_lower($name)) {
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
