<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Test;

use Kabunx\Hydrate\Attributes\Column;
use Kabunx\Hydrate\Entity;

class Address extends Entity
{
    public string $name = '';

    #[Column('cityName', 'city_name')]
    public string $city = '';
}