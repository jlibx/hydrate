<?php
declare(strict_types=1);

namespace Kabunx\Hydrate\Contracts;

/**
 *
 */
interface SuperXMLInterface
{
    /**
     * @param string $content
     * @param array $replaces
     * @return static
     */
    public static function instance(string $content, array $replaces = []): static;

    public function findValue(string $path, string $default = ''): string;
}
