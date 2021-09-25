<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public ?string $source;

    public ?string $target;

    public function __construct(?string $source = null, ?string $target = null)
    {
        $this->source = $source;
        $this->target = $target;
    }
}
