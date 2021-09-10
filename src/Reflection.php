<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Doctrine\Common\Annotations\AnnotationReader;
use Kabunx\Hydrate\Annotations\Source;
use Kabunx\Hydrate\Contracts\EntityInterface;
use ReflectionClass;
use ReflectionProperty;


class Reflection
{
    /**
     * @var AnnotationReader
     */
    protected static AnnotationReader $annotationReader;

    /**
     * @var array
     */
    protected static array $reflectionProperties = [];

    /**
     * @var array
     */
    protected static array $snakeNames = [];

    /**
     * @var EntityInterface
     */
    protected EntityInterface $entity;

    /**
     * 将数组赋值到对象
     *
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public static function hydrate(EntityInterface $entity): EntityInterface
    {
        $instance = new static();
        $instance->entity = $entity;
        $instance->convert();

        return $instance->entity;
    }


    public function convert(): void
    {
        $reflectProperties = $this->getReflectionProperties();
        foreach ($reflectProperties as $name => $property) {
            $from = $this->getSourceKeyName($name);
            $default = $property->isInitialized($this->entity)
                ? $property->getValue($this->entity)
                : null;
            $column = $this->getAnnotationReader()->getPropertyAnnotation($property, Source::class);
            if ($column instanceof Source && $column->from) { // 映射关系
                $from = $column->from;
            }
            $value = $this->entity->getOriginal($from, $default);
            // 被定义的数据转化
            $value = $this->entity->transform2Entity($name, $value, $property->getType());
            $property->setValue($this->entity, $value);
        }
    }

    /**
     * 对象转为数组
     *
     * @param EntityInterface $entity
     * @return array
     */
    public static function extract(EntityInterface $entity): array
    {
        $instance = new static();
        $instance->entity = $entity;

        return $instance->toArray();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        $reflectProperties = $this->getReflectionProperties();
        foreach ($reflectProperties as $name => $property) {
            $value = $property->getValue($this->entity);
            $value = $this->entity->transform2Array($name, $value);
            $column = $this->getAnnotationReader()->getPropertyAnnotation($property, Source::class);
            if ($column instanceof Source && $column->to) {
                $name = $column->to;
            } else {
                $name = $this->getArrayKeyName($name);
            }
            $result[$name] = $value;
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

    /**
     * @return AnnotationReader
     */
    protected function getAnnotationReader(): AnnotationReader
    {
        if (! isset(static::$annotationReader)) {
            static::$annotationReader = new AnnotationReader();
        }

        return static::$annotationReader;
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
}
