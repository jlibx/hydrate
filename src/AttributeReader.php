<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Kabunx\Hydrate\Contracts\EntityInterface;
use ReflectionProperty;

class AttributeReader
{

    /**
     * @param ReflectionProperty $property
     * @return EntityInterface|null
     */
    public function getArrayEntityClass(ReflectionProperty $property): ?EntityInterface
    {
        $attribute = $this->getArrayEntityAttribute($property);
        if (is_null($attribute)) {
            return null;
        }
        if (! class_exists($attribute->entity)) {
            return null;
        }
        $class = new $attribute->entity;

        return $class instanceof EntityInterface ? $class : null;
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

    public function getColumnFrom(ReflectionProperty $property): ?string
    {
        $source = $this->getColumnAttribute($property);

        return $source?->from;
    }

    public function getColumnTo(ReflectionProperty $property): ?string
    {
        $source = $this->getColumnAttribute($property);

        return $source?->to;
    }

    public function getColumnAttribute(ReflectionProperty $property): ?Column
    {
        $attrs = $property->getAttributes(Column::class);
        if (count($attrs) > 0) {
            foreach ($attrs as $attr) {
                $source = $attr->newInstance();
                if ($source instanceof Column) {
                    return $source;
                }
            }
        }

        return null;
    }
}
