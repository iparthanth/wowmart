@extends('admin.layouts.app')
@section('title', 'Uploaded files')
@section('crumbs')
    Media
@endsection

@section('content')

<form method="POST" action="{{ route('admin.uploads.store') }}" enctype="multipart/form-data" class="card upload-drop">
    @csrf
    <div>
        <label for="f-files" class="field-label">Add files to the library</label>
        <p class="hint" style="margin:2px 0 0">
            JPG, PNG, WebP, GIF or PDF · up to {{ App\Http\Controllers\Admin\UploadController::MAX_KB / 1024 }} MB each · 20 at a time.
            Products, brands and categories then pick from here.
        </p>
        @if ($errors->has('files') || $errors->has('files.*'))
            <p class="error" style="margin:6px 0 0">{{ $errors->first('files') ?: $errors->first('files.*') }}</p>
        @endif
    </div>
    <input id="f-files" type="file" name="files[]" multiple required
           accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,image/jpeg,image/png,image/webp,image/gif,application/pdf">
    <button type="submit" class="btn btn-primary">Upload</button>
</form>

<form method="GET" action="{{ route('admin.uploads.index') }}" class="card filters" role="search">
    <label class="sr-only" for="q">Search</label>
    <input id="q" type="search" name="search" value="{{ $filters['search'] }}" class="input filter-search" placeholder="File name">
    <label class="sr-only" for="filter-type">Type</label>
    <select id="filter-type" name="type" class="select">
        @foreach (['' => 'All files', 'image' => 'Images', 'document' => 'PDFs'] as $value => $text)
            <option value="{{ $value }}" @selected((string) $filters['type'] === $value)>{{ $text }}</option>
        @endforeach
    </select>
    <label class="sr-only" for="filter-sort">Sort</label>
    <select id="filter-sort" name="sort" class="select">
        @foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'largest' => 'Largest first', 'name' => 'Name, A to Z'] as $value => $text)
            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $text }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-ghost">Apply</button>
    @if ($filtered)
        <a href="{{ route('admin.uploads.index') }}" class="btn btn-link">Clear filters</a>
    @endif
    <span class="faint" style="margin-left:auto;font-size:12.5px">{{ number_format($files->total()) }} {{ Str::plural('file', $files->total()) }}</span>
</form>

@if ($files->isEmpty())
    <div class="card empty">
        <strong>{{ $filtered ? 'Nothing matches' : 'The library is empty' }}</strong>
        {{ $filtered ? 'Try another name or type.' : 'Upload images above; they can then be used anywhere in the panel.' }}
    </div>
@else
    <ul class="library">
        @foreach ($files as $file)
            <li class="card file">
                <a href="{{ $file->url() }}" target="_blank" rel="noopener" class="file-preview" title="Open {{ $file->file_original_name }}">
                    @if ($file->isImage())
                        <img src="{{ $file->url() }}" alt="" loading="lazy">
                    @else
                        <span class="file-kind mono">{{ strtoupper($file->extension) }}</span>
                    @endif
                </a>
                <div class="file-body">
                    <span class="file-name" title="{{ $file->file_original_name }}">{{ $file->file_original_name }}</span>
                    <span class="faint">{{ $file->humanSize() }} · {{ $file->created_at?->format('j M Y') }}</span>
                    <div class="file-foot">
                        @if (isset($inUse[$file->id]))
                            <span class="pill pill-info" title="Used by a product, brand, category or deal">In use</span>
                        @else
                            <span class="pill pill-mute">Unused</span>
                            <form method="POST" action="{{ route('admin.uploads.destroy', $file) }}"
                                  onsubmit="return confirm(@js('Delete "'.$file->file_original_name.'"? This cannot be undone.'))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm link-danger">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    @if ($files->hasPages())
        <div style="margin-top:16px">{{ $files->links() }}</div>
    @endif
@endif

@endsection
