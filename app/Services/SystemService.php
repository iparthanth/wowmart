<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

/**
 * The System section: version/update bookkeeping, server-status checks and
 * the sitemap generator. Everything file-based; nothing here needs a table.
 */
class SystemService
{
    public const VERSION = '1.0.0';

    /** name => [current, required] pairs; status is computed. */
    public static function requirements(): array
    {
        return [
            'PHP' => [PHP_VERSION, '8.2.0'],
            'Laravel' => [app()->version(), '11.0.0'],
        ];
    }

    /** php.ini keys => recommended values. */
    public const CONFIGS = [
        'memory_limit' => '256M',
        'max_execution_time' => '300',
        'upload_max_filesize' => '64M',
        'post_max_size' => '64M',
    ];

    /** PHP extensions the panel relies on. */
    public const EXTENSIONS = ['ctype', 'curl', 'dom', 'fileinfo', 'gd', 'mbstring', 'openssl', 'pdo', 'pdo_sqlite', 'tokenizer', 'xml', 'zip'];

    /** Paths that must be writable for uploads and caches. */
    public const WRITABLE = ['storage', 'storage/framework/views', 'storage/logs', 'bootstrap/cache', 'public/uploads'];

    public static function lastUpdateCheck(): ?string
    {
        return BusinessSetting::get('system_last_update_check');
    }

    /** Record an uploaded update package. WowMart updates by hand; this only files the package. */
    public function fileUpdatePackage(string $storedName, string $originalName, int $size): array
    {
        BusinessSetting::put('system_last_update_check', Carbon::now()->toIso8601String());

        $pending = collect(json_decode((string) BusinessSetting::get('system_update_packages', '[]'), true) ?? []);
        $entry = [
            'file' => $storedName,
            'original' => $originalName,
            'size' => $size,
            'filed_at' => Carbon::now()->toIso8601String(),
        ];
        $pending->prepend($entry);
        BusinessSetting::put('system_update_packages', $pending->take(10)->values()->toJson());

        return $entry;
    }

    // ------------------------------------------------------------- sitemaps

    public static function sitemapPath(string $name): string
    {
        return public_path('sitemaps/'.basename($name));
    }

    /** @return array<int, array<string, mixed>> the generated sitemap files, newest first */
    public static function sitemaps(): array
    {
        if (! is_dir(public_path('sitemaps'))) {
            return [];
        }

        return collect(File::files(public_path('sitemaps')))
            ->sortByDesc('mtime')
            ->map(fn (SplFileInfo $file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified' => Carbon::createFromTimestamp($file->getMTime()),
                'mime' => $file->getExtension() === 'xml' ? 'application/xml' : 'text/plain',
                'url' => asset('sitemaps/'.$file->getFilename()),
            ])
            ->values()
            ->all();
    }

    /** Rebuild sitemap files from the catalogue; returns the file names written. */
    public function generateSitemaps(): array
    {
        File::ensureDirectoryExists(public_path('sitemaps'));

        $categories = Category::orderBy('id')->get();
        $products = Product::where('published', 1)->where('approved', 1)->orderBy('id')->get();
        $blogs = Blog::where('status', 1)->orderBy('id')->get();

        $this->writeSitemap('sitemap-categories.xml', $categories->map(fn ($c) => [url('/category/'.$c->slug), $c->updated_at ?? $c->created_at]));
        $this->writeSitemap('sitemap-products.xml', $products->map(fn ($p) => [url('/product/'.$p->slug), $p->updated_at ?? $p->created_at]));
        $this->writeSitemap('sitemap-blogs.xml', $blogs->map(fn ($b) => [url('/blog/'.$b->slug), $b->updated_at ?? $b->created_at]));

        return ['sitemap-categories.xml', 'sitemap-products.xml', 'sitemap-blogs.xml'];
    }

    private function writeSitemap(string $name, iterable $entries): void
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($entries as [$loc, $modified]) {
            $modified = $modified ? Carbon::parse($modified)->toAtomString() : Carbon::now()->toAtomString();
            $lines[] = '  <url><loc>'.htmlspecialchars($loc, ENT_XML1)."</loc><lastmod>{$modified}</lastmod></url>";
        }
        $lines[] = '</urlset>';

        File::put(self::sitemapPath($name), implode("\n", $lines));
    }

    public function deleteSitemap(string $name): bool
    {
        $name = basename($name);
        if (! Str::endsWith($name, '.xml')) {
            return false;
        }
        $path = self::sitemapPath($name);

        return is_file($path) ? File::delete($path) : false;
    }
}
