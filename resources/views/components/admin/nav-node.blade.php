@props(['node', 'level' => 0])
@php
    // One sidebar node: a link, a "not built yet" placeholder, or a collapsible
    // group whose children render recursively. A group starts open when any
    // descendant matches the current route, so the active page is always visible.
    $isActive = function (array $node) use (&$isActive): bool {
        $route = $node['route'] ?? null;

        if ($route !== null && Route::has($route) && request()->routeIs(...($node['patterns'] ?? [$route]))) {
            return true;
        }

        foreach ($node['children'] ?? [] as $child) {
            if ($isActive($child)) {
                return true;
            }
        }

        return false;
    };

    $children = $node['children'] ?? [];
    $route = $node['route'] ?? null;
    $built = $route !== null && Route::has($route);
    $patterns = $node['patterns'] ?? ($route !== null ? [$route] : []);
    $active = $built && request()->routeIs(...$patterns);
    $indent = 'style="padding-left:'.(17 + $level * 14).'px"';
@endphp

@if ($children !== [])
    <details class="nav-group" @if ($isActive($node)) open @endif>
        <summary {!! $indent !!}>
            <span>{{ $node['label'] }}</span>
            <span class="nav-caret" aria-hidden="true"></span>
        </summary>
        <div class="nav-sub">
            @foreach ($children as $child)
                <x-admin.nav-node :node="$child" :level="$level + 1" />
            @endforeach
        </div>
    </details>
@elseif ($built)
    <a href="{{ route($route, $node['params'] ?? []) }}" @if ($active) aria-current="page" @endif {!! $indent !!}>
        <span>{{ $node['label'] }}</span>
        @if (($node['count'] ?? 0) > 0)
            <span class="nav-count" title="{{ $node['count'] }} need attention">
                <x-admin.spark />{{ $node['count'] }}
            </span>
        @endif
    </a>
@else
    <span class="nav-soon" title="Not built yet" {!! $indent !!}>{{ $node['label'] }}</span>
@endif
