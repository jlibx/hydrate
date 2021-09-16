<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayEntity
{
    public string $entity;

    public function __construct(string $entity)
    {
        $this->entity = $entity;
    }
}