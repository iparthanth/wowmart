<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Services\SystemService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function __construct(private readonly SystemService $system) {}

    // ---------------------------------------------------------------- update

    public function update(): View
    {
        return view('admin.system.update', [
            'version' => SystemService::VERSION,
            'packages' => collect(json_decode((string) BusinessSetting::get('system_update_packages', '[]'), true) ?? []),
            'lastCheck' => SystemService::lastUpdateCheck(),
        ]);
    }

    public function applyUpdate(Request $request): RedirectResponse
    {
        $request->validate(['update_zip' => ['required', 'file', 'extensions:zip', 'max:204800']]);

        $file = $request->file('update_zip');
        $stored = $file->store('update-packages');

        $this->system->fileUpdatePackage($stored, $file->getClientOriginalName(), (int) $file->getSize());

        return back()->with('status', 'Update package filed. WowMart applies updates by hand — release notes list the steps.');
    }

    // ---------------------------------------------------------- server status

    public function serverStatus(): View
    {
        return view('admin.system.server-status', [
            'requirements' => collect(SystemService::requirements())
                ->map(fn ($r, $name) => ['name' => $name, 'current' => $r[0], 'required' => $r[1], 'ok' => version_compare($r[0], $r[1], '>=')]),
            'configs' => collect(SystemService::CONFIGS)
                ->map(fn ($recommended, $name) => ['name' => $name, 'current' => (string) ini_get($name), 'recommended' => $recommended, 'ok' => true]),
            'extensions' => collect(SystemService::EXTENSIONS)
                ->map(fn ($name) => ['name' => $name, 'ok' => extension_loaded($name)]),
            'writable' => collect(SystemService::WRITABLE)
                ->map(fn ($path) => ['path' => $path, 'ok' => is_writable(base_path($path))]),
        ]);
    }

    // ------------------------------------------------------ sitemap generator

    public function sitemaps(): View
    {
        return view('admin.system.sitemaps', [
            'sitemaps' => SystemService::sitemaps(),
        ]);
    }

    public function generateSitemaps(): RedirectResponse
    {
        $written = $this->system->generateSitemaps();

        return back()->with('status', 'Generated: '.implode(', ', $written));
    }

    public function deleteSitemap(string $name): RedirectResponse
    {
        if (! $this->system->deleteSitemap($name)) {
            throw new DomainException('That sitemap file could not be deleted.');
        }

        return back()->with('status', "Sitemap \"{$name}\" deleted.");
    }
}
