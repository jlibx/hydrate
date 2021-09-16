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

    public function getSourceFrom(ReflectionProperty $property): ?string
    {
        $source = $this->getSourceAttribute($property);

        return $source?->from;
    }

    public function getSourceTo(ReflectionProperty $property): ?string
    {
        $source = $this->getSourceAttribute($property);

        return $source?->to;
    }

    public function getSourceAttribute(ReflectionProperty $property): ?Source
    {
        $attrs = $property->getAttributes(Source::class);
        if (count($attrs) > 0) {
            foreach ($attrs as $attr) {
                $source = $attr->newInstance();
                if ($source instanceof Source) {
                    return $source;
                }
            }
        }

        return null;
    }
}
