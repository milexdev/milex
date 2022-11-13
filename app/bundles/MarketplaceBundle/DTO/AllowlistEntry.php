<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\DTO;

final class AllowlistEntry
{
    /**
     * Packagist package in the format vendor/package.
     */
    public string $package;

    /**
     * Human readable name.
     */
    public string $displayName;

    /**
     * Minimum Milex version in semver format (e.g. 4.1.2).
     */
    public ?string $minimumMilexVersion;

    /**
     * Maximum Milex version in semver format (e.g. 4.1.2).
     */
    public ?string $maximumMilexVersion;

    public function __construct(string $package, string $displayName, ?string $minimumMilexVersion, ?string $maximumMilexVersion)
    {
        $this->package              = $package;
        $this->displayName          = $displayName;
        $this->minimumMilexVersion = $minimumMilexVersion;
        $this->maximumMilexVersion = $maximumMilexVersion;
    }

    /**
     * @param array<string,mixed> $array
     */
    public static function fromArray(array $array): AllowlistEntry
    {
        return new self(
            $array['package'],
            $array['display_name'] ?? '',
            $array['minimum_milex_version'],
            $array['maximum_milex_version']
        );
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'package'                => $this->package,
            'display_name'           => $this->displayName,
            'minimum_milex_version' => $this->minimumMilexVersion,
            'maximum_milex_version' => $this->maximumMilexVersion,
        ];
    }
}
