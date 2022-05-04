<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Kabunx\Hydrate\Attributes\ArrayEntity;
use Kabunx\Hydrate\Attributes\Column;
use Kabunx\Hydrate\Attributes\Naming;
use Kabunx\Hydrate\Contracts\EntityInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * 注解
 */
class AttributeReader
{

    public function getNamingAttribute(ReflectionClass $class): ?Naming
    {
        $attrs = $class->getAttributes(Naming::class);
        if (count($attrs) > 0) {
            foreach ($attrs as $attr) {
                $naming = $attr->newInstance();
                if ($naming instanceof Naming) {
                    return $naming;
                }
            }
        }

        return null;
    }

    public function getNamingStrategy(ReflectionClass $class): string
    {
        $naming = $this->getNamingAttribute($class);

        return $naming?->strategy ?: Naming::SNAKE_SNAKE;
    }

    public function getColumnAttribute(ReflectionProperty $property): ?Column
    {
        $attrs = $property->getAttributes(Column::class);
        if (count($attrs) > 0) {
            foreach ($attrs as $attr) {
                $columnAttribute = $attr->newInstance();
                if ($columnAttribute instanceof Column) {
                    return $columnAttribute;
                }
            }
        }

        return null;
    }

    public function getColumnSourceName(ReflectionProperty $property): ?string
    {
        $columnAttribute = $this->getColumnAttribute($property);

        return $columnAttribute?->source;
    }

    public function getColumnTargetName(ReflectionProperty $property): ?string
    {
        $columnAttribute = $this->getColumnAttribute($property);

        return $columnAttribute?->target;
    }

    public function getArrayEntityAttribute(ReflectionProperty $property): ?ArrayEntity
    {
        $attrs = $property->getAttributes(ArrayEntity::class);
        if (count($attrs) > 0) {
            foreach ($attrs as $attr) {
                $arrayEntity = $attr->newInstance();
                if ($arrayEntity instanceof ArrayEntity) {
                    return $arrayEntity;
                }
            }
        }

        return null;
    }

    /**
     * @param ReflectionProperty $property
     * @return EntityInterface|null
     */
    public function getArrayEntityInstance(ReflectionProperty $property): ?EntityInterface
    {
        $attribute = $this->getArrayEntityAttribute($property);
        if (is_null($attribute)) {
            return null;
        }
        if (!class_exists($attribute->entity)) {
            return null;
        }
        $class = new $attribute->entity;

        return $class instanceof EntityInterface ? $class : null;
    }
}
