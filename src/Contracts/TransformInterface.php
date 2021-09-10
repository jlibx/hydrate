<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Contracts;

interface TransformInterface
{
    public function hydrate(mixed $value): static;

    public function extract(): mixed;
}
