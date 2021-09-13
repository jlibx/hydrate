<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use ReflectionProperty;

class SourceReader
{
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

    protected function getSourceAttribute(ReflectionProperty $property): ?Source
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
