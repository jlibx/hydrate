<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public ?string $from;

    public ?string $to;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }
}
