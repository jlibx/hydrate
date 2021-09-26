<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Test;

use Kabunx\Hydrate\Entity;

class Profile extends Entity
{
    public string $name = '';

    public int $age = 0;

    public bool $isValidated = false;
}