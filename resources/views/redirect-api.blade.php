<x-guest-layout>
    <livewire:scan.verify-form />
    <form id="redirectForm" method="POST" action="{{ route('attendances.scan') }}">
        @csrf
        <input name="qr_code_id" type="hidden" value="{{ $qr_code->id }}">
        <input name="lat" type="hidden" value="{{ $office->gps_lat }}">
        <input name="lng" type="hidden" value="{{ $office->gps_lng }}">
        <input name="type" type="hidden" value="{{ $type }}">
        <input name="office_id" type="hidden" value="{{ $office->id }}">
    </form>

    <script></script>
</x-guest-layout>
