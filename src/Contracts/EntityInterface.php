<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Contracts;

use ReflectionType;

interface EntityInterface
{
    /**
     * @param array $data
     * @return static
     */
    public static function instance(array $data): static;

    /**
     * @param array $data
     * @return static
     */
    public function newInstance(array $data): static;

    /**
     * @param array $data
     * @return $this
     */
    public function fill(array $data): static;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getOriginal(string $key, mixed $default = null): mixed;

    public function transform2Entity(string $key, mixed $value, ReflectionType $type = null);

    public function transform2Array(string $key, mixed $value): mixed;

    public function getSourceKeyFormat(): string;

    public function getArrayKeyFormat(): string;

}
