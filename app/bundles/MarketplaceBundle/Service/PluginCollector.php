<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Service;

use Milex\CoreBundle\Release\ThisRelease;
use Milex\MarketplaceBundle\Api\Connection;
use Milex\MarketplaceBundle\Collection\PackageCollection;
use Milex\MarketplaceBundle\DTO\AllowlistEntry;

class PluginCollector
{
    private Connection $connection;
    private Allowlist $allowlist;

    /**
     * @var AllowlistEntry[]
     */
    private array $allowlistedPackages = [];

    private int $total = 0;

    public function __construct(Connection $connection, Allowlist $allowlist)
    {
        $this->connection = $connection;
        $this->allowlist  = $allowlist;
    }

    public function collectPackages(int $page, int $limit, string $query = ''): PackageCollection
    {
        $allowlist = $this->allowlist->getAllowList();

        if (!empty($allowlist)) {
            $this->allowlistedPackages = $this->filterAllowlistedPackagesForCurrentMilexVersion($allowlist->entries);
            $payload                   = $this->getAllowlistedPackages($page, $limit);
        } else {
            $payload = $this->connection->getPlugins($page, $limit, $query);
        }

        $this->total = (int) $payload['total'];

        return PackageCollection::fromArray($payload['results']);
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param AllowlistEntry[] $entries
     *
     * @return AllowlistEntry[]
     */
    private function filterAllowlistedPackagesForCurrentMilexVersion(array $entries): array
    {
        $milexVersion = ThisRelease::getMetadata()->getVersion();

        return array_filter($entries, function (AllowlistEntry $entry) use ($milexVersion) {
            if (
                !empty($entry->minimumMilexVersion) &&
                !version_compare($milexVersion, $entry->minimumMilexVersion, '>=')
            ) {
                return false;
            }

            if (
                !empty($entry->maximumMilexVersion) &&
                !version_compare($milexVersion, $entry->maximumMilexVersion, '<=')
            ) {
                return false;
            }

            return true;
        });
    }

    /**
     * During the Marketplace beta period, we only want to show packages that are explicitly
     * allowlisted. This function only gets allowlisted packages from Packagist. Their API doesn't
     * support querying multiple packages at once, so we simply do a foreach loop.
     *
     * @return array<string,mixed>
     */
    private function getAllowlistedPackages(int $page, int $limit): array
    {
        $total   = count($this->allowlistedPackages);
        $results = [];

        if (0 === $total) {
            return [
                'total'   => 0,
                'results' => [],
            ];
        }

        /** @var array<int,AllowlistEntry[]> */
        $chunks = array_chunk($this->allowlistedPackages, $limit);
        // Array keys start at 0 but page numbers start at 1
        $pageChunk = $page - 1;

        foreach ($chunks[$pageChunk] as $entry) {
            if (count($results) >= $limit) {
                continue;
            }

            $payload = $this->connection->getPlugins(1, 1, $entry->package);

            if (isset($payload['results'][0])) {
                $results[] = $payload['results'][0] + $entry->toArray();
            }
        }

        return [
            'total'   => $total,
            'results' => $results,
        ];
    }
}
