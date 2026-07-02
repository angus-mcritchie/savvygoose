<?php

namespace App\Http\Controllers;

use App\Support\DependencyRepositoryResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StarDependenciesController extends Controller
{
    private const MAX_DEPENDENCIES = 150;

    public function resolve(Request $request, DependencyRepositoryResolver $resolver): JsonResponse
    {
        $validated = $request->validate([
            'manifest' => ['required', 'string', 'max:200000'],
            'type' => ['required', 'in:npm,composer'],
        ]);

        $data = json_decode($validated['manifest'], true);

        if (! is_array($data)) {
            return response()->json([
                'message' => 'Could not parse manifest as JSON.',
                'errors' => ['manifest' => ['That does not look like valid JSON.']],
            ], 422);
        }

        $packages = $validated['type'] === 'composer'
            ? $this->composerPackageNames($data)
            : $this->npmPackageNames($data);

        if ($packages === []) {
            return response()->json([
                'message' => 'No dependencies found in that manifest.',
                'errors' => ['manifest' => ['No dependencies found.']],
            ], 422);
        }

        if (count($packages) > self::MAX_DEPENDENCIES) {
            return response()->json([
                'message' => 'Too many dependencies to resolve at once.',
                'errors' => ['manifest' => ['Limit is '.self::MAX_DEPENDENCIES.' dependencies per run.']],
            ], 422);
        }

        return response()->json([
            'dependencies' => $resolver->resolve($packages, $validated['type']),
        ]);
    }

    public function star(Request $request): JsonResponse
    {
        $token = session('github_token');

        if (! $token) {
            return response()->json(['message' => 'Not connected to GitHub.'], 403);
        }

        $validated = $request->validate([
            'repos' => ['required', 'array', 'min:1', 'max:'.self::MAX_DEPENDENCIES],
            'repos.*.owner' => ['required', 'string', 'max:39', 'regex:/^[A-Za-z0-9-]+$/'],
            'repos.*.repo' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._-]+$/'],
        ]);

        $results = collect($validated['repos'])->map(function (array $repo) use ($token) {
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/vnd.github+json'])
                ->withBody('', 'application/json')
                ->put("https://api.github.com/user/starred/{$repo['owner']}/{$repo['repo']}");

            return [
                'owner' => $repo['owner'],
                'repo' => $repo['repo'],
                'starred' => $response->successful(),
                'status' => $response->status(),
            ];
        });

        return response()->json(['results' => $results->values()]);
    }

    /**
     * @return list<string>
     */
    private function npmPackageNames(array $data): array
    {
        return collect(array_merge(
            array_keys($data['dependencies'] ?? []),
            array_keys($data['devDependencies'] ?? []),
        ))->unique()->values()->all();
    }

    /**
     * @return list<string>
     */
    private function composerPackageNames(array $data): array
    {
        return collect(array_merge(
            array_keys($data['require'] ?? []),
            array_keys($data['require-dev'] ?? []),
        ))
            ->reject(fn (string $name) => $name === 'php' || str_starts_with($name, 'ext-') || str_starts_with($name, 'lib-'))
            ->unique()
            ->values()
            ->all();
    }
}
