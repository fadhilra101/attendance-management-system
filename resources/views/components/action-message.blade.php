@props(['on'])

<div x-data="{ shown: false, timeout: null }" x-init="@this.on('{{ $on }}', () => {
    clearTimeout(timeout);
    shown = true;
    timeout = setTimeout(() => { shown = false }, 2000);
})" x-show="shown" x-transition.duration.200ms
    class="text-sm text-gray-600 dark:text-gray-400" {{ $attributes->merge(['class' => 'text-sm']) }}>
    {{ $slot }}
</div>
