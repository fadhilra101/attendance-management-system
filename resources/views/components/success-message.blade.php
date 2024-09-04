@props(['on'])

<div x-data="{ shown: false, timeout: null }"
     x-init="@this.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000); })"
     x-show.transition.out.opacity.duration.1500ms="shown"
     x-transition:leave.opacity.duration.1500ms
     style="display: none;"
    {{ $attributes->merge(['class' => 'fixed top-4 right-4 bg-green-500 text-gray-800 text-md px-6 py-4 rounded-xl shadow-lg']) }}>
    {{ $slot->isEmpty() ? 'Saved.' : $slot }}
</div>
