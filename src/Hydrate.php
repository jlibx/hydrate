<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Kabunx\Hydrate\Contracts\EntityInterface;
use ReflectionClass;
use ReflectionProperty;

class Hydrate
{
    protected static AttributeReader $attributeReader;

    protected static array $reflectionProperties = [];

    protected static array $snakeNames = [];

    protected static array $studlyNames = [];

    protected EntityInterface $entity;

    /**
     * 将数据配分到属性上
     */
    public static function reassign(EntityInterface $entity): void
    {
        $instance = new static();
        $instance->setEntity($entity);
        $instance->assignEntity();
    }

    public function assignEntity(): void
    {
        $reflectProperties = $this->getReflectionProperties();
        foreach ($reflectProperties as $name => $property) {
            $default = $property->isInitialized($this->entity)
                ? $property->getValue($this->entity)
                : null;
            $source = $this->getAttributeReader()->getColumnSourceName($property);
            if (! $source) {
                $source = $this->getEntitySourceKeyName($name);
            }
            $value = $this->entity->getValueFromOriginal($source, $default);
            // 被定义的数据转化
            if ($entity = $this->getAttributeReader()->getArrayEntityClass($property)) {
                $entities = [];
                foreach ((array)$value as $item) {
                    $entities[] = $entity->newInstance($item);
                }
                $value = $entities;
            } else {
                $value = $this->entity->transform2Entity($name, $value, $property->getType());
            }

            $property->setValue($this->entity, $value);
        }
    }

    /**
     * 对象转为数组
     */
    public static function extract(EntityInterface $entity): void
    {
        $instance = new static();
        $instance->setEntity($entity);
        $instance->toArray();
    }

    public function toArray(): void
    {
        $result = [];
        $reflectProperties = $this->getReflectionProperties();
        foreach ($reflectProperties as $name => $property) {
            $target = $this->getAttributeReader()->getColumnTargetName($property);
            if (! $target) {
                $target = $this->getEntityTargetKeyName($name);
            }
            $value = $property->getValue($this->entity);
            $value = $this->entity->transform2Array($name, $value);
            $result[$target] = $value;
        }
        $this->entity->setArray($result);
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

    public function getAttributeReader(): AttributeReader
    {
        if (! isset(static::$attributeReader)) {
            static::$attributeReader = new AttributeReader();
        }

        return static::$attributeReader;
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
    protected function getEntitySourceKeyName(string $name): string
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
    protected function getEntityTargetKeyName(string $name): string
    {
        return match ($this->entity->getTargetKeyFormat()) {
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
