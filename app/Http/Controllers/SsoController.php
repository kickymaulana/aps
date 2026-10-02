<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SsoController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect_uri'),
            'response_type' => 'code',
            'scope' => config('services.sso.scope', ''),
        ]);

        return redirect()->away(config('services.sso.authorize_url').'?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('filament.admin.auth.login')->withErrors(['sso' => 'Autentikasi SSO gagal.']);
        }

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout(10)
            ->post(config('services.sso.token_url'), [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.sso.client_id'),
                'client_secret' => config('services.sso.client_secret'),
                'redirect_uri' => config('services.sso.redirect_uri'),
                'code' => $request->string('code')->toString(),
            ]);

        if ($tokenResponse->failed() || ! $tokenResponse->json('access_token')) {
            return redirect()->route('filament.admin.auth.login')->withErrors(['sso' => 'Token SSO tidak valid.']);
        }

        $userResponse = Http::withToken($tokenResponse->json('access_token'))
            ->acceptJson()
            ->timeout(10)
            ->get(config('services.sso.userinfo_url'));

        $nik = $userResponse->json(config('services.sso.nik_claim', 'nik'));
        $user = $userResponse->successful() && is_string($nik)
            ? User::where('nik', $nik)->first()
            : null;

        if (! $user) {
            return redirect()->route('filament.admin.auth.login')->withErrors(['sso' => 'NIK belum terdaftar di aplikasi APS.']);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(url('/admin'));
    }
}
