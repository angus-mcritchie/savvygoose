<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class GithubAuthController extends Controller
{
    /**
     * The GitHub access token is kept only in the session for the
     * duration of the visit. There is no User model or database in this
     * app, so nothing about this login is ever persisted beyond that.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['public_repo'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        session(['github_token' => $githubUser->token]);

        return redirect()->route('star-dependencies', ['connected' => 1]);
    }

    public function disconnect(): JsonResponse
    {
        $token = session('github_token');

        if ($token) {
            Http::withBasicAuth(config('services.github.client_id'), config('services.github.client_secret'))
                ->withHeaders(['Accept' => 'application/vnd.github+json'])
                ->delete('https://api.github.com/applications/'.config('services.github.client_id').'/grant', [
                    'access_token' => $token,
                ]);
        }

        session()->forget('github_token');

        return response()->json(['disconnected' => true]);
    }
}
