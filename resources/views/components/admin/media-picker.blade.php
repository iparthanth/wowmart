{{--
    Choose images from the media library by id - the reference panel's reuse
    model: files are uploaded once on Uploaded Files, then picked here.

    Native <details> and inputs only. Controls inside a closed <details> are
    still submitted, so the picker can stay collapsed without losing state.
--}}
@props(['name', 'images', 'selected' => [], 'multiple' => false])
@php
    $selected = array_values(array_map('intval', array_filter((array) $selected)));
    // Chosen images first, in their saved order, so re-saving keeps that order.
    $rank = array_flip($selected);
    $ordered = $images->sortBy(fn ($u) => $rank[$u->id] ?? PHP_INT_MAX);
    $chosen = $ordered->filter(fn ($u) => isset($rank[$u->id]));
    $input = $multiple ? 'checkbox' : 'radio';
    $field = $multiple ? $name.'[]' : $name;
@endphp
<details class="picker" id="f-{{ $name }}">
    <summary>
        <span class="picker-chosen">
            @forelse ($chosen->take(6) as $img)
                <img src="{{ $img->url() }}" alt="{{ $img->file_original_name }}">
            @empty
                <span class="faint">None chosen</span>
            @endforelse
            @if ($chosen->count() > 6)<span class="faint">+{{ $chosen->count() - 6 }}</span>@endif
        </span>
        <span class="btn btn-ghost btn-sm">Choose from library</span>
    </summary>

    @if ($images->isEmpty())
        <p class="faint" style="margin:12px 0 0">The library has no images yet. Upload some on Uploaded Files first.</p>
    @else
        <div class="picker-grid">
            @unless ($multiple)
                <label class="tile tile-none">
                    <input type="radio" name="{{ $field }}" value="" @checked($selected === [])>
                    <span>None</span>
                </label>
            @endunless
            @foreach ($ordered as $img)
                <label class="tile" title="{{ $img->file_original_name }}">
                    <input type="{{ $input }}" name="{{ $field }}" value="{{ $img->id }}" @checked(isset($rank[$img->id]))>
                    <img src="{{ $img->url() }}" alt="" loading="lazy">
                    <span class="tile-name">{{ $img->file_original_name }}</span>
                </label>
            @endforeach
        </div>
    @endif
</details>
