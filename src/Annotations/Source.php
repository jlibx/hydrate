<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Annotations;

/**
 * @Annotation
 */
final class Source
{
    /**
     * @var string
     */
    public string $from = '';

    /**
     * @var string
     */
    public string $to = '';
}
