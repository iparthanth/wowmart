{{--
    An on/off control that is really a one-button form, so it works without
    JavaScript and every change is a normal, CSRF-checked POST.
--}}
@props(['action', 'on', 'label'])
<form method="POST" action="{{ $action }}" class="switch-form">
    @csrf
    <button type="submit" class="switch" role="switch" aria-checked="{{ $on ? 'true' : 'false' }}"
            aria-label="{{ $label }}" title="{{ $label }}: {{ $on ? 'on' : 'off' }}">
        <span class="switch-knob"></span>
    </button>
</form>
