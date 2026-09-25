<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadRequest;
use App\Models\Upload;
use App\Models\User;
use App\Services\UploadService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UploadController extends Controller
{
    /**
     * Content-checked types. SVG is left out on purpose: an SVG can carry
     * script, and these files are served from the panel's own origin.
     */
    public const TYPES = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];

    /** Matches PHP's upload_max_filesize on this server (2M); raise both together. */
    public const MAX_KB = 2048;

    private const SORTS = [
        'newest' => ['id', 'desc'],
        'oldest' => ['id', 'asc'],
        'largest' => ['file_size', 'desc'],
        'name' => ['file_original_name', 'asc'],
    ];

    public function __construct(private readonly UploadService $uploads) {}

    /** The library's search/type/sort filters, unknown values falling back to "all, newest first". */
    public static function filters(Request $request): array
    {
        // Scalars only: ?search[]=x would otherwise hit "Array to string conversion" and answer 500.
        $search = $request->query('search');
        $sort = $request->query('sort');

        return [
            'search' => is_string($search) ? trim($search) : '',
            'type' => in_array($request->query('type'), ['image', 'document'], true) ? $request->query('type') : null,
            'sort' => is_string($sort) && array_key_exists($sort, self::SORTS) ? $sort : 'newest',
        ];
    }

    /** Shared with the API list so both sort and filter the same way. */
    public static function query(array $f): Builder
    {
        [$column, $direction] = self::SORTS[$f['sort']];

        return Upload::query()
            ->when($f['search'], fn (Builder $q, string $s) => $q->where('file_original_name', 'like', "%{$s}%"))
            ->when($f['type'], fn (Builder $q, string $t) => $q->where('type', $t))
            ->orderBy($column, $direction)
            ->orderByDesc('id');
    }

    public function index(Request $request): View
    {
        $f = self::filters($request);

        return view('admin.uploads.index', [
            'files' => self::query($f)->paginate(24)->withQueryString(),
            'inUse' => Upload::idsInUse(),
            'filters' => $f,
            'filtered' => $f['search'] !== '' || $f['type'] !== null,
        ]);
    }

    public function store(UploadRequest $request): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();

        foreach ($request->file('files') as $file) {
            $this->uploads->store($file, $admin);
        }

        $n = count($request->file('files'));

        return redirect()->route('admin.uploads.index')
            ->with('status', $n.' '.Str::plural('file', $n).' added to the library.');
    }

    public function destroy(Upload $upload): RedirectResponse
    {
        try {
            $this->uploads->delete($upload);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', "\"{$upload->file_original_name}\" deleted.");
    }
}
