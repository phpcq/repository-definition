<?php

declare(strict_types=1);

namespace Phpcq\RepositoryDefinition\Plugin;

use InvalidArgumentException;

use function preg_match;
use function sprintf;
use function version_compare;

/**
 * Describes the range of plugin API versions a consumer supports (inclusive boundaries).
 */
final class ApiVersionRange
{
    public function __construct(private readonly string $min, private readonly string $max)
    {
        if (!self::isValidVersion($min) || !self::isValidVersion($max)) {
            throw new InvalidArgumentException(sprintf('Invalid api version range "%s" - "%s"', $min, $max));
        }
        if (version_compare($min, $max, '>')) {
            throw new InvalidArgumentException(sprintf('Minimum "%s" is greater than maximum "%s"', $min, $max));
        }
    }

    public static function isValidVersion(string $version): bool
    {
        return 1 === preg_match('/^\d+\.\d+\.\d+$/', $version);
    }

    public function getMin(): string
    {
        return $this->min;
    }

    public function getMax(): string
    {
        return $this->max;
    }

    public function contains(string $version): bool
    {
        return self::isValidVersion($version)
            && version_compare($version, $this->min, '>=')
            && version_compare($version, $this->max, '<=');
    }
}
