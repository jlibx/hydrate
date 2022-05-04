<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Naming
{
    public const DEFAULT_SNAKE = 'default_snake';
    public const DEFAULT_CAMEL = 'default_camel';
    public const DEFAULT_STUDLY = 'default_studly';
    public const SNAKE_SNAKE = 'snake_snake';
    public const SNAKE_CAMEL = 'snake_camel';
    public const CAMEL_SNAKE = 'camel_snake';
    public const CAMEL_CAMEL = 'camel_camel';
    public const STUDLY_SNAKE = 'studly_snake';
    public const STUDLY_CAMEL = 'studly_camel';

    public string $strategy = '';

    /**
     * @param string $strategy
     */
    public function __construct(string $strategy)
    {
        $this->strategy = $strategy;
    }


}