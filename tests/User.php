<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Test;

use Carbon\Carbon;
use Kabunx\Hydrate\Column;
use Kabunx\Hydrate\Entity;

class User extends Entity
{
    protected string $sourceKeyFormat = 'camel';

    public string $name = '';

    public string $email = '';

    public int $gender = 0;

    public ?Carbon $birthday;

    public ?Carbon $createdAt;

    #[Column(source: "modifiedAt")]
    public ?Carbon $updatedAt;


    /**
     * @param string $value
     * @return int
     */
    public function setGender(string $value): int
    {
        return $value == 'm' ? 0 : 1;
    }

    public function getBirthday(?Carbon $birthday): ?string
    {
        return $birthday?->toDateString();
    }
}