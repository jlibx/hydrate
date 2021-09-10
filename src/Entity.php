<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Kabunx\Hydrate\Contracts\EntityInterface;
use Kabunx\Hydrate\Traits\Transformable;


class Entity implements EntityInterface
{
    use Transformable;

    /**
     * @var array
     */
    protected array $original = [];

    /**
     * @var array
     */
    protected array $array = [];

    /**
     * 支持类型【'default', 'snake'】
     *
     * @var string
     */
    protected string $sourceKeyFormat = 'default';

    /**
     * 支持类型【'default', 'snake'】
     *
     * @var string
     */
    protected string $arrayKeyFormat = 'snake';

    /**
     * @param array $data
     * @return static
     */
    public static function instance(array $data): static
    {
        return (new static())->fill($data);
    }

    /**
     * array to this object
     * 通过映射关系补充当前对象数据
     *
     * @param array $data
     * @return $this
     */
    public function fill(array $data): static
    {
        $this->original = $data;
        $this->array = [];
        Reflection::hydrate($this);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->array)) {
            $this->array = Reflection::extract($this);
        }

        return $this->array;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getOriginal(string $key, mixed $default = null): mixed
    {
        if (empty($this->original)) {
            return value($default);
        }
        if (array_key_exists($key, $this->original)) {
            return $this->original[$key];
        }
        if (! str_contains($key, '.')) {
            return $this->original[$key] ?? value($default);
        }
        $array = $this->original;
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * @return string
     */
    public function getSourceKeyFormat(): string
    {
        return $this->sourceKeyFormat;
    }

    /**
     * @return string
     */
    public function getArrayKeyFormat(): string
    {
        return $this->arrayKeyFormat;
    }

}
