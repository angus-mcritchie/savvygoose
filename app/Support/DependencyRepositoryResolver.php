<?php

namespace App\Support;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class DependencyRepositoryResolver
{
    /**
     * Resolve a list of npm or Packagist package names to their GitHub
     * owner/repo, fetching them concurrently via Http::pool.
     *
     * @param  list<string>  $packages
     * @return list<array{name: string, owner: ?string, repo: ?string, resolved: bool}>
     */
    public function resolve(array $packages, string $type): array
    {
        $responses = Http::pool(fn (Pool $pool) => collect($packages)
            ->map(fn (string $package) => $pool->as($package)
                ->timeout(8)
                ->get($this->registryUrl($package, $type)))
            ->all());

        return collect($packages)
            ->map(function (string $package) use ($responses, $type) {
                $response = $responses[$package] ?? null;

                $repo = $response instanceof \Throwable || ! $response?->ok()
                    ? null
                    : $this->extractRepo($response->json(), $package, $type);

                return [
                    'name' => $package,
                    'owner' => $repo['owner'] ?? null,
                    'repo' => $repo['repo'] ?? null,
                    'resolved' => $repo !== null,
                ];
            })
            ->values()
            ->all();
    }

    private function registryUrl(string $package, string $type): string
    {
        return $type === 'composer'
            ? "https://repo.packagist.org/p2/{$package}.json"
            : 'https://registry.npmjs.org/'.$package;
    }

    /**
     * @return array{owner: string, repo: string}|null
     */
    private function extractRepo(?array $body, string $package, string $type): ?array
    {
        $url = $type === 'composer'
            ? $this->sourceUrlFromComposer($body, $package)
            : $this->sourceUrlFromNpm($body);

        return $url ? $this->normalizeGithubUrl($url) : null;
    }

    private function sourceUrlFromNpm(?array $body): ?string
    {
        $repository = $body['repository'] ?? null;

        return is_array($repository) ? ($repository['url'] ?? null) : $repository;
    }

    private function sourceUrlFromComposer(?array $body, string $package): ?string
    {
        $latest = collect($body['packages'][$package] ?? [])->first();

        return $latest['source']['url'] ?? $latest['homepage'] ?? null;
    }

    /**
     * @return array{owner: string, repo: string}|null
     */
    private function normalizeGithubUrl(string $url): ?array
    {
        $url = trim($url);

        if (str_starts_with($url, 'gitlab:') || str_starts_with($url, 'bitbucket:')) {
            return null;
        }

        $url = preg_replace('#^github:#', '', $url);

        // npm allows a bare "owner/repo" shorthand with no host at all.
        if (preg_match('#^[\w.-]+/[\w.-]+$#', $url)) {
            [$owner, $repo] = explode('/', $url, 2);

            return ['owner' => $owner, 'repo' => $repo];
        }

        if (preg_match('#github\.com[:/]+([\w.-]+)/([\w.-]+?)(?:\.git)?/?$#i', $url, $matches)) {
            return ['owner' => $matches[1], 'repo' => $matches[2]];
        }

        return null;
    }
}
