<?php
declare(strict_types=1);

namespace Kabunx\Hydrate;

use Kabunx\Hydrate\Contracts\EntityInterface;
use Kabunx\Hydrate\Traits\Transformable;


class Entity implements EntityInterface
{
    use Transformable;

    /**
     * 原始数据
     */
    protected array $original = [];

    /**
     * 已转化的数组
     */
    protected array $array = [];

    /**
     * 支持类型【'default', 'snake', 'camel', 'studly'】
     */
    protected string $sourceKeyFormat = 'snake';

    /**
     * 支持类型【'default', 'snake'】
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
     * @param array $data
     * @return $this
     */
    public function newInstance(array $data): static
    {
        return static::instance($data);
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
        Hydrate::reallocate($this);

        return $this;
    }

    /**
     * 转化为数组
     */
    public function toArray(): array
    {
        if (empty($this->array)) {
            $this->array = Hydrate::extract($this);
        }

        return $this->array;
    }

    /**
     * 提取数据
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

    public function getSourceKeyFormat(): string
    {
        return $this->sourceKeyFormat;
    }

    public function getArrayKeyFormat(): string
    {
        return $this->arrayKeyFormat;
    }

}
