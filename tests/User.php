<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Test;

use Carbon\Carbon;
use Kabunx\Hydrate\ArrayEntity;
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

    #[Column("profile.name")]
    public string $profileName = '';

    public Profile $profile;

    #[ArrayEntity(Address::class)]
    public array $addresses = [];

    /**
     * @param string $value
     * @return int
     */
    public function setGender(string $value): int
    {
        return match ($value) {
            'f', 'female' => 2,
            'm', 'male', => 1,
            default => 0
        };
    }

    public function getBirthday(?Carbon $birthday): ?string
    {
        return $birthday?->toDateString();
    }
}